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
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('owner_id')->constrained('plans')->nullOnDelete();
            $table->string('billing_cycle')->nullable()->after('plan_id'); // 1_month, 3_month, 6_month, 12_month, 3_year, 5_year
            $table->timestamp('subscription_starts_at')->nullable()->after('billing_cycle');
            $table->timestamp('subscription_ends_at')->nullable()->after('subscription_starts_at');
            $table->string('applied_coupon_code')->nullable()->after('subscription_ends_at');
            $table->decimal('discount_percent', 5, 2)->nullable()->after('applied_coupon_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn([
                'plan_id',
                'billing_cycle',
                'subscription_starts_at',
                'subscription_ends_at',
                'applied_coupon_code',
                'discount_percent',
            ]);
        });
    }
};
