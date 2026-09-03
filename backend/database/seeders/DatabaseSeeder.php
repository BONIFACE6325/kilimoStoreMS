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

        // Dummy data (Bins, Farmers, Batches, Loans, Buyers) removed for clean production state.

        // 9. Create Default Services for Galanoki Company Ltd
        $services = [
            // Mpunga na Sembe
            ['name_sw' => 'Kukoboa (Sembe/Mpunga)', 'name_en' => 'Milling', 'rate' => 70.00, 'unit' => 'kg', 'description' => 'Milling and dehusking fee per Kg.'],
            ['name_sw' => 'Kusogeza kwenye kinu', 'name_en' => 'Move to Mill', 'rate' => 300.00, 'unit' => 'gunia', 'description' => 'Fee for moving bags to mill.'],
            ['name_sw' => 'Kupima Mamba', 'name_en' => 'Weigh Mamba Roba', 'rate' => 200.00, 'unit' => 'roba', 'description' => 'Mamba weighing fee per 100kg bale.'],
            ['name_sw' => 'Kuanika mpunga', 'name_en' => 'Dry Paddy', 'rate' => 1000.00, 'unit' => 'gunia', 'description' => 'Sun-drying fee per bag.'],
            ['name_sw' => 'Kuanika + Kuchanganya', 'name_en' => 'Dry & Mix', 'rate' => 1500.00, 'unit' => 'gunia', 'description' => 'Dry and mix fee per bag.'],
            ['name_sw' => 'Kuanika + Kurundika', 'name_en' => 'Dry & Pile', 'rate' => 1200.00, 'unit' => 'gunia', 'description' => 'Dry and pile/stack fee per bag.'],
            ['name_sw' => 'Kuchanganya Mpunga', 'name_en' => 'Mix Mpunga', 'rate' => 1000.00, 'unit' => 'gunia', 'description' => 'Paddy mixing fee per bag.'],
            
            // Grading Machine
            ['name_sw' => 'Kuchanganya Mchele na Mafuta', 'name_en' => 'Mix Rice & Oil', 'rate' => 2.50, 'unit' => 'kg', 'description' => 'Rice oil polishing fee per Kg.'],
            ['name_sw' => 'Kugiredi', 'name_en' => 'Grading', 'rate' => 8.00, 'unit' => 'kg', 'description' => 'Grading machine fee per Kg.'],
            ['name_sw' => 'Kudoloti (Color sorting)', 'name_en' => 'Color Sorting', 'rate' => 22.00, 'unit' => 'kg', 'description' => 'Color sorting/sorting machine fee per Kg.'],
            
            // Stoko
            ['name_sw' => 'Kupanga stoko', 'name_en' => 'Stack Stock', 'rate' => 700.00, 'unit' => 'gunia', 'description' => 'Warehouse stack arranging fee per bag.'],
            ['name_sw' => 'Kukoboa (Stoko)', 'name_en' => 'Milling (Stock)', 'rate' => 100.00, 'unit' => 'kg', 'description' => 'Milling fee for stored grain per Kg.'],
            ['name_sw' => 'Wafanyakazi (Labor)', 'name_en' => 'Labor (Wafanyakazi)', 'rate' => 1000.00, 'unit' => 'gunia', 'description' => 'Manual labor fee per bag.'],
            ['name_sw' => 'Kuchanganya + Wafanyakazi', 'name_en' => 'Mix & Labor', 'rate' => 2000.00, 'unit' => 'gunia', 'description' => 'Mixing and labor fee per bag.'],
        ];

        foreach ($services as $service) {
            \App\Models\Service::create(array_merge($service, [
                'tenant_id' => $tenant->id,
            ]));
        }
    }
}
