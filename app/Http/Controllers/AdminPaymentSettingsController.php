<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminPaymentSettingsController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gcash_qr' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gcash_account_name' => ['required', 'string', 'max:120'],
            'gcash_number' => ['required', 'string', 'max:30'],
        ]);
        $path = $validated['gcash_qr']->store('admin-payment-qrs', 'local');
        if (! is_string($path)) throw new \RuntimeException('The admin GCash QR could not be saved privately.');
        $old = DB::table('admin_payment_settings')->where('id', 1)->value('gcash_qr_path');
        DB::table('admin_payment_settings')->where('id', 1)->update([
            'gcash_qr_path' => $path, 'gcash_account_name' => $validated['gcash_account_name'],
            'gcash_number' => $validated['gcash_number'], 'updated_at' => now(),
        ]);
        if (is_string($old) && $old !== $path) Storage::disk('local')->delete($old);
        return back()->with('status', 'Admin GCash payment details updated.');
    }
}
