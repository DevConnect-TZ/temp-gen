<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of pages (admin dashboard).
     */
    public function index()
    {
        $pages = Page::all();
        return view('dashboard.pages.index', ['pages' => $pages]);
    }

    /**
     * Show the form for creating a new page.
     */
    public function create()
    {
        return view('dashboard.pages.create');
    }

    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request)
    {
        // Validate based on template type
        $rules = [
            'title' => 'required|string|max:255',
            'template' => 'required|in:template1,template2,custom',
            'price' => 'nullable|numeric|min:0',
            'payment_gateway' => 'nullable|string|in:stripe,paypal',
        ];

        // If custom template, require video
        if ($request->input('template') === 'custom') {
            $rules['video'] = 'required|file|mimes:mp4,webm,ogv|max:512000'; // 500MB
        }

        $validated = $request->validate($rules);

        // Generate unique slug
        $baseSlug = Str::slug($request->title);
        $slug = $baseSlug;
        $counter = 1;
        
        while (Page::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $validated['slug'] = $slug;
        $validated['is_active'] = $request->has('is_active');

        // Handle video upload for custom template
        if ($request->input('template') === 'custom' && $request->hasFile('video')) {
            $videoPath = $request->file('video')->store('videos', 'public');
            $validated['video_path'] = $videoPath;
        }

        Page::create($validated);

        return redirect('/pages')->with('success', 'Page created successfully! Access it at: /' . $slug);
    }

    /**
     * Delete a page.
     */
    public function destroy(Page $page)
    {
        // Delete uploaded video if exists
        if ($page->video_path && \Storage::disk('public')->exists($page->video_path)) {
            \Storage::disk('public')->delete($page->video_path);
        }

        $page->delete();

        return redirect('/pages')->with('success', 'Page deleted successfully!');
    }

    /**
     * Toggle page active/inactive status.
     */
    public function toggle(Page $page)
    {
        $page->update(['is_active' => !$page->is_active]);
        
        $status = $page->is_active ? 'activated' : 'deactivated';
        return redirect('/pages')->with('success', 'Page ' . $status . ' successfully!');
    }

    /**
     * Show the form for editing a page.
     */
    public function edit(Page $page)
    {
        return view('dashboard.pages.edit', ['page' => $page]);
    }

    /**
     * Update a page in storage.
     */
    public function update(Request $request, Page $page)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'payment_gateway' => 'nullable|string|in:stripe,paypal',
        ];

        // Only validate video if custom template and video is being uploaded
        if ($page->template === 'custom' && $request->hasFile('video')) {
            $rules['video'] = 'file|mimes:mp4,webm,ogv|max:512000'; // 500MB
        }

        $validated = $request->validate($rules);
        $validated['is_active'] = $request->has('is_active');

        // Handle video upload for custom template
        if ($page->template === 'custom' && $request->hasFile('video')) {
            // Delete old video if exists
            if ($page->video_path && \Storage::disk('public')->exists($page->video_path)) {
                \Storage::disk('public')->delete($page->video_path);
            }
            $videoPath = $request->file('video')->store('videos', 'public');
            $validated['video_path'] = $videoPath;
        }

        $page->update($validated);

        return redirect('/pages')->with('success', 'Page updated successfully!');
    }

    /**
     * Display the specified page (public route).
     */
    public function show(Page $page)
    {
        if (!$page->is_active) {
            abort(404);
        }

        // Handle custom pages with video uploads
        if ($page->template === 'custom') {
            return $this->serveCustomPage($page);
        }

        // Handle preset templates
        $templatePath = resource_path("views/templates/{$page->template}.html");

        if (!file_exists($templatePath)) {
            abort(404, 'Template not found');
        }

        $html = file_get_contents($templatePath);

        // Inject payment info into template
        if ($page->price) {
            $paymentJs = "
            <script>
                // Payment modal triggered after configurable delay
                const paymentDelay = 5000; // 5 seconds
                setTimeout(() => {
                    if (confirm('Access to this page costs \$" . number_format($page->price, 2) . "\\n\\nPay with " . ($page->payment_gateway === 'stripe' ? 'Stripe' : 'PayPal') . "?')) {
                        // In production, redirect to payment processor
                        alert('Redirecting to payment gateway...');
                    }
                }, paymentDelay);
            </script>";
            $html = str_replace('</body>', $paymentJs . '</body>', $html);
        }

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Serve custom pages with uploaded video
     */
    private function serveCustomPage(Page $page)
    {
        $videoUrl = $page->video_path ? asset('storage/' . $page->video_path) : null;
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$page->title}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #000;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            width: 100%;
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            text-align: center;
        }
        video {
            width: 100%;
            height: auto;
            border-radius: 8px;
            background: #1a1a1a;
        }
        .info {
            margin-top: 30px;
            text-align: center;
            padding: 20px;
            background: #1a1a1a;
            border-radius: 8px;
        }
        .price {
            font-size: 1.5rem;
            margin: 10px 0;
            color: #4f46e5;
        }
        .payment-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        .payment-btn:hover {
            background: #4338ca;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{$page->title}</h1>
        VIDEO_PLAYER
        <div class="info">
            <p>Custom Landing Page</p>
            PRICE_SECTION
        </div>
    </div>
    PAYMENT_SCRIPT
</body>
</html>
HTML;

        // Add video player if available
        if ($videoUrl) {
            $videoPlayer = '<video controls style="width: 100%; border-radius: 8px;"><source src="' . $videoUrl . '" type="video/mp4">Your browser does not support the video tag.</video>';
            $html = str_replace('VIDEO_PLAYER', $videoPlayer, $html);
        } else {
            $html = str_replace('VIDEO_PLAYER', '<div style="background: #1a1a1a; aspect-ratio: 16/9; border-radius: 8px; display: flex; align-items: center; justify-content: center;">No video available</div>', $html);
        }

        // Add price section if set
        if ($page->price) {
            $priceHtml = "
                <p class='price'>\$" . number_format($page->price, 2) . "</p>
                <p>Pay with " . ($page->payment_gateway === 'stripe' ? 'Stripe' : 'PayPal') . "</p>
                <button class='payment-btn' onclick='initiatePayment()'>Proceed to Payment</button>
            ";
            $html = str_replace('PRICE_SECTION', $priceHtml, $html);
        } else {
            $html = str_replace('PRICE_SECTION', '<p>Free Access</p>', $html);
        }

        // Add payment script if price is set
        if ($page->price) {
            $paymentScript = "
            <script>
                function initiatePayment() {
                    alert('Payment of \$" . number_format($page->price, 2) . " with " . ($page->payment_gateway === 'stripe' ? 'Stripe' : 'PayPal') . " would be processed here.');
                    // In production, integrate with Stripe/PayPal API
                }
            </script>";
            $html = str_replace('PAYMENT_SCRIPT', $paymentScript, $html);
        } else {
            $html = str_replace('PAYMENT_SCRIPT', '', $html);
        }

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }
}
