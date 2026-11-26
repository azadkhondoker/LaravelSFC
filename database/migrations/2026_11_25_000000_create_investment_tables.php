<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('nid', 30)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('loan_client_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('interest_rate', 7, 3);
            $table->unsignedSmallInteger('duration_months');
            $table->date('start_date');
            $table->timestamps();
        });

        Schema::create('land_investments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('location')->nullable();
            $table->decimal('purchase_price', 12, 2);
            $table->decimal('area', 12, 3);
            $table->string('area_unit', 30);
            $table->string('deed_path')->nullable();
            $table->date('purchased_at');
            $table->timestamps();
        });

        Schema::create('investment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        Schema::create('other_investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_category_id')
                ->constrained('investment_categories')
                ->restrictOnDelete();
            $table->string('name', 150);
            $table->decimal('amount', 12, 2);
            $table->text('details')->nullable();
            $table->date('invested_at');
            $table->timestamps();
        });

        $now = now();
        DB::table('investment_categories')->insert([
            ['name' => 'Business share', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gold', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Silver', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('other_investments');
        Schema::dropIfExists('investment_categories');
        Schema::dropIfExists('land_investments');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('loan_clients');
    }
};
