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
        Schema::create('bot_notifications', function (Blueprint $table) {
            $table->id()->startingValue(10_000);
            $table->foreignIdFor(\App\Models\User::class, 'sender_id')->constrained(new \App\Models\User()->getTable(), new \App\Models\User()->getKeyName());
            $table->string('batch_id')->nullable();
            $table->string('status');
            $table->boolean('quote');
            $table->unsignedBigInteger('total_notifications');
            $table->unsignedBigInteger('successful_notifications');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_notifications');
    }
};
