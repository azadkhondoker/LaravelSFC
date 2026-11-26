<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('other_investments', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('details');
            $table->string('receipt_name')->nullable()->after('receipt_path');
        });
    }

    public function down(): void
    {
        Schema::table('other_investments', function (Blueprint $table) {
            $table->dropColumn(['receipt_path', 'receipt_name']);
        });
    }
};
