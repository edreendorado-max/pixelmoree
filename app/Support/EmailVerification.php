<?php

namespace App\Support;

use App\Mail\EmailVerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class EmailVerification
{
    public function sendCode(User $user, bool $respectCooldown = true): bool
    {
        $mailer = config('mail.default');
        $smtpHost = strtolower((string) config('mail.mailers.smtp.host'));
        if (in_array($mailer, ['log', 'array'], true)
            || ($mailer === 'smtp' && in_array($smtpHost, ['', 'localhost', '127.0.0.1', '::1'], true))
            || blank(config('mail.from.address'))) {
            throw new RuntimeException('Email delivery is not configured. Set a real mail transport, SMTP host, and sender address before creating or verifying accounts.');
        }

        return DB::transaction(function () use ($user, $respectCooldown): bool {
            $verification = DB::table('email_verification_codes')
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($respectCooldown && $verification !== null
                && now()->diffInSeconds($verification->last_sent_at) < 60) {
                return false;
            }

            $code = (string) random_int(100000, 999999);
            DB::table('email_verification_codes')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'code_hash' => hash('sha256', $code),
                    'expires_at' => now()->addMinutes(10),
                    'last_sent_at' => now(),
                    'attempts' => 0,
                    'created_at' => $verification?->created_at ?? now(),
                    'updated_at' => now(),
                ],
            );

            Mail::to($user->email)->send(new EmailVerificationCodeMail($code));

            return true;
        });
    }

    public function verify(User $user, string $code): bool
    {
        return DB::transaction(function () use ($user, $code): bool {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            if ($lockedUser->hasVerifiedEmail()) {
                DB::table('email_verification_codes')->where('user_id', $lockedUser->id)->delete();
                return true;
            }

            $verification = DB::table('email_verification_codes')
                ->where('user_id', $lockedUser->id)
                ->lockForUpdate()
                ->first();

            if ($verification === null || now()->greaterThan($verification->expires_at)) {
                throw ValidationException::withMessages([
                    'code' => 'This verification code is missing or expired. Request a new one.',
                ]);
            }
            if ((int) $verification->attempts >= 5) {
                throw ValidationException::withMessages([
                    'code' => 'Too many incorrect attempts. Request a new verification code.',
                ]);
            }

            if (! hash_equals($verification->code_hash, hash('sha256', $code))) {
                DB::table('email_verification_codes')->where('user_id', $lockedUser->id)->increment('attempts');
                return false;
            }

            $lockedUser->forceFill(['email_verified_at' => now()])->save();
            DB::table('email_verification_codes')->where('user_id', $lockedUser->id)->delete();

            return true;
        });
    }
}
