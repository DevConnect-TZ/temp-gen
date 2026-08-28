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

    public function test_admin_can_create_tiktok_live_page_with_video_and_account_name(): void
    {
        Storage::fake('public');
        session(['admin_authenticated' => true]);

        $response = $this->post('/pages', [
            'title' => 'TikTok Live Page',
            'template' => 'template5',
            'account_name' => '@juma_live',
            'price' => 1500,
            'payment_gateway' => 'sonicpesa',
            'is_active' => true,
            'video' => $this->fakeMp4Upload(),
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.slug', 'tiktok-live-page');

        $this->assertDatabaseHas('pages', [
            'slug' => 'tiktok-live-page',
            'template' => 'template5',
            'account_name' => '@juma_live',
        ]);

        $page = Page::where('slug', 'tiktok-live-page')->firstOrFail();
        $this->assertNotNull($page->video_path);
        Storage::disk('public')->assertExists($page->video_path);
    }

    public function test_tiktok_live_page_requires_account_name(): void
    {
        Storage::fake('public');
        session(['admin_authenticated' => true]);

        $response = $this->post('/pages', [
            'title' => 'TikTok No Account Page',
            'template' => 'template5',
            'price' => 1500,
            'is_active' => true,
            'video' => $this->fakeMp4Upload(),
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('account_name');
        $this->assertDatabaseMissing('pages', ['title' => 'TikTok No Account Page']);
    }

    public function test_public_tiktok_live_page_is_served_with_account_name_and_live_ui(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('videos/tiktok-live.mp4', $this->fakeMp4Content());

        $page = Page::create([
            'title' => 'TikTok Live Page',
            'slug' => 'tiktok-live-page',
            'template' => 'template5',
            'account_name' => '@juma_live',
            'price' => 1500,
            'payment_delay' => 6,
            'video_path' => 'videos/tiktok-live.mp4',
            'is_active' => true,
        ]);

        $response = $this->get('/'.$page->slug);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
        $response->assertSee('/api/page-videos/'.$page->slug.'/stream');
        $response->assertSee('@juma_live');
        $response->assertSee('LIVE');
        $response->assertSee('host-chip');
        $response->assertSee('viewerCount');
        $response->assertSee('joinStack');
        $response->assertSee('likeCount');
        $response->assertSee('openPaymentModal();', false);
        $response->assertSee('}, 6000);', false);
    }

    public function test_tiktok_live_page_defaults_account_name_to_page_title(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('videos/tiktok-live-2.mp4', $this->fakeMp4Content());

        $page = Page::create([
            'title' => 'Default Account Live',
            'slug' => 'default-account-live',
            'template' => 'template5',
            'price' => 1000,
            'video_path' => 'videos/tiktok-live-2.mp4',
            'is_active' => true,
        ]);

        $response = $this->get('/'.$page->slug);

        $response->assertOk();
        $response->assertSee('Default Account Live');
    }

    public function test_admin_can_update_tiktok_live_account_name(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('videos/tiktok-edit.mp4', $this->fakeMp4Content());

        $page = Page::create([
            'title' => 'Edit TikTok Live',
            'slug' => 'edit-tiktok-live',
            'template' => 'template5',
            'account_name' => '@old_name',
            'price' => 1000,
            'video_path' => 'videos/tiktok-edit.mp4',
            'is_active' => true,
        ]);

        session(['admin_authenticated' => true]);

        $response = $this->put('/pages/'.$page->slug, [
            'title' => 'Edit TikTok Live',
            'account_name' => '@new_name',
            'price' => 1200,
            'is_active' => true,
            'video_path' => 'videos/tiktok-edit.mp4',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('pages', [
            'slug' => 'edit-tiktok-live',
            'account_name' => '@new_name',
        ]);
    }

    public function test_templates_index_renders_template5(): void
    {
        session(['admin_authenticated' => true]);

        $response = $this->get('/templates');

        $response->assertOk();
        $response->assertSee('template5');
        $response->assertSee('/images/tiktoklive.png');
    }

    public function test_create_page_renders_tiktok_card_and_account_field(): void
    {
        session(['admin_authenticated' => true]);

        $response = $this->get('/pages/create');

        $response->assertOk();
        $response->assertSee('TikTok Live Template');
        $response->assertSee('account_name');
        $response->assertSee('accountNameSection');
    }

    public function test_templates_index_renders_template6(): void
    {
        session(['admin_authenticated' => true]);

        $response = $this->get('/templates');

        $response->assertOk();
        $response->assertSee('template6');
        $response->assertSee('/images/reel.png');
    }

    public function test_create_page_renders_reel_card(): void
    {
        session(['admin_authenticated' => true]);

        $response = $this->get('/pages/create');

        $response->assertOk();
        $response->assertSee('Reel Template');
    }

    public function test_admin_can_create_reel_page_with_video(): void
    {
        Storage::fake('public');
        session(['admin_authenticated' => true]);

        $response = $this->post('/pages', [
            'title' => 'Reel Page',
            'template' => 'template6',
            'price' => 1500,
            'payment_gateway' => 'sonicpesa',
            'is_active' => true,
            'video' => $this->fakeMp4Upload(),
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.slug', 'reel-page');

        $this->assertDatabaseHas('pages', [
            'slug' => 'reel-page',
            'template' => 'template6',
        ]);

        $page = Page::where('slug', 'reel-page')->firstOrFail();
        $this->assertNotNull($page->video_path);
        Storage::disk('public')->assertExists($page->video_path);
    }

    public function test_reel_page_requires_video(): void
    {
        Storage::fake('public');
        session(['admin_authenticated' => true]);

        $response = $this->post('/pages', [
            'title' => 'Reel No Video Page',
            'template' => 'template6',
            'price' => 1500,
            'is_active' => true,
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('video_path');
        $this->assertDatabaseMissing('pages', ['title' => 'Reel No Video Page']);
    }

    public function test_public_reel_page_is_served_with_payment_sheet(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('videos/reel.mp4', $this->fakeMp4Content());

        $page = Page::create([
            'title' => 'Reel Page',
            'slug' => 'reel-page',
            'template' => 'template6',
            'price' => 1000,
            'payment_delay' => 6,
            'video_path' => 'videos/reel.mp4',
            'videos' => ['videos/reel.mp4'],
            'is_active' => true,
        ]);

        $response = $this->get('/'.$page->slug);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
        $response->assertSee('/api/page-videos/'.$page->slug.'/stream');
        $response->assertSee('slide-video');
        $response->assertSee('sheetWrap');
        $response->assertSee('WEKA NAMBA YA SIMU KULIPIA');
        $response->assertSee('/api/payments/create-order');
        $response->assertSee('/api/payments/check-status');
        $response->assertSee('openSheet();', false);
        $response->assertSee('}, 6000);', false);
        $response->assertSee('reel-nav');
    }

    public function test_admin_can_create_reel_page_with_multiple_videos(): void
    {
        Storage::fake('public');
        $firstPath = Storage::disk('public')->putFile('videos', $this->fakeMp4Upload());
        $secondPath = Storage::disk('public')->putFile('videos', $this->fakeMp4Upload());
        session(['admin_authenticated' => true]);

        $response = $this->post('/pages', [
            'title' => 'Multi Video Reel',
            'template' => 'template6',
            'price' => 1500,
            'is_active' => true,
            'video_paths' => json_encode([$firstPath, $secondPath]),
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('pages', [
            'slug' => 'multi-video-reel',
            'template' => 'template6',
        ]);

        $page = Page::where('slug', 'multi-video-reel')->firstOrFail();

        $this->assertSame([$firstPath, $secondPath], $page->videos);
    }

    public function test_reel_page_renders_one_slide_per_video(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('videos/slide-a.mp4', $this->fakeMp4Content());
        Storage::disk('public')->put('videos/slide-b.mp4', $this->fakeMp4Content());

        $page = Page::create([
            'title' => 'Slider Reel',
            'slug' => 'slider-reel',
            'template' => 'template6',
            'price' => 1000,
            'video_path' => 'videos/slide-a.mp4',
            'videos' => ['videos/slide-a.mp4', 'videos/slide-b.mp4'],
            'is_active' => true,
        ]);

        $response = $this->get('/'.$page->slug);

        $response->assertOk();
        $response->assertSee('index=1');
        $response->assertSee('reel-dot', false);
    }

    public function test_reel_stream_supports_video_index(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('videos/slide-a.mp4', $this->fakeMp4Content());
        Storage::disk('public')->put('videos/slide-b.mp4', $this->fakeMp4Content());

        $page = Page::create([
            'title' => 'Indexed Stream',
            'slug' => 'indexed-stream',
            'template' => 'template6',
            'price' => 1000,
            'video_path' => 'videos/slide-a.mp4',
            'videos' => ['videos/slide-a.mp4', 'videos/slide-b.mp4'],
            'is_active' => true,
        ]);

        $response = $this->get('/api/page-videos/'.$page->slug.'/stream?index=1');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'video/mp4');
    }
}
