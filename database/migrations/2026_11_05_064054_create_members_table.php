<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
        $table->id();
        $table->string('member_code')->unique();   // jemon: M-0001
        $table->text('name', 100);
        $table->text('email', 100);
        $table->integer('gender');
        $table->integer('nid')->nullable();
        $table->string('phone', 20)->nullable();
        $table->text('address')->nullable();
        $table->date('joining_date');
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
