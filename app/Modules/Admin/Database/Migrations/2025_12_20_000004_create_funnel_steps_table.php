<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funnel_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('funnel_id')->constrained()->onDelete('cascade');
            $table->integer('step_order');
            $table->string('name');
            $table->integer('delay_days')->default(0);
            $table->integer('delay_hours')->default(0);
            $table->foreignId('condition_tag_id')->nullable()->constrained('funnel_tags')->nullOnDelete();
            $table->enum('condition_type', ['none', 'has_tag', 'missing_tag'])->default('none');
            $table->string('from_email');
            $table->string('from_name');
            $table->string('subject');
            $table->longText('body_html');
            $table->longText('body_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funnel_steps');
    }
};
