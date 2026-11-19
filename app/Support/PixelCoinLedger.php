<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Pixel Coin values are stored as integer hundredths (100 = 1.00 PC). */
class PixelCoinLedger
{
    public function credit(User $user, int $coinMinor, string $type, string $description, ?string $artworkId = null, ?string $itemId = null, ?string $paymentOrderId = null): void
    {
        if ($coinMinor <= 0) return;
        $locked = User::query()->lockForUpdate()->findOrFail($user->id);
        $balance = (int) $locked->pixel_coin_balance + $coinMinor;
        $locked->forceFill(['pixel_coin_balance' => $balance])->save();
        DB::table('pixel_coin_transactions')->insert([
            'transaction_id' => (string) Str::uuid(), 'user_id' => $locked->id,
            'artwork_id' => $artworkId, 'artwork_item_id' => $itemId,
            'payment_order_id' => $paymentOrderId, 'type' => $type, 'direction' => 'credit',
            'amount' => $coinMinor, 'balance_after' => $balance, 'description' => $description,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function debit(User $user, int $coinMinor, string $type, string $description, ?string $artworkId = null, ?string $itemId = null, ?string $paymentOrderId = null): void
    {
        if ($coinMinor <= 0) throw ValidationException::withMessages(['coin_amount' => 'Enter a valid Pixel Coin amount.']);
        $locked = User::query()->lockForUpdate()->findOrFail($user->id);
        $balance = (int) $locked->pixel_coin_balance;
        if ($balance < $coinMinor) throw ValidationException::withMessages(['coin_amount' => 'Your Pixel Coin balance is too low for this transaction.']);
        $balance -= $coinMinor;
        $locked->forceFill(['pixel_coin_balance' => $balance])->save();
        DB::table('pixel_coin_transactions')->insert([
            'transaction_id' => (string) Str::uuid(), 'user_id' => $locked->id,
            'artwork_id' => $artworkId, 'artwork_item_id' => $itemId,
            'payment_order_id' => $paymentOrderId, 'type' => $type, 'direction' => 'debit',
            'amount' => $coinMinor, 'balance_after' => $balance, 'description' => $description,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public static function format(int $coinMinor): string
    {
        return number_format($coinMinor / 100, 2);
    }
}
