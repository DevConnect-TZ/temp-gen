<?php

namespace Database\Seeders;

use App\Models\AdminSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminCredentialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminSetting::set('admin_email', 'sbrk255@gmail.com');
        AdminSetting::set('admin_password', Hash::make('01319943591Bk.'));
    }
}
