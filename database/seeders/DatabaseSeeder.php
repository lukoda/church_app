<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::unprepared(file_get_contents(database_path('seeders/data/regions.sql')));
        DB::unprepared(file_get_contents(database_path('seeders/data/districts.sql')));
        DB::unprepared(file_get_contents(database_path('seeders/data/wards.sql')));
        $this->call([
            RolesPermission::class,
            UserSeeder::class
        ]);
    }
}
