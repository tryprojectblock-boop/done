<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnel_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('funnel_id')->constrained()->onDelete('cascade');
            $table->integer('current_step')->default(0);
            $table->timestamp('subscribed_at');
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['active', 'completed', 'unsubscribed', 'paused'])->default('active');
            $table->unique(['user_id', 'funnel_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_subscribers');
    }
};
