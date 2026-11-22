<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Simulated PHP earnings are stored as integer centavos. */
class CashLedger
{
    public function credit(User $user, int $amountMinor, string $type, string $purchaseId): void
    {
        if ($amountMinor <= 0) {
            return;
        }

        $locked = User::query()->lockForUpdate()->findOrFail($user->id);
        $balance = (int) $locked->cash_balance_minor + $amountMinor;
        $locked->forceFill(['cash_balance_minor' => $balance])->save();

        DB::table('cash_ledger_entries')->insert([
            'user_id' => $locked->id,
            'purchase_id' => $purchaseId,
            'type' => $type,
            'direction' => 'credit',
            'amount_minor' => $amountMinor,
            'balance_after_minor' => $balance,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function debit(User $user, int $amountMinor, string $type, string $purchaseId): void
    {
        if ($amountMinor <= 0) {
            throw ValidationException::withMessages(['amount_php' => 'Enter a valid withdrawal amount.']);
        }

        $locked = User::query()->lockForUpdate()->findOrFail($user->id);
        $balance = (int) $locked->cash_balance_minor;
        if ($balance < $amountMinor) {
            throw ValidationException::withMessages(['amount_php' => 'The amount is greater than your available creator balance.']);
        }

        $balance -= $amountMinor;
        $locked->forceFill(['cash_balance_minor' => $balance])->save();

        DB::table('cash_ledger_entries')->insert([
            'user_id' => $locked->id,
            'purchase_id' => $purchaseId,
            'type' => $type,
            'direction' => 'debit',
            'amount_minor' => $amountMinor,
            'balance_after_minor' => $balance,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
