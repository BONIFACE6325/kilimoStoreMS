<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Farmer;
use App\Models\Buyer;
use App\Models\Batch;
use App\Models\BatchMovement;
use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Settlement;
use App\Models\SettlementDeduction;
use App\Models\DryingJob;
use App\Models\MillingJob;
use App\Models\GradingRecord;
use App\Models\Expense;
use App\Models\OtherIncome;
use App\Models\FarmerService;
use App\Models\Bin;

class ResetForProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-for-production';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset database for production: configure gwakilabonface@gmail.com & masumbuko2409@gmail.com System Owners with password 12345678 and purge all test data.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Preparing system for production deployment...');

        // 1. Ensure primary tenant
        $tenant = Tenant::first();
        $tenantId = $tenant ? $tenant->id : null;

        // 2. Remove any unwanted/test users except the two production admins
        User::whereNotIn('email', ['gwakilabonface@gmail.com', 'masumbuko2409@gmail.com'])->delete();

        // 3. Configure User 1: gwakilabonface@gmail.com
        $user1 = User::firstOrNew(['email' => 'gwakilabonface@gmail.com']);
        $user1->name = 'Boniface Gwakila';
        $user1->password = Hash::make('12345678');
        $user1->tenant_id = $tenantId;
        $user1->role = 'System Owner';
        $user1->status = 'active';
        $user1->save();

        // 4. Configure User 2: masumbuko2409@gmail.com
        $user2 = User::firstOrNew(['email' => 'masumbuko2409@gmail.com']);
        $user2->name = 'Masumbuko';
        $user2->password = Hash::make('12345678');
        $user2->tenant_id = $tenantId;
        $user2->role = 'System Owner';
        $user2->status = 'active';
        $user2->save();

        $this->info("✅ System Owner accounts successfully configured with identical initial password '12345678':");

        // Display current active production users
        $users = User::all(['name', 'email', 'role']);
        $this->table(['Name', 'Email', 'Role'], $users->toArray());

        // 5. Purge operational / test data
        $this->warn('🧹 Purging all test transactional data (farmers, loans, sales, intake batches, receipts)...');

        FarmerService::query()->delete();
        SettlementDeduction::query()->delete();
        Settlement::query()->delete();
        InvoiceItem::query()->delete();
        Invoice::query()->delete();
        LoanTransaction::query()->delete();
        Loan::query()->delete();
        BatchMovement::query()->delete();
        Batch::query()->delete();
        GradingRecord::query()->delete();
        DryingJob::query()->delete();
        MillingJob::query()->delete();
        Expense::query()->delete();
        OtherIncome::query()->delete();
        Farmer::query()->delete();
        Buyer::query()->delete();

        // 6. Reset warehouse bin occupancies to 0
        Bin::query()->update(['current_occupancy_mt' => 0]);

        $this->info('✨ Database cleanup completed! Master configuration (Users, Services, Crops, Income & Expense categories) remains intact.');
        $this->info('🎉 System is 100% clean and ready for Production!');

        return Command::SUCCESS;
    }
}
