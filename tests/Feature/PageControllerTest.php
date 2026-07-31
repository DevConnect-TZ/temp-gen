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
        $response->assertSee('/template-assets/template4/css/page2.css');
        $response->assertSee('/template-assets/template4/js/chat.js');
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

    public function test_custom_page_uses_configured_payment_delay_before_showing_modal(): void
    {
        $page = Page::create([
            'title' => 'Custom Video Page',
            'slug' => 'custom-video-page',
            'template' => 'custom',
            'price' => 1000,
            'payment_delay' => 10,
            'video_path' => 'videos/test.mp4',
            'is_active' => true,
        ]);

        $response = $this->get('/'.$page->slug);

        $response->assertOk();
        $response->assertSee('videos/test.mp4');
        $response->assertSee('setTimeout(function()', false);
        $response->assertSee('openPaymentModal();', false);
        $response->assertSee('}, 10000);', false);
    }

    public function test_custom_page_defaults_to_four_second_delay_when_unset(): void
    {
        $page = Page::create([
            'title' => 'Custom Video Page Two',
            'slug' => 'custom-video-page-two',
            'template' => 'custom',
            'price' => 1000,
            'video_path' => 'videos/test2.mp4',
            'is_active' => true,
        ]);

        $response = $this->get('/'.$page->slug);

        $response->assertOk();
        $response->assertSee('4000);', false);
    }
}
