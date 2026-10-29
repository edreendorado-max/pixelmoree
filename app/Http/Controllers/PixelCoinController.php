<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\PixelCoinTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PixelCoinController extends Controller
{
    private const MAX_BALANCE = 1_000_000_000;

    public function show(Request $request): JsonResponse
    {
        $user = User::query()->findOrFail($request->user()->id);

        return response()->json([
            'balance' => (int) $user->pixel_coin_balance,
            'transactions' => $user->pixelCoinTransactions()
                ->latest('id')
                ->limit(20)
                ->get()
                ->map(fn (PixelCoinTransaction $transaction): array => $this->transactionData($transaction)),
            'demo_only' => true,
        ]);
    }

    public function topUp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'in:500,1000,2500'],
        ]);

        $result = DB::transaction(function () use ($request, $validated): array {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $amount = (int) $validated['amount'];
            $balance = (int) $user->pixel_coin_balance;

            if ($balance + $amount > self::MAX_BALANCE) {
                throw ValidationException::withMessages([
                    'amount' => 'The Pixel Coin demo balance limit has been reached.',
                ]);
            }

            $user->pixel_coin_balance = $balance + $amount;
            $user->save();

            $transaction = $user->pixelCoinTransactions()->create([
                'type' => 'demo_top_up',
                'amount' => $amount,
                'balance_after' => $user->pixel_coin_balance,
                'description' => 'Free demo credits; no payment was processed.',
            ]);

            return ['balance' => (int) $user->pixel_coin_balance, 'transaction' => $transaction];
        });

        return response()->json([
            'balance' => $result['balance'],
            'transaction' => $this->transactionData($result['transaction']),
            'demo_only' => true,
        ], 201);
    }

    public function purchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'artwork_id' => ['required', 'string', 'max:140'],
        ]);

        $result = DB::transaction(function () use ($request, $validated): JsonResponse|array {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $artwork = Artwork::query()
                ->whereKey($validated['artwork_id'])
                ->where('status', 'Listed')
                ->lockForUpdate()
                ->firstOrFail();

            if ($user->pixelCoinTransactions()
                ->where('type', 'purchase')
                ->where('artwork_id', $artwork->id)
                ->exists()) {
                return response()->json(['message' => 'This artwork has already been purchased with Pixel Coin.'], 409);
            }

            $amount = (int) ceil((float) $artwork->price * 1000);
            $balance = (int) $user->pixel_coin_balance;
            if ($amount <= 0 || $amount > self::MAX_BALANCE) {
                throw ValidationException::withMessages([
                    'artwork_id' => 'This listing does not have a supported Pixel Coin demo price.',
                ]);
            }
            if ($balance < $amount) {
                return response()->json([
                    'message' => 'There are not enough Pixel Coins for this demo purchase.',
                    'balance' => $balance,
                    'required' => $amount,
                ], 422);
            }

            $user->pixel_coin_balance = $balance - $amount;
            $user->save();

            $transaction = $user->pixelCoinTransactions()->create([
                'artwork_id' => $artwork->id,
                'type' => 'purchase',
                'amount' => $amount,
                'balance_after' => $user->pixel_coin_balance,
                'description' => 'Demo purchase: ' . $artwork->title,
            ]);

            return ['balance' => (int) $user->pixel_coin_balance, 'transaction' => $transaction];
        });

        if ($result instanceof JsonResponse) {
            return $result;
        }

        return response()->json([
            'balance' => $result['balance'],
            'transaction' => $this->transactionData($result['transaction']),
            'demo_only' => true,
        ], 201);
    }

    private function transactionData(PixelCoinTransaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'type' => $transaction->type,
            'amount' => (int) $transaction->amount,
            'balance_after' => (int) $transaction->balance_after,
            'description' => $transaction->description,
            'created_at' => $transaction->created_at?->toIso8601String(),
        ];
    }
}
