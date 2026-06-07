<?php

namespace Database\Seeders;

use App\Models\PesaLinkAccount;
use Illuminate\Database\Seeder;

class PesaLinkAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PesaLinkAccount::query()->delete();

        PesaLinkAccount::create([
            'name' => 'Default',
            'api_key' => env('PESALINK_API_KEY', '7e882bd9b694db8fa0584df8d589c207'),
            'base_url' => 'https://pesalink.online/api',
            'is_active' => true,
        ]);
    }
}
