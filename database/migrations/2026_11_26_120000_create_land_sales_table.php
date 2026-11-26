<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('land_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_investment_id')->constrained('land_investments')->restrictOnDelete();
            $table->decimal('area_sold', 12, 3);
            $table->decimal('sale_price', 12, 2);
            $table->string('buyer_name', 150);
            $table->string('buyer_phone', 30)->nullable();
            $table->date('sold_at');
            $table->string('deed_path')->nullable();
            $table->string('deed_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('land_sales');
    }
};
