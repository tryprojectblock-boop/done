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
        Schema::create('workspace_ticket_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('workspace_ticket_forms')->cascadeOnDelete();
            $table->string('type')->default('text');
            $table->string('label');
            $table->string('name');
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['form_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_ticket_form_fields');
    }
};
