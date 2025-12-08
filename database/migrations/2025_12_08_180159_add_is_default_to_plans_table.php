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
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('is_popular');
        });

        // Set the first free plan as the default trial plan if it exists
        $trialPlan = \App\Modules\Admin\Models\Plan::where('type', 'free')->first();
        if ($trialPlan) {
            $trialPlan->update(['is_default' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
