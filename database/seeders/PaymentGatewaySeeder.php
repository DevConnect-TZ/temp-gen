<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing gateways to avoid duplicates
        PaymentGateway::query()->delete();

        // Seed SonicPesa Gateway
        PaymentGateway::create([
            'name' => 'sonicpesa',
            'display_name' => 'SonicPesa',
            'api_key' => env('SONICPESA_API_KEY', 'sk_live_TU7Q0bYOQT5rC4zhOPB3JZRAvtJB82tKczIkhfVc'),
            'is_active' => true,
            'description' => 'SonicPesa Payment Gateway - USSD payments for Tanzania',
        ]);

        // Seed Snippe Gateway
        PaymentGateway::create([
            'name' => 'snippe',
            'display_name' => 'Snippe',
            'api_key' => env('SNIPPE_API_KEY', 'snp_f5e1464da54af60cc99e179592ed55642d769727152ae7a1ba7834c4b4c26c28'),
            'webhook_url' => env('SNIPPE_WEBHOOK_URL', 'https://example.com/webhook'),
            'is_active' => false,
            'description' => 'Snippe Payment Gateway - Mobile money payments',
        ]);
    }
}
