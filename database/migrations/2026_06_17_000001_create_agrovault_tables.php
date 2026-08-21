<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tenants
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('subdomain')->unique();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // 2. Branches
        Schema::create('branches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('code');
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        // 3. Update default users table or create custom fields
        // Since default users table is already created in 0001_01_01_000000_create_users_table.php,
        // we can add tenant/branch fields or recreate it here. To be clean, let's add fields to it.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('role')->default('operator');
            $table->string('status')->default('active');
        });

        // 4. Farmers
        Schema::create('farmers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('farmer_code');
            $table->string('name');
            $table->string('phone');
            $table->string('national_id')->nullable();
            $table->string('region')->nullable();
            $table->string('village')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['tenant_id', 'farmer_code']);
        });

        // 5. Bins
        Schema::create('bins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('capacity_mt', 12, 2);
            $table->decimal('current_occupancy_mt', 12, 2)->default(0.00);
            $table->string('crop_type')->nullable();
            $table->string('status')->default('empty');
            $table->timestamps();
        });

        // 6. Batches
        Schema::create('batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignUuid('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->string('batch_code');
            $table->string('crop_type');
            $table->string('variety')->nullable();
            $table->decimal('initial_moisture', 5, 2);
            $table->decimal('current_moisture', 5, 2);
            $table->decimal('initial_weight_mt', 12, 3);
            $table->decimal('current_weight_mt', 12, 3);
            $table->foreignUuid('current_bin_id')->nullable()->constrained('bins')->nullOnDelete();
            $table->string('status')->default('received');
            $table->timestamps();
            $table->unique(['tenant_id', 'batch_code']);
        });

        // 7. Batch Movements
        Schema::create('batch_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignUuid('source_bin_id')->nullable()->constrained('bins')->nullOnDelete();
            $table->foreignUuid('destination_bin_id')->nullable()->constrained('bins')->nullOnDelete();
            $table->foreignUuid('moved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('quantity_mt', 12, 3);
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        // 8. Drying Jobs
        Schema::create('drying_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->string('machine_id')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->decimal('initial_moisture', 5, 2);
            $table->decimal('final_moisture', 5, 2)->nullable();
            $table->decimal('weight_before_mt', 12, 3);
            $table->decimal('weight_after_mt', 12, 3)->nullable();
            $table->decimal('fee_amount', 15, 2)->default(0.00);
            $table->string('status')->default('queued');
            $table->timestamps();
        });

        // 9. Milling Jobs
        Schema::create('milling_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->string('machine_id')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->decimal('input_weight_mt', 12, 3);
            $table->decimal('output_weight_mt', 12, 3)->nullable();
            $table->decimal('byproduct_weight_mt', 12, 3)->nullable();
            $table->decimal('fee_amount', 15, 2)->default(0.00);
            $table->string('status')->default('queued');
            $table->timestamps();
        });

        // 10. Grading Records
        Schema::create('grading_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignUuid('grader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('moisture_pct', 5, 2);
            $table->decimal('foreign_matter_pct', 5, 2);
            $table->decimal('broken_kernels_pct', 5, 2);
            $table->char('grade_assigned', 1);
            $table->decimal('fee_amount', 15, 2)->default(0.00);
            $table->timestamps();
        });

        // 11. Loans
        Schema::create('loans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->foreignUuid('collateral_batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->string('loan_code');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate_annual', 5, 2);
            $table->decimal('current_balance', 15, 2);
            $table->decimal('accrued_interest', 15, 2)->default(0.00);
            $table->timestamp('disbursed_at')->nullable();
            $table->date('due_date');
            $table->string('status')->default('pending_approval');
            $table->timestamps();
            $table->unique(['tenant_id', 'loan_code']);
        });

        // 12. Loan Transactions
        Schema::create('loan_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->string('transaction_type'); // 'disbursement', 'interest_accrual', 'payment'
            $table->decimal('amount', 15, 2);
            $table->string('reference_number')->nullable();
            $table->timestamps();
        });

        // 13. Buyers
        Schema::create('buyers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // 14. Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('buyer_id')->constrained('buyers')->cascadeOnDelete();
            $table->string('invoice_number');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('vat_amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->string('status')->default('unpaid');
            $table->date('due_date');
            $table->timestamps();
            $table->unique(['tenant_id', 'invoice_number']);
        });

        // 15. Invoice Line Items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignUuid('batch_id')->constrained('batches');
            $table->decimal('quantity_mt', 12, 3);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->timestamps();
        });

        // 16. Settlements
        Schema::create('settlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('farmer_id')->constrained('farmers')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->cascadeOnDelete();
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('total_deductions', 15, 2);
            $table->decimal('net_payout', 15, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('payment_reference')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });

        // 17. Settlement Deductions
        Schema::create('settlement_deductions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('settlement_id')->constrained('settlements')->cascadeOnDelete();
            $table->string('deduction_type'); // 'storage_fee', 'drying_fee', 'milling_fee', 'loan_principal', 'loan_interest'
            $table->uuid('source_reference_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_deductions');
        Schema::dropIfExists('settlements');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('buyers');
        Schema::dropIfExists('loan_transactions');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('grading_records');
        Schema::dropIfExists('milling_jobs');
        Schema::dropIfExists('drying_jobs');
        Schema::dropIfExists('batch_movements');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('bins');
        Schema::dropIfExists('farmers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'branch_id', 'role', 'status']);
        });
        Schema::dropIfExists('branches');
        Schema::dropIfExists('tenants');
    }
};
