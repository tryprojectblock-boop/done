<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('type')->default('classic'); // classic, product
            $table->string('status')->default('active'); // active, archived, suspended
            $table->json('settings')->nullable();
            $table->json('enabled_modules')->nullable();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('logo_path')->nullable();
            $table->string('color', 7)->nullable(); // Hex color
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
