<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'tenant_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('other_incomes') && !Schema::hasColumn('other_incomes', 'tenant_id')) {
            Schema::table('other_incomes', function (Blueprint $table) {
                $table->foreignUuid('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'tenant_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('other_incomes') && Schema::hasColumn('other_incomes', 'tenant_id')) {
            Schema::table('other_incomes', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
