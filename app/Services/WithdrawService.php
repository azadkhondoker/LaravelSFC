<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\Member;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WithdrawService
{
    /** Total deposits are recorded as installments in this application. */
    public function totalDeposit(int $memberId): string
    {
        return (string) Installment::where('member_id', $memberId)->sum('amount');
    }

    public function totalWithdrawn(int $memberId): string
    {
        return (string) Withdrawal::where('member_id', $memberId)
            ->where('status', Withdrawal::APPROVED)->sum('amount');
    }

    public function pendingWithdrawals(int $memberId): string
    {
        return (string) Withdrawal::where('member_id', $memberId)
            ->where('status', Withdrawal::PENDING)->sum('amount');
    }

    /** Savings balance = deposits - approved withdrawals. */
    public function balance(int $memberId): string
    {
        return $this->fromMinorUnits(
            $this->toMinorUnits($this->totalDeposit($memberId))
            - $this->toMinorUnits($this->totalWithdrawn($memberId))
        );
    }

    /** Amount still available after reserving pending requests. */
    public function available(int $memberId): string
    {
        return $this->fromMinorUnits(
            $this->toMinorUnits($this->balance($memberId))
            - $this->toMinorUnits($this->pendingWithdrawals($memberId))
        );
    }

    /** A recorded request reserves funds but does not reduce balance until approved. */
    public function request(Member $member, string $amount, string $date, ?string $reason = null): Withdrawal
    {
        return DB::transaction(function () use ($member, $amount, $date, $reason) {
            // Serialize requests for a member so concurrent requests cannot overdraw.
            Member::withTrashed()->whereKey($member->id)->lockForUpdate()->firstOrFail();

            $amountInMinorUnits = $this->toMinorUnits($amount);
            if ($amountInMinorUnits <= 0) {
                throw ValidationException::withMessages(['amount' => 'Amount must be greater than zero.']);
            }

            $available = $this->available($member->id);
            if ($amountInMinorUnits > $this->toMinorUnits($available)) {
                throw ValidationException::withMessages([
                    'amount' => "Withdrawal exceeds available balance ({$available}).",
                ]);
            }

            return Withdrawal::create([
                'transaction_id' => $this->newTransactionId(),
                'member_id' => $member->id,
                'amount' => $amount,
                'withdrawal_date' => $date,
                'status' => Withdrawal::PENDING,
                'reason' => $reason,
            ]);
        });
    }

    /** Re-check the balance under lock before finalizing an approval. */
    public function approve(Withdrawal $withdrawal, int $adminId, ?string $note = null): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $adminId, $note) {
            $w = Withdrawal::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            Member::withTrashed()->whereKey($w->member_id)->lockForUpdate()->firstOrFail();

            if ($w->status !== Withdrawal::PENDING) {
                throw ValidationException::withMessages(['status' => 'This request was already processed.']);
            }

            if ($this->toMinorUnits((string) $w->amount) > $this->toMinorUnits($this->balance($w->member_id))) {
                throw ValidationException::withMessages([
                    'amount' => 'Member balance is no longer sufficient for this withdrawal.',
                ]);
            }

            $w->update([
                'status' => Withdrawal::APPROVED,
                'approved_by' => $adminId,
                'decided_at' => now(),
                'admin_note' => $note,
            ]);

            return $w;
        });
    }

    public function reject(Withdrawal $withdrawal, int $adminId, ?string $note = null): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $adminId, $note) {
            $w = Withdrawal::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();

            if ($w->status !== Withdrawal::PENDING) {
                throw ValidationException::withMessages(['status' => 'This request was already processed.']);
            }

            $w->update([
                'status' => Withdrawal::REJECTED,
                'approved_by' => $adminId,
                'decided_at' => now(),
                'admin_note' => $note,
            ]);

            return $w;
        });
    }

    private function newTransactionId(): string
    {
        do {
            $id = 'WD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Withdrawal::where('transaction_id', $id)->exists());

        return $id;
    }

    private function toMinorUnits(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromMinorUnits(int $amount): string
    {
        $sign = $amount < 0 ? '-' : '';
        $amount = abs($amount);

        return $sign.intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
