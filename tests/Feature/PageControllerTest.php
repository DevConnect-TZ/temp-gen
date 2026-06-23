<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_page_with_template4(): void
    {
        PaymentGateway::create([
            'name' => 'fastlipa',
            'display_name' => 'FastLipa',
            'api_key' => 'fastlipa-test-token',
            'base_url' => 'https://api.fastlipa.com/api',
            'is_active' => true,
            'description' => 'FastLipa',
        ]);

        session(['admin_authenticated' => true]);

        $response = $this->post('/pages', [
            'title' => 'WhatsApp Group Page',
            'template' => 'template4',
            'price' => 2000,
            'payment_gateway' => 'fastlipa',
            'is_active' => true,
        ]);

        $response->assertRedirect('/pages');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pages', [
            'title' => 'WhatsApp Group Page',
            'template' => 'template4',
            'price' => 2000,
            'payment_gateway' => 'fastlipa',
            'is_active' => true,
        ]);
    }

    public function test_public_template4_page_is_served(): void
    {
        PaymentGateway::create([
            'name' => 'fastlipa',
            'display_name' => 'FastLipa',
            'api_key' => 'fastlipa-test-token',
            'base_url' => 'https://api.fastlipa.com/api',
            'is_active' => true,
            'description' => 'FastLipa',
        ]);

        $page = Page::create([
            'title' => 'WhatsApp Group Page',
            'slug' => 'whatsapp-group-page',
            'template' => 'template4',
            'price' => 2000,
            'payment_gateway' => 'fastlipa',
            'is_active' => true,
        ]);

        $response = $this->get('/'.$page->slug);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
        $response->assertSee('WhatsApp Group');
        $response->assertSee('/templates/template4/css/page2.css');
        $response->assertSee('/templates/template4/js/chat.js');
        $response->assertSee('window.pageId = '.$page->id);
        $response->assertSee('window.pagePrice = 2000');
        $response->assertSee('/api/payments/create-order');
        $response->assertSee('/api/payments/check-status');
    }

    public function test_inactive_template4_page_returns_404(): void
    {
        $page = Page::create([
            'title' => 'Inactive WhatsApp Page',
            'slug' => 'inactive-whatsapp-page',
            'template' => 'template4',
            'price' => 2000,
            'payment_gateway' => 'fastlipa',
            'is_active' => false,
        ]);

        $response = $this->get('/'.$page->slug);

        $response->assertNotFound();
    }
}
