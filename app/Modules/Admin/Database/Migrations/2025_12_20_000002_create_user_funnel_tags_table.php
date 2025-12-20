<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_funnel_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('funnel_tag_id')->constrained()->onDelete('cascade');
            $table->timestamp('tagged_at');
            $table->unique(['user_id', 'funnel_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_funnel_tags');
    }
};
