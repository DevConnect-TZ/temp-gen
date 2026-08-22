<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Minimal MP4 payload whose ftyp header is detected as video/mp4.
     */
    private function fakeMp4Content(int $kilobytes = 32): string
    {
        return pack('N', 24).'ftypisom'.pack('N', 0).'isom'.str_repeat('A', 1024 * $kilobytes);
    }

    private function fakeMp4Upload(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('video.mp4', $this->fakeMp4Content());
    }

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
        $response->assertSee('/api/page-videos/'.$page->slug.'/stream');
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

    public function test_admin_can_upload_custom_page_video_via_xhr_and_get_json_response(): void
    {
        Storage::fake('public');
        session(['admin_authenticated' => true]);

        $response = $this->post('/pages', [
            'title' => 'Custom Video Upload Page',
            'template' => 'custom',
            'price' => 1000,
            'is_active' => true,
            'video' => $this->fakeMp4Upload(),
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.slug', 'custom-video-upload-page');
        $response->assertJsonPath('data.redirect', route('pages.index'));

        $page = Page::where('slug', 'custom-video-upload-page')->firstOrFail();

        $this->assertNotNull($page->video_path);
        Storage::disk('public')->assertExists($page->video_path);
    }

    public function test_xhr_upload_with_invalid_video_type_returns_json_validation_errors(): void
    {
        Storage::fake('public');
        session(['admin_authenticated' => true]);

        $response = $this->post('/pages', [
            'title' => 'Bad Video Page',
            'template' => 'custom',
            'price' => 1000,
            'video' => UploadedFile::fake()->createWithContent('video.txt', str_repeat('A', 1024)),
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('video');
        $this->assertDatabaseMissing('pages', ['title' => 'Bad Video Page']);
    }

    public function test_upload_video_endpoint_returns_stored_path(): void
    {
        Storage::fake('public');
        session(['admin_authenticated' => true]);

        $response = $this->post('/pages/upload-video', [
            'video' => $this->fakeMp4Upload(),
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');

        $videoPath = $response->json('data.video_path');

        $this->assertIsString($videoPath);
        $this->assertStringStartsWith('videos/', $videoPath);
        Storage::disk('public')->assertExists($videoPath);
    }

    public function test_upload_video_endpoint_rejects_unsupported_file(): void
    {
        Storage::fake('public');
        session(['admin_authenticated' => true]);

        $response = $this->post('/pages/upload-video', [
            'video' => UploadedFile::fake()->createWithContent('notes.txt', str_repeat('A', 1024)),
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('video');
    }

    public function test_admin_can_create_custom_page_with_pre_uploaded_video_path(): void
    {
        Storage::fake('public');
        $videoPath = Storage::disk('public')->putFile('videos', $this->fakeMp4Upload());
        session(['admin_authenticated' => true]);

        $response = $this->post('/pages', [
            'title' => 'Pre Uploaded Video Page',
            'template' => 'custom',
            'price' => 1000,
            'is_active' => true,
            'video_path' => $videoPath,
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('pages', [
            'slug' => 'pre-uploaded-video-page',
            'video_path' => $videoPath,
        ]);
    }

    public function test_custom_page_rejects_non_existent_video_path(): void
    {
        Storage::fake('public');
        session(['admin_authenticated' => true]);

        $response = $this->post('/pages', [
            'title' => 'Missing Video Path Page',
            'template' => 'custom',
            'price' => 1000,
            'is_active' => true,
            'video_path' => 'videos/does-not-exist.mp4',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseMissing('pages', ['title' => 'Missing Video Path Page']);
    }

    public function test_custom_page_video_streams_with_correct_content_type(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('videos/stream-test.mp4', $this->fakeMp4Content());

        $page = Page::create([
            'title' => 'Stream Test Page',
            'slug' => 'stream-test-page',
            'template' => 'custom',
            'price' => 1000,
            'video_path' => 'videos/stream-test.mp4',
            'is_active' => true,
        ]);

        $response = $this->get('/api/page-videos/'.$page->slug.'/stream');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'video/mp4');
        $response->assertHeader('Cache-Control', 'max-age=3600, public');
    }

    public function test_custom_page_video_stream_supports_range_requests(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('videos/range-test.mp4', $this->fakeMp4Content());

        $page = Page::create([
            'title' => 'Range Test Page',
            'slug' => 'range-test-page',
            'template' => 'custom',
            'price' => 1000,
            'video_path' => 'videos/range-test.mp4',
            'is_active' => true,
        ]);

        $response = $this->get('/api/page-videos/'.$page->slug.'/stream', [
            'Range' => 'bytes=0-99',
        ]);

        $response->assertStatus(206);
        $response->assertHeader('Content-Type', 'video/mp4');
        $response->assertHeader('Content-Range', 'bytes 0-99/'.strlen($this->fakeMp4Content()));
    }

    public function test_custom_page_video_stream_returns_404_for_inactive_page(): void
    {
        $page = Page::create([
            'title' => 'Inactive Stream Page',
            'slug' => 'inactive-stream-page',
            'template' => 'custom',
            'price' => 1000,
            'video_path' => 'videos/missing.mp4',
            'is_active' => false,
        ]);

        $response = $this->get('/api/page-videos/'.$page->slug.'/stream');

        $response->assertNotFound();
    }

    public function test_custom_page_video_stream_returns_404_when_file_missing(): void
    {
        Storage::fake('public');

        $page = Page::create([
            'title' => 'Missing File Page',
            'slug' => 'missing-file-page',
            'template' => 'custom',
            'price' => 1000,
            'video_path' => 'videos/gone.mp4',
            'is_active' => true,
        ]);

        $response = $this->get('/api/page-videos/'.$page->slug.'/stream');

        $response->assertNotFound();
    }

    public function test_admin_can_replace_custom_page_video_via_xhr_and_get_json_response(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('videos/old.mp4', $this->fakeMp4Content(8));

        $page = Page::create([
            'title' => 'Replace Video Page',
            'slug' => 'replace-video-page',
            'template' => 'custom',
            'price' => 1000,
            'video_path' => 'videos/old.mp4',
            'is_active' => true,
        ]);

        session(['admin_authenticated' => true]);

        $response = $this->put('/pages/'.$page->slug, [
            'title' => 'Replace Video Page',
            'price' => 1500,
            'is_active' => true,
            'video' => $this->fakeMp4Upload(),
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');

        $page->refresh();

        $this->assertNotSame('videos/old.mp4', $page->video_path);
        Storage::disk('public')->assertExists($page->video_path);
        Storage::disk('public')->assertMissing('videos/old.mp4');
    }
}
