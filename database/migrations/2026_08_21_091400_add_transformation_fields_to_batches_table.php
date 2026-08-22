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
        Schema::table('batches', function (Blueprint $table) {
            $table->uuid('parent_batch_id')->nullable()->after('id');
            $table->foreign('parent_batch_id')->references('id')->on('batches')->onDelete('set null');
        });
        
        // Note: For enums in Laravel, changing them directly is tricky across DBs.
        // We will just let 'transformed' and 'sold' be inserted if we are using strings.
        // In the original migration, status is a string. Let's make sure it can accept 'transformed' and 'sold'.
        // There is no strict DB enum in the original migration (`$table->string('status')`), so we just add the parent_batch_id.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['parent_batch_id']);
            $table->dropColumn('parent_batch_id');
        });
    }
};
