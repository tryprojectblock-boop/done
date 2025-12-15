<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('date');
            $table->decimal('working_hours', 4, 1)->default(0); // 0 = full holiday, >0 = reduced hours
            $table->timestamps();

            $table->unique(['workspace_id', 'date']);
            $table->index(['workspace_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_holidays');
    }
};
