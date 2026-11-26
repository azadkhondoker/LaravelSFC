<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_guarantors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('nid', 30);
            $table->text('address');
            $table->boolean('liability_confirmed')->default(false);
            $table->timestamps();
            $table->unique(['loan_id', 'position']);
        });

        Schema::create('loan_collaterals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->string('asset_type', 100);
            $table->text('description')->nullable();
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->string('document_path')->nullable();
            $table->string('document_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_collaterals');
        Schema::dropIfExists('loan_guarantors');
    }
};
