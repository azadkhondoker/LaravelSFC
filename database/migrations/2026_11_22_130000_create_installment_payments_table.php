<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->string('receipt_no')->nullable()->unique();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('installment_amount', 12, 2);
            $table->unsignedBigInteger('installments_count');
            $table->date('paid_at');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_payments');
    }
};
