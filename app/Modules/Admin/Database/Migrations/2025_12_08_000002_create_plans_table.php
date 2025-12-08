<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('paid'); // free, paid
            $table->integer('workspace_limit')->default(1);
            $table->integer('team_member_limit')->default(5);
            $table->integer('storage_limit_gb')->default(5);
            $table->decimal('price_1_month', 10, 2)->default(0);
            $table->decimal('price_3_month', 10, 2)->default(0);
            $table->decimal('price_6_month', 10, 2)->default(0);
            $table->decimal('price_12_month', 10, 2)->default(0);
            $table->decimal('price_3_year', 10, 2)->default(0);
            $table->decimal('price_5_year', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->json('features')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
