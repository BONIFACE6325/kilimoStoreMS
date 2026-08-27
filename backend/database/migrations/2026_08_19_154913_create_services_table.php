<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name_sw');
            $table->string('name_en');
            $table->string('category'); // 'drying', 'milling', 'grading', 'stock', etc.
            $table->decimal('rate', 15, 2);
            $table->string('unit'); // 'kg', 'gunia', 'roba'
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('drying_jobs', function (Blueprint $table) {
            $table->foreignUuid('service_id')->nullable()->constrained('services')->nullOnDelete();
        });

        Schema::table('milling_jobs', function (Blueprint $table) {
            $table->foreignUuid('service_id')->nullable()->constrained('services')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('milling_jobs', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
        });

        Schema::table('drying_jobs', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
        });

        Schema::dropIfExists('services');
    }
};
