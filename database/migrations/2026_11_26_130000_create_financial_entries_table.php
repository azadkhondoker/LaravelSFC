<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_type', 20);
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('entry_date');
            $table->text('note');
            $table->timestamps();
            $table->index(['entry_type', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_entries');
    }
};
