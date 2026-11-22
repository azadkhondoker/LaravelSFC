<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            $table->date('deposit_date')->nullable();
            $table->foreignId('installment_payment_id')
                ->nullable()
                ->constrained('installment_payments')
                ->restrictOnDelete();
        });

        $legacyInstallments = DB::table('installments')
            ->join('members', 'members.id', '=', 'installments.member_id')
            ->select(
                'installments.id',
                'installments.member_id',
                'members.joining_date',
            )
            ->orderBy('installments.member_id')
            ->orderBy('installments.paid_at')
            ->orderBy('installments.id')
            ->get();

        $monthOffsets = [];
        foreach ($legacyInstallments as $installment) {
            $monthOffset = $monthOffsets[$installment->member_id] ?? 0;
            $depositDate = Carbon::parse($installment->joining_date)
                ->startOfDay()
                ->addMonthsNoOverflow($monthOffset)
                ->toDateString();

            DB::table('installments')
                ->where('id', $installment->id)
                ->update(['deposit_date' => $depositDate]);

            $monthOffsets[$installment->member_id] = $monthOffset + 1;
        }

        Schema::table('installments', function (Blueprint $table) {
            $table->unique(['member_id', 'deposit_date']);
        });
    }

    public function down(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            $table->dropUnique('installments_member_id_deposit_date_unique');
            $table->dropForeign(['installment_payment_id']);
            $table->dropColumn(['deposit_date', 'installment_payment_id']);
        });
    }
};
