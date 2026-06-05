<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
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
            'base_url' => 'https://api.sonicpesa.com/api/v1/payment',
            'is_active' => true,
            'description' => 'SonicPesa Payment Gateway - USSD payments for Tanzania',
        ]);

        // Seed Snippe Gateway
        PaymentGateway::create([
            'name' => 'snippe',
            'display_name' => 'Snippe',
            'api_key' => env('SNIPPE_API_KEY', 'snp_f5e1464da54af60cc99e179592ed55642d769727152ae7a1ba7834c4b4c26c28'),
            'base_url' => 'https://api.snippe.sh/v1',
            'webhook_url' => env('SNIPPE_WEBHOOK_URL', 'https://example.com/webhook'),
            'is_active' => false,
            'description' => 'Snippe Payment Gateway - Mobile money payments',
        ]);

        // Seed FastLipa Gateway
        PaymentGateway::create([
            'name' => 'fastlipa',
            'display_name' => 'FastLipa',
            'api_key' => config('services.fastlipa.api_token', 'FastLipa_qY7TaiWgMLfJObBRvLEFlM7Ab'),
            'base_url' => config('services.fastlipa.base_url', 'https://api.fastlipa.com/api'),
            'is_active' => false,
            'description' => 'FastLipa Payment Gateway - Mobile money payments',
        ]);

        // Seed Mobilipa Gateway
        PaymentGateway::create([
            'name' => 'mobilipa',
            'display_name' => 'Mobilipa',
            'api_key' => env('MOBILIPA_API_KEY', 'sk_live_YN0RUW4o2EQ3SxLNOiyyj25Ldfs37KyBHk1GSbda'),
            'base_url' => 'https://api.mobilipa.store',
            'is_active' => false,
            'description' => 'Mobilipa Payment Gateway - Mobile money USSD payments for Tanzania',
        ]);

        // Seed PesaLink Gateway
        PaymentGateway::create([
            'name' => 'pesalink',
            'display_name' => 'PesaLink',
            'api_key' => env('PESALINK_API_KEY', '7e882bd9b694db8fa0584df8d589c207'),
            'base_url' => 'https://pesalink.online/api',
            'is_active' => false,
            'description' => 'PesaLink Payment Gateway - Mobile money payments',
        ]);
    }
}
