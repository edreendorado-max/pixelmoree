<?php

namespace App\Http\Controllers;

use App\Models\PixelCoinTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PixelCoinController extends Controller
{
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
            'wallet_status' => 'read_only',
        ]);
    }

    public function topUp(Request $request): JsonResponse
    {
        abort(503, 'Pixel Coin top-ups are unavailable during beta.');
    }

    public function purchase(Request $request): JsonResponse
    {
        abort(503, 'Pixel Coin checkout is unavailable during beta.');
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
