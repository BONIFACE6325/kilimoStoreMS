<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\User;
use App\Models\Farmer;
use App\Models\Bin;
use App\Models\Batch;
use App\Models\Loan;
use App\Models\LoanTransaction;
use App\Models\Buyer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Settlement;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Tenant
        $tenant = Tenant::create([
            'name' => 'AgroVault East Africa',
            'subdomain' => 'kilimo',
            'status' => 'active',
        ]);

        // 2. Create Branches
        $branchA = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Arusha Main Warehouse',
            'location' => 'Arusha, Tanzania',
            'code' => 'ARU',
        ]);

        $branchB = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Mwanza Collection Hub',
            'location' => 'Mwanza, Tanzania',
            'code' => 'MWZ',
        ]);

        // 3. Create Users
        $manager = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchA->id,
            'name' => 'James Makori',
            'email' => 'manager@agrovault.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'status' => 'active',
        ]);

        $accountant = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchA->id,
            'name' => 'Sarah Kimani',
            'email' => 'accountant@agrovault.com',
            'password' => Hash::make('password'),
            'role' => 'accountant',
            'status' => 'active',
        ]);

        $operator = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchA->id,
            'name' => 'Tom Njoroge',
            'email' => 'operator@agrovault.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'status' => 'active',
        ]);

        // 4. Create Bins for Branch A (Arusha)
        $binA1 = Bin::create([
            'branch_id' => $branchA->id,
            'name' => 'A1',
            'capacity_mt' => 1000,
            'current_occupancy_mt' => 780,
            'crop_type' => 'Maize',
            'status' => 'occupied',
        ]);

        $binA2 = Bin::create([
            'branch_id' => $branchA->id,
            'name' => 'A2',
            'capacity_mt' => 1000,
            'current_occupancy_mt' => 910,
            'crop_type' => 'Maize',
            'status' => 'full',
        ]);

        $binA3 = Bin::create([
            'branch_id' => $branchA->id,
            'name' => 'A3',
            'capacity_mt' => 1000,
            'current_occupancy_mt' => 450,
            'crop_type' => 'Maize',
            'status' => 'low',
        ]);

        $binB1 = Bin::create([
            'branch_id' => $branchA->id,
            'name' => 'B1',
            'capacity_mt' => 1000,
            'current_occupancy_mt' => 910,
            'crop_type' => 'Rice',
            'status' => 'full',
        ]);

        $binC1 = Bin::create([
            'branch_id' => $branchA->id,
            'name' => 'C1',
            'capacity_mt' => 1000,
            'current_occupancy_mt' => 450,
            'crop_type' => 'Beans',
            'status' => 'low',
        ]);

        // 5. Create Farmers
        $farmer1 = Farmer::create([
            'tenant_id' => $tenant->id,
            'farmer_code' => 'FRM-001',
            'name' => 'Amina Mwangi',
            'phone' => '+255 712 345 678',
            'national_id' => '19881024-55443-00002-12',
            'region' => 'Arusha',
            'village' => 'Mwandiga',
            'status' => 'active',
        ]);

        $farmer2 = Farmer::create([
            'tenant_id' => $tenant->id,
            'farmer_code' => 'FRM-002',
            'name' => 'John Ochieng',
            'phone' => '+255 713 456 789',
            'national_id' => '19901115-44332-00001-34',
            'region' => 'Mwanza',
            'village' => 'Igoma',
            'status' => 'active',
        ]);

        $farmer3 = Farmer::create([
            'tenant_id' => $tenant->id,
            'farmer_code' => 'FRM-003',
            'name' => 'Grace Kilimo',
            'phone' => '+255 714 567 890',
            'national_id' => '19850912-33221-00003-45',
            'region' => 'Dodoma',
            'village' => 'Kihonda',
            'status' => 'active',
        ]);

        $farmer4 = Farmer::create([
            'tenant_id' => $tenant->id,
            'farmer_code' => 'FRM-004',
            'name' => 'Peter Mushi',
            'phone' => '+255 715 678 901',
            'national_id' => '19800203-22110-00004-56',
            'region' => 'Iringa',
            'village' => 'Makambako',
            'status' => 'inactive',
        ]);

        $farmer5 = Farmer::create([
            'tenant_id' => $tenant->id,
            'farmer_code' => 'FRM-005',
            'name' => 'Mary Njau',
            'phone' => '+255 716 789 012',
            'national_id' => '19920804-11009-00005-67',
            'region' => 'Morogoro',
            'village' => 'Mikese',
            'status' => 'active',
        ]);

        // 6. Create Batches
        $batch1 = Batch::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchA->id,
            'farmer_id' => $farmer1->id,
            'batch_code' => 'BCH-1142',
            'crop_type' => 'Maize',
            'variety' => 'Katumani',
            'initial_moisture' => 14.50,
            'current_moisture' => 12.10,
            'initial_weight_mt' => 240.000,
            'current_weight_mt' => 240.000,
            'current_bin_id' => $binA2->id,
            'status' => 'stored',
        ]);

        $batch2 = Batch::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchA->id,
            'farmer_id' => $farmer2->id,
            'batch_code' => 'BCH-1141',
            'crop_type' => 'Rice',
            'variety' => 'Super Kyela',
            'initial_moisture' => 15.00,
            'current_moisture' => 13.40,
            'initial_weight_mt' => 180.000,
            'current_weight_mt' => 180.000,
            'current_bin_id' => $binB1->id,
            'status' => 'stored',
        ]);

        $batch3 = Batch::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branchA->id,
            'farmer_id' => $farmer3->id,
            'batch_code' => 'BCH-1140',
            'crop_type' => 'Maize',
            'variety' => 'Katumani',
            'initial_moisture' => 13.80,
            'current_moisture' => 11.80,
            'initial_weight_mt' => 320.000,
            'current_weight_mt' => 320.000,
            'current_bin_id' => $binA1->id,
            'status' => 'stored',
        ]);

        // 7. Create Loans
        $loan1 = Loan::create([
            'tenant_id' => $tenant->id,
            'farmer_id' => $farmer1->id,
            'collateral_batch_id' => $batch1->id,
            'loan_code' => 'LN-2041',
            'principal_amount' => 1200000.00,
            'interest_rate_annual' => 0.00,
            'current_balance' => 480000.00,
            'accrued_interest' => 0.00,
            'disbursed_at' => now()->subDays(60),
            'due_date' => now()->addDays(60),
            'status' => 'active',
        ]);

        LoanTransaction::create([
            'loan_id' => $loan1->id,
            'transaction_type' => 'disbursement',
            'amount' => 1200000.00,
            'reference_number' => 'DISB-8841',
        ]);

        LoanTransaction::create([
            'loan_id' => $loan1->id,
            'transaction_type' => 'payment',
            'amount' => 720000.00,
            'reference_number' => 'PAY-8841',
        ]);

        $loan2 = Loan::create([
            'tenant_id' => $tenant->id,
            'farmer_id' => $farmer3->id,
            'collateral_batch_id' => $batch3->id,
            'loan_code' => 'LN-2040',
            'principal_amount' => 3500000.00,
            'interest_rate_annual' => 0.00,
            'current_balance' => 3500000.00,
            'accrued_interest' => 0.00,
            'disbursed_at' => now()->subDays(30),
            'due_date' => now()->subDays(2),
            'status' => 'overdue',
        ]);

        // 8. Create Buyers
        $buyer1 = Buyer::create([
            'tenant_id' => $tenant->id,
            'name' => 'AgriCo Ltd',
            'contact_person' => 'David Ouma',
            'phone' => '+255 788 111 222',
            'email' => 'sales@agrico.co.tz',
            'status' => 'active',
        ]);

        $buyer2 = Buyer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Grain Masters',
            'contact_person' => 'Tom Juma',
            'phone' => '+255 788 333 444',
            'email' => 'info@grainmasters.co.tz',
            'status' => 'active',
        ]);

        // 9. Create Default Services for Galanoki Company Ltd
        $services = [
            // Mpunga na Sembe
            ['name_sw' => 'Kukoboa (Sembe/Mpunga)', 'name_en' => 'Milling', 'category' => 'milling', 'rate' => 70.00, 'unit' => 'kg', 'description' => 'Milling and dehusking fee per Kg.'],
            ['name_sw' => 'Kusogeza kwenye kinu', 'name_en' => 'Move to Mill', 'category' => 'milling', 'rate' => 300.00, 'unit' => 'gunia', 'description' => 'Fee for moving bags to mill.'],
            ['name_sw' => 'Kupima Mamba', 'name_en' => 'Weigh Mamba Roba', 'category' => 'milling', 'rate' => 200.00, 'unit' => 'roba', 'description' => 'Mamba weighing fee per 100kg bale.'],
            ['name_sw' => 'Kuanika mpunga', 'name_en' => 'Dry Paddy', 'category' => 'drying', 'rate' => 1000.00, 'unit' => 'gunia', 'description' => 'Sun-drying fee per bag.'],
            ['name_sw' => 'Kuanika + Kuchanganya', 'name_en' => 'Dry & Mix', 'category' => 'drying', 'rate' => 1500.00, 'unit' => 'gunia', 'description' => 'Dry and mix fee per bag.'],
            ['name_sw' => 'Kuanika + Kurundika', 'name_en' => 'Dry & Pile', 'category' => 'drying', 'rate' => 1200.00, 'unit' => 'gunia', 'description' => 'Dry and pile/stack fee per bag.'],
            ['name_sw' => 'Kuchanganya Mpunga', 'name_en' => 'Mix Mpunga', 'category' => 'milling', 'rate' => 1000.00, 'unit' => 'gunia', 'description' => 'Paddy mixing fee per bag.'],
            
            // Grading Machine
            ['name_sw' => 'Kuchanganya Mchele na Mafuta', 'name_en' => 'Mix Rice & Oil', 'category' => 'milling', 'rate' => 2.50, 'unit' => 'kg', 'description' => 'Rice oil polishing fee per Kg.'],
            ['name_sw' => 'Kugiredi', 'name_en' => 'Grading', 'category' => 'grading', 'rate' => 8.00, 'unit' => 'kg', 'description' => 'Grading machine fee per Kg.'],
            ['name_sw' => 'Kudoloti (Color sorting)', 'name_en' => 'Color Sorting', 'category' => 'milling', 'rate' => 22.00, 'unit' => 'kg', 'description' => 'Color sorting/sorting machine fee per Kg.'],
            
            // Stoko
            ['name_sw' => 'Kupanga stoko', 'name_en' => 'Stack Stock', 'category' => 'milling', 'rate' => 700.00, 'unit' => 'gunia', 'description' => 'Warehouse stack arranging fee per bag.'],
            ['name_sw' => 'Kukoboa (Stoko)', 'name_en' => 'Milling (Stock)', 'category' => 'milling', 'rate' => 100.00, 'unit' => 'kg', 'description' => 'Milling fee for stored grain per Kg.'],
            ['name_sw' => 'Wafanyakazi (Labor)', 'name_en' => 'Labor (Wafanyakazi)', 'category' => 'milling', 'rate' => 1000.00, 'unit' => 'gunia', 'description' => 'Manual labor fee per bag.'],
            ['name_sw' => 'Kuchanganya + Wafanyakazi', 'name_en' => 'Mix & Labor', 'category' => 'milling', 'rate' => 2000.00, 'unit' => 'gunia', 'description' => 'Mixing and labor fee per bag.'],
        ];

        foreach ($services as $service) {
            \App\Models\Service::create(array_merge($service, [
                'tenant_id' => $tenant->id,
            ]));
        }
    }
}
