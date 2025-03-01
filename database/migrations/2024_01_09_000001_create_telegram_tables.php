<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('telegram_id')->unique();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->timestamps();
        });

        Schema::create('partnerships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->constrained('telegram_users')->onDelete('cascade');
            $table->foreignId('user2_id')->constrained('telegram_users')->onDelete('cascade');
            $table->string('invite_token')->nullable()->unique();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partnership_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_user_id')->constrained('telegram_users');
            $table->foreignId('to_user_id')->constrained('telegram_users');
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->enum('type', ['debt', 'demand']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('partnerships');
        Schema::dropIfExists('telegram_users');
    }
};