<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Admin;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dinomination = DB::table('dinominations')->insert([
            'name' => 'KKKT',
            'description' => 'Kanisa La Kiinjili La Kilutheri',
        ]);

        $admin = Admin::create([
            'dinomination_id' => 1,
            'phone' => '0678123456',
            'church_level' => 'Dinomination',
            'password' => Hash::make('123456')
        ]);

        $admin->assignRole('Dinomination Admin');
    }
}
