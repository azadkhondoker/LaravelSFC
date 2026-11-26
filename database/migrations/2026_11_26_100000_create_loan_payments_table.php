<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('month_number');
            $table->date('due_date');
            $table->decimal('principal', 12, 2);
            $table->decimal('interest', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->date('paid_at');
            $table->string('receipt_no')->unique();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['loan_id', 'month_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};
