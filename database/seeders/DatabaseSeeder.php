<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PacsEntrySeeder::class,
            WeeklyScheduleSeeder::class,
            MachineSeeder::class,
            ShiftTemplateSeeder::class,
            ShiftSeeder::class,
            ProductionSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            SalesSeeder::class,
            StockMovementsSeeder::class,
            InvoiceSeeder::class,
            RecyclingSeeder::class,
            CostSeeder::class,
            SupplierSeeder::class,
            PacsEntriesLogSeeder::class,
            UsersLogSeeder::class,
            ShiftTemplatesLogSeeder::class,
            ShiftsLogSeeder::class,
            WeeklyScheduleLogSeeder::class,
        ]);
    }
}

