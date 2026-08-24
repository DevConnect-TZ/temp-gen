<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            'template' => 'required|in:template1,template2,template3,template4,template5,custom',
            'account_name' => 'nullable|required_if:template,template5|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'payment_gateway' => 'nullable|string|in:sonicpesa,snippe,fastlipa,mobilipa,pesalink',
            'pesalink_account_id' => 'nullable|required_if:payment_gateway,pesalink|exists:pesa_link_accounts,id',
            'payment_delay' => 'nullable|integer|min:0',
        ];
        if (in_array($request->input('template'), ['custom', 'template5'], true)) {
            $rules['video'] = 'nullable|file|mimes:mp4,webm,ogv|max:512000'; // 500MB
            $rules['video_path'] = ['required_without:video', 'nullable', 'string', 'regex:/^videos\/[a-zA-Z0-9_.-]+\.(mp4|webm|ogv)$/'];
        }

        $validated = $request->validate($rules);

        // Generate unique slug
        $baseSlug = Str::slug($request->title);
        $slug = $baseSlug;
        $counter = 1;

        while (Page::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $validated['slug'] = $slug;
        $validated['is_active'] = $request->has('is_active');

        // Handle video for custom/tiktok templates (immediate upload or fallback direct file)
        if (in_array($request->input('template'), ['custom', 'template5'], true)) {
            $videoPath = $this->resolveVideoPath($request);

            if ($videoPath === false) {
                return $this->failureResponse($request, 'The video upload was incomplete or corrupted. Please try again.');
            }

            if ($videoPath !== null) {
                $validated['video_path'] = $videoPath;
            }
        }

        $page = Page::create($validated);

        return $this->successResponse($request, 'Page created successfully! Access it at: /'.$slug, $page);
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
        $page->update(['is_active' => ! $page->is_active]);

        $status = $page->is_active ? 'activated' : 'deactivated';

        return redirect('/pages')->with('success', 'Page '.$status.' successfully!');
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
            'account_name' => 'nullable|required_if:template,template5|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'payment_gateway' => 'nullable|string|in:sonicpesa,snippe,fastlipa,mobilipa,pesalink',
            'pesalink_account_id' => 'nullable|required_if:payment_gateway,pesalink|exists:pesa_link_accounts,id',
            'payment_delay' => 'nullable|integer|min:0',
        ];

        // Only validate video if custom/tiktok template
        if (in_array($page->template, ['custom', 'template5'], true)) {
            $rules['video'] = 'nullable|file|mimes:mp4,webm,ogv|max:512000'; // 500MB
            $rules['video_path'] = ['nullable', 'string', 'regex:/^videos\/[a-zA-Z0-9_.-]+\.(mp4|webm|ogv)$/'];
        }

        $validated = $request->validate($rules);
        $validated['is_active'] = $request->has('is_active');

        // Handle video replacement for custom/tiktok templates (immediate upload or fallback direct file)
        if (in_array($page->template, ['custom', 'template5'], true)) {
            $videoPath = $this->resolveVideoPath($request);

            if ($videoPath === false) {
                return $this->failureResponse($request, 'The video upload was incomplete or corrupted. Please try again.');
            }

            if ($videoPath !== null && $videoPath !== $page->video_path) {
                // Delete old video if exists
                if ($page->video_path && Storage::disk('public')->exists($page->video_path)) {
                    Storage::disk('public')->delete($page->video_path);
                }

                $validated['video_path'] = $videoPath;
            }
        }

        $page->update($validated);

        return $this->successResponse($request, 'Page updated successfully!', $page);
    }

    /**
     * Display the specified page (public route).
     */
    public function show(Page $page)
    {
        if (! $page->is_active) {
            abort(404);
        }

        // Handle custom pages with video uploads
        if ($page->template === 'custom') {
            return $this->serveCustomPage($page);
        }

        // Handle TikTok live pages with video uploads
        if ($page->template === 'template5') {
            return $this->serveTikTokLivePage($page);
        }

        // Handle preset templates
        $templatePath = resource_path("views/templates/{$page->template}.html");

        if (! file_exists($templatePath)) {
            abort(404, 'Template not found');
        }

        $html = file_get_contents($templatePath);
        $csrfToken = csrf_token();

        // Inject payment system into template
        if ($page->price) {
            // Inject variables early in the head so template scripts can access them
            $variablesJs = "
            <script>
                // Initialize payment variables immediately
                window.pageId = {$page->id};
                window.pagePrice = {$page->price};
                window.csrfTokenValue = '{$csrfToken}';
            </script>";

            $html = str_replace('</head>', $variablesJs.'</head>', $html);

            $paymentJs = "
            <script>
                // SonicPesa Payment Integration - Additional payment handlers
                // Variables already set above in head

                // Fetch the admin-configured return URL dynamically
                async function getUhondoReturnUrl() {
                    try {
                        const response = await fetch('/api/uhondo-access/config', {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await response.json();
                        return data.redirect_url || data.return_url || '/';
                    } catch (error) {
                        console.error('Uhondo config error:', error);
                        return '/';
                    }
                }

                async function resolveUhondoAccessUrl(transactionId) {
                    try {
                        const response = await fetch('/api/uhondo-access/create', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-Token': window.csrfTokenValue,
                            },
                            body: JSON.stringify({ transaction_id: String(transactionId) }),
                        });

                        const data = await response.json();

                        if (response.ok && data.status === 'success' && data.access_url) {
                            return data.access_url;
                        }

                        return data.redirect_url || (await getUhondoReturnUrl());
                    } catch (error) {
                        console.error('Uhondo access error:', error);
                        return await getUhondoReturnUrl();
                    }
                }

                // Update hardcoded template amounts with dynamic page price
                document.addEventListener('DOMContentLoaded', function() {
                    // === TEMPLATE1 ===
                    // Update modal heading amount (Lipia TSH 2000/= Kuendelea)
                    const heading = document.querySelector('h4.fw-bold');
                    if (heading && heading.textContent.includes('2000')) {
                        heading.textContent = 'Lipia TSH ' + window.pagePrice + '/= Kuendelea';
                    }
                    
                    // Update amount display in form (Tsh 2000)
                    const amountSpan = document.querySelector('span.fw-bold.text-primary');
                    if (amountSpan && amountSpan.textContent.includes('2000')) {
                        amountSpan.textContent = 'Tsh ' + window.pagePrice;
                    }
                    
                    // Update hidden package input
                    const packageInput = document.getElementById('package3');
                    if (packageInput) {
                        packageInput.value = window.pagePrice;
                    }

                    // === TEMPLATE2 ===
                    // Replace all 'TSH 1000' displays with dynamic price
                    document.querySelectorAll('span.card-price').forEach(el => {
                        if (el.textContent.includes('1000')) {
                            el.textContent = 'TSH ' + window.pagePrice;
                        }
                    });

                    // Replace price-amount display
                    const priceAmountDiv = document.querySelector('.price-amount');
                    if (priceAmountDiv && priceAmountDiv.textContent.includes('1000')) {
                        priceAmountDiv.textContent = 'TSH ' + window.pagePrice;
                    }

                    // Replace hero description amount if it mentions price
                    const heroDesc = document.querySelector('.hero-desc');
                    if (heroDesc && heroDesc.textContent.includes('1000')) {
                        heroDesc.textContent = heroDesc.textContent.replace(/tsh 1000/i, 'tsh ' + window.pagePrice);
                    }

                    // Replace row title amount if it mentions price
                    const rowTitle = document.querySelector('.row-title');
                    if (rowTitle && rowTitle.textContent.includes('1000')) {
                        rowTitle.textContent = rowTitle.textContent.replace(/TSH 1000/i, 'TSH ' + window.pagePrice);
                    }

                    // === TEMPLATE3 ===
                    // Override hardcoded price with dynamic page price
                    if (typeof currentPrice !== 'undefined') {
                        currentPrice = window.pagePrice;
                    }
                    const t3Price = document.getElementById('display-price');
                    if (t3Price) {
                        t3Price.textContent = 'TSh ' + Number(window.pagePrice).toLocaleString();
                    }

                    // Override openPlayer to always use page price and skip external video previews
                    window.openPlayer = function(idx, videoId, price) {
                        currentVideoId = videoId;
                        currentPrice = window.pagePrice;
                        if (t3Price) {
                            t3Price.textContent = 'TSh ' + Number(window.pagePrice).toLocaleString();
                        }
                        togglePay(true);
                    };

                    // Override handlePayment to use Laravel backend with visual feedback
                    window.handlePayment = async function() {
                        const phoneEl = document.getElementById('phone');
                        if (!phoneEl) return;
                        const phone = phoneEl.value.trim();
                        if (!phone || phone.length < 10) {
                            showMsg('Tafadhali weka namba ya simu sahihi', 'error');
                            return;
                        }

                        // Disable button and show spinner immediately
                        const btn = document.getElementById('pay-btn');
                        const btnText = btn?.querySelector('.btn-text');
                        const btnSpinner = btn?.querySelector('.loading-spinner');
                        if (btn) {
                            btn.disabled = true;
                            if (btnText) btnText.style.display = 'none';
                            if (btnSpinner) btnSpinner.style.display = 'inline';
                        }

                        try {
                            const response = await fetch('/api/payments/create-order', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-Token': window.csrfTokenValue,
                                },
                                body: JSON.stringify({
                                    page_id: window.pageId,
                                    buyer_phone: phone,
                                    buyer_name: 'Customer',
                                    buyer_email: 'customer@example.com',
                                }),
                            });

                            const data = await response.json();

                            if (!response.ok || data.status !== 'success') {
                                showMsg(data.message || 'Imeshindwa kuanzisha malipo.', 'error');
                                if (btn) {
                                    btn.disabled = false;
                                    if (btnText) btnText.style.display = 'inline';
                                    if (btnSpinner) btnSpinner.style.display = 'none';
                                }
                                return;
                            }

                            // Show native waiting UI
                            showWaiting(data.data.transaction_id || data.data.order_id);

                            // Poll our Laravel check-status endpoint
                            let pollCount = 0;
                            const maxPolls = 30;
                            const transactionId = data.data.transaction_id;

                            const pollInterval = setInterval(async () => {
                                pollCount++;
                                try {
                                    const statusResponse = await fetch('/api/payments/check-status', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-Token': window.csrfTokenValue,
                                        },
                                        body: JSON.stringify({ transaction_id: transactionId }),
                                    });

                                    const statusData = await statusResponse.json();

                                    if (statusResponse.ok && statusData.status === 'success') {
                                        const status = (statusData.payment_status || '').toUpperCase();

                                        if (status === 'COMPLETED') {
                                            clearInterval(pollInterval);
                                            showSuccess(data.data.order_id || transactionId);
                                            setTimeout(async () => {
                                                window.location.href = await resolveUhondoAccessUrl(transactionId);
                                            }, 2000);
                                            return;
                                        } else if (status === 'CANCELLED' || status === 'FAILED' || status === 'REJECTED') {
                                            clearInterval(pollInterval);
                                            showFailed();
                                            return;
                                        }
                                    }
                                } catch (e) {
                                    console.error('Polling error:', e);
                                }

                                if (pollCount >= maxPolls) {
                                    clearInterval(pollInterval);
                                    showTimeout(transactionId);
                                }
                            }, 3000);

                        } catch (error) {
                            console.error('Payment error:', error);
                            showMsg('Hitilafu ya mtandao. Jaribu tena.', 'error');
                            if (btn) {
                                btn.disabled = false;
                                if (btnText) btnText.style.display = 'inline';
                                if (btnSpinner) btnSpinner.style.display = 'none';
                            }
                        }
                    };

                    // Update amount variable for template2 payment form
                    window.amount = window.pagePrice;
                });

                // Patch the payment form submission
                function handleTemplatePayment(phoneNumber) {
                    if (!phoneNumber || phoneNumber.length < 10) {
                        if (typeof showToastNotification === 'function') {
                            showToastNotification('Invalid Phone', 'Please enter a valid phone number', 'error');
                        } else {
                            alert('Please enter a valid phone number');
                        }
                        return;
                    }

                    createPaymentOrder(phoneNumber);
                }

                async function createPaymentOrder(phoneNumber) {
                    try {
                        const payButton = document.getElementById('payButton');
                        const loadingButton = document.getElementById('loadingButton');
                        
                        if (payButton && loadingButton) {
                            payButton.style.display = 'none';
                            loadingButton.style.display = 'block';
                        }
                        
                        const response = await fetch('/api/payments/create-order', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-Token': window.csrfTokenValue,
                            },
                            body: JSON.stringify({
                                page_id: window.pageId,
                                buyer_phone: phoneNumber,
                                buyer_name: document.getElementById('fullName')?.value || document.getElementById('firstname')?.value || 'Customer',
                                buyer_email: document.getElementById('email')?.value || 'customer@example.com',
                            }),
                        });

                        const data = await response.json();

                        if (!response.ok || data.status !== 'success') {
                            if (typeof showToastNotification === 'function') {
                                showToastNotification('Error', data.message || 'Failed to create payment order', 'error');
                            } else {
                                alert(data.message || 'Failed to create payment order');
                            }
                            if (payButton && loadingButton) {
                                payButton.style.display = 'block';
                                loadingButton.style.display = 'none';
                            }
                            return;
                        }

                        currentTransactionId = data.data.transaction_id;
                        currentOrderId = data.data.order_id || data.data.reference; // Support both gateways
                        if (typeof showToastNotification === 'function') {
                            showToastNotification('Payment Processing', 'Check your phone for payment prompt', 'success');
                            if (typeof showPaymentInstructions === 'function') {
                                showPaymentInstructions();
                            }
                        }
                        
                        // Start polling payment status
                        pollPaymentStatus();
                    } catch (error) {
                        console.error('Payment error:', error);
                        if (typeof showToastNotification === 'function') {
                            showToastNotification('Error', 'Payment error: ' + error.message, 'error');
                        } else {
                            alert('Payment error: ' + error.message);
                        }
                        const payButton = document.getElementById('payButton');
                        const loadingButton = document.getElementById('loadingButton');
                        if (payButton && loadingButton) {
                            payButton.style.display = 'block';
                            loadingButton.style.display = 'none';
                        }
                    }
                }

                function pollPaymentStatus() {
                    let pollCount = 0;
                    const maxPolls = 30; // 1.5 minutes with 3-second intervals

                    pollingInterval = setInterval(async () => {
                        pollCount++;

                        try {
                            const response = await fetch('/api/payments/check-status', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-Token': window.csrfTokenValue,
                                },
                                body: JSON.stringify({ transaction_id: currentTransactionId }),
                            });

                            const data = await response.json();

                            if (response.ok && data.status === 'success') {
                                const status = data.payment_status || data.statusMessage;
                                
                                // Handle both SonicPesa (COMPLETED) and Snippe (completed) status formats
                                if (status === 'COMPLETED' || status === 'completed') {
                                    clearInterval(pollingInterval);
                                    if (typeof showToastNotification === 'function') {
                                        showToastNotification('Success', '✓ Payment successful! Access granted.', 'success');
                                    }
                                    // Close modal and redirect after 2 seconds
                                    setTimeout(() => {
                                        if (typeof downloadModal !== 'undefined') {
                                            downloadModal.hide();
                                        }
                                        resolveUhondoAccessUrl(currentTransactionId).then((accessUrl) => {
                                            window.location.href = accessUrl;
                                        });
                                    }, 2000);
                                    return;
                                } else if (status === 'CANCELLED' || status === 'canceled' || status === 'REJECTED' || status === 'USERCANCELLED') {
                                    clearInterval(pollingInterval);
                                    if (typeof showToastNotification === 'function') {
                                        showToastNotification('Cancelled', 'Payment was cancelled. Please try again.', 'error');
                                    }
                                    return;
                                }
                            }
                        } catch (error) {
                            console.error('Status check error:', error);
                        }

                        if (pollCount >= maxPolls) {
                            clearInterval(pollingInterval);
                            if (typeof showToastNotification === 'function') {
                                showToastNotification('Timeout', 'Payment took too long. Please try again.', 'error');
                            }
                        }
                    }, 3000); // Poll every 3 seconds
                }

                // Intercept form submission for template1
                if (document.getElementById('paymentForm')) {
                    // Neutralize template1's jQuery handler that posts to legacy PHP endpoints
                    if (typeof $ !== 'undefined' && typeof $.fn !== 'undefined') {
                        try { $('#paymentForm').off('submit'); } catch(e) {}
                    }
                    document.getElementById('paymentForm').addEventListener('submit', function(e) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        const phoneNumber = document.getElementById('phoneInput')?.value || '';
                        handleTemplatePayment(phoneNumber);
                    }, true);
                }

                // Intercept form submission for template2
                if (document.getElementById('emailInput')) {
                    const originalProcessPayment = window.processPayment;
                    window.processPayment = async function() {
                        const phoneNumber = document.getElementById('phoneInput')?.value || '';
                        if (phoneNumber) {
                            handleTemplatePayment(phoneNumber);
                        }
                    };
                }

                // Auto-show payment modal using template's native function
                setTimeout(() => {
                    if (typeof downloadModal !== 'undefined') {
                        // template1 Bootstrap modal
                        downloadModal.show();
                    }
                    // template2 has its own modal logic - only opens when user plays video for 5 seconds
                }, 6000); // 6 seconds delay
            </script>";

            $html = str_replace('</body>', $paymentJs.'</body>', $html);
        }

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Serve custom pages with uploaded video
     */
    private function serveCustomPage(Page $page)
    {
        $videoUrl = $page->video_path ? route('api.page-videos.stream', $page, false) : null;
        $price = $page->price ?? 0;
        $formattedPrice = number_format((float) $price);
        $gateway = $page->payment_gateway ?? 'stripe';
        $csrfToken = csrf_token();
        $paymentDelay = ($page->payment_delay ?? 0) > 0 ? $page->payment_delay : 4;
        $paymentDelayMs = $paymentDelay * 1000;

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{$csrfToken}">
    <title>{$page->title}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        .video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            z-index: -1;
        }

        /* Payment Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.30);
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .pm-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.35);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
            max-height: 92vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .pay-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            padding: 22px 24px 18px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .pay-header::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }

        .pay-header::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 90px;
            height: 90px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
        }

        .pay-header h2 {
            font-size: 17px;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
            position: relative;
            z-index: 1;
        }

        .price-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 50px;
            padding: 6px 18px;
            margin-top: 8px;
            position: relative;
            z-index: 1;
        }

        .price-tag .amount {
            font-size: 26px;
            font-weight: 800;
            color: #4ade80;
            letter-spacing: -0.5px;
        }

        .price-tag .currency {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.6);
        }

        .perks {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 14px 18px 0;
        }

        .perk {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            background: #f8faff;
            border: 1px solid #e8edf8;
            border-radius: 12px;
            padding: 9px 11px;
        }

        .perk-icon { font-size: 17px; flex-shrink: 0; margin-top: 1px; }
        .perk-text { font-size: 11px; font-weight: 600; color: #374151; line-height: 1.4; }

        .networks-label {
            font-size: 11px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            text-align: center;
            margin: 14px 0 8px;
        }

        .networks {
            display: flex;
            justify-content: center;
            gap: 6px;
            padding: 0 18px;
            flex-wrap: wrap;
        }

        .net {
            display: flex;
            align-items: center;
            gap: 5px;
            border-radius: 9px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 700;
            border: 1.5px solid;
            white-space: nowrap;
        }

        .net-mpesa { background: #fff0f0; border-color: #e60026; color: #b0001d; }
        .net-mpesa .nd { background: #e60026; }
        .net-tigo { background: #f0f4ff; border-color: #0057a8; color: #003d7a; }
        .net-tigo .nd { background: #0057a8; }
        .net-airtel { background: #fff4f0; border-color: #e5251d; color: #a81a14; }
        .net-airtel .nd { background: #e5251d; }
        .net-halo { background: #fff8f0; border-color: #f7941d; color: #b5690d; }
        .net-halo .nd { background: #f7941d; }
        .nd { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

        .form-area { padding: 14px 18px 18px; }

        .phone-wrap { position: relative; margin-bottom: 13px; }

        .phone-prefix {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .phone-flag { font-size: 17px; }
        .phone-sep { width: 1px; height: 16px; background: #d1d5db; margin: 0 4px; }

        .phone-inp {
            width: 100%;
            border: 2px solid #e5e7eb;
            border-radius: 13px;
            padding: 14px 14px 14px 86px;
            font-size: 17px;
            font-family: inherit;
            font-weight: 600;
            color: #111;
            background: #f9fafb;
            outline: none;
            transition: all 0.2s;
            letter-spacing: 0.5px;
        }

        .phone-inp:focus {
            border-color: #16a34a;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(22,163,74,0.1);
        }

        .phone-inp::placeholder {
            color: #d1d5db;
            font-weight: 400;
            font-size: 14px;
            letter-spacing: 0;
        }

        .pay-btn {
            width: 100%;
            border: none;
            border-radius: 13px;
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: #fff;
            font-family: inherit;
            font-size: 16px;
            font-weight: 800;
            padding: 16px;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(22,163,74,0.35);
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
        }

        .pay-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s;
        }

        .pay-btn:hover::before { left: 100%; }

        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(22,163,74,0.45);
        }

        .pay-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .load-btn {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 13px;
            background: linear-gradient(135deg, #16a34a, #15803d);
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(22,163,74,0.35);
        }

        .spin {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid #fff;
            border-radius: 50%;
            animation: sp 0.7s linear infinite;
        }

        .sec-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 11px;
            font-size: 12px;
            color: #9ca3af;
        }

        #pi { display: none; padding: 22px; }

        .pi-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f0fdf4;
            border-top: 5px solid #16a34a;
            border-radius: 50%;
            animation: sp 0.8s linear infinite;
            margin: 0 auto 16px;
        }

        #pi h4 {
            font-size: 18px;
            font-weight: 800;
            color: #111;
            text-align: center;
            margin-bottom: 5px;
        }

        #pi p {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
            margin-bottom: 18px;
        }

        .ii {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            background: #f0fdf4;
            border-radius: 11px;
            border-left: 4px solid #16a34a;
            margin-bottom: 9px;
        }

        .ii-ic { font-size: 24px; flex-shrink: 0; }
        .ii-tx { font-size: 13px; font-weight: 600; color: #166534; }

        .ld {
            display: inline-flex;
            gap: 4px;
            align-items: center;
            margin-left: 5px;
            vertical-align: middle;
        }

        .ld span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #16a34a;
            animation: bounce 1.2s infinite;
        }

        .ld span:nth-child(2) { animation-delay: 0.2s; }
        .ld span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes sp { to { transform: rotate(360deg); } }
        @keyframes bounce { 0%, 80%, 100% { transform: scale(0.6); } 40% { transform: scale(1); } }

        @media (max-width: 480px) {
            .perks { grid-template-columns: 1fr; }
            .pay-header h2 { font-size: 15px; }
            .price-tag .amount { font-size: 22px; }
        }

        .message-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
        }

        .message {
            background: white;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 4px solid;
            animation: slideInRight 0.3s ease;
        }

        .message.success { border-left-color: #28a745; }
        .message.error { border-left-color: #dc3545; }
        .message.info { border-left-color: #17a2b8; }

        @keyframes slideInRight {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
    </style>
</head>
<body>
    <video class="video" autoplay loop muted playsinline preload="auto">
        <source src="{$videoUrl}" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="pm-card">
            <!-- Step 1: Payment Form -->
            <div id="dc">
                <div class="pay-header">
                    <h2 id="modalTitle">🔥 LIPIA TSH {$formattedPrice}/= KUENDELEA</h2>
                    <div class="price-tag">
                        <span class="currency">TSh</span>
                        <span class="amount" id="modalPrice">{$formattedPrice}</span>
                        <span class="currency">tu!</span>
                    </div>
                </div>

                <div class="perks">
                    <div class="perk"><span class="perk-icon">🎬</span><span class="perk-text">Video za moto za bongo</span></div>
                    <div class="perk"><span class="perk-icon">👥</span><span class="perk-text">Groups za wakubwa TZ</span></div>
                    <div class="perk"><span class="perk-icon">📱</span><span class="perk-text">Video 20+ kila siku</span></div>
                    <div class="perk"><span class="perk-icon">📞</span><span class="perk-text">Video call inapatikana</span></div>
                </div>

                <div class="networks-label">Lipa kupitia</div>
                <div class="networks">
                    <div class="net net-mpesa"><span class="nd"></span>M-Pesa</div>
                    <div class="net net-tigo"><span class="nd"></span>Mixx by Yas</div>
                    <div class="net net-airtel"><span class="nd"></span>Airtel Money</div>
                    <div class="net net-halo"><span class="nd"></span>Halopesa</div>
                </div>

                <div class="form-area">
                    <form id="paymentForm">
                        <input type="hidden" name="package" value="{$price}">
                        <input type="hidden" name="page_id" value="{$page->id}">
                        <input type="hidden" name="gateway" value="{$gateway}">
                        <div class="phone-wrap">
                            <div class="phone-prefix">
                                <span class="phone-flag">🇹🇿</span>
                                <span class="phone-sep"></span>
                            </div>
                            <input
                                type="tel"
                                id="phoneInput"
                                name="phone"
                                class="phone-inp"
                                placeholder="07XX XXX XXX"
                                pattern="[0-9\+\-\(\) ]{10,15}"
                                minlength="10"
                                maxlength="15"
                                inputmode="tel"
                                required
                                autocomplete="tel"
                            >
                        </div>
                        <button type="submit" class="pay-btn" id="payBtn">
                            <span class="btn-text" id="payBtnText">💳 LIPIA TSh {$formattedPrice} SASA</span>
                        </button>
                        <div class="load-btn" id="lb"><div class="spin"></div></div>
                        <div class="sec-note">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Malipo salama • SSL Encrypted • PesaLink
                        </div>
                    </form>
                </div>
            </div>

            <!-- Step 2: Payment Instructions -->
            <div id="pi">
                <div class="pi-spinner"></div>
                <h4>Endelea kulipa... 💚</h4>
                <p>Tafadhali kamilisha malipo kwa simu yako</p>
                <div class="ii"><span class="ii-ic">📱</span><span class="ii-tx">Angalia simu yako kwa USSD prompt</span></div>
                <div class="ii"><span class="ii-ic">🔑</span><span class="ii-tx">Weka PIN yako kukamilisha</span></div>
                <div class="ii"><span class="ii-ic">⏳</span><span class="ii-tx">Inasubiri uthibitisho <span class="ld"><span></span><span></span><span></span></span></span></div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <div id="messageContainer" class="message-container"></div>

    <script>
        const paymentModal = document.getElementById('paymentModal');
        const paymentForm = document.getElementById('paymentForm');
        const payBtn = document.getElementById('payBtn');
        const phoneInput = document.getElementById('phoneInput');
        const messageContainer = document.getElementById('messageContainer');

        function syncModalContent() {
            const amount = Number({$price}).toLocaleString('en-US');
            const modalTitle = document.getElementById('modalTitle');
            const modalPrice = document.getElementById('modalPrice');
            const payBtnText = document.getElementById('payBtnText');

            if (modalTitle) {
                modalTitle.textContent = '🔥 LIPIA TSH ' + amount + '/= KUENDELEA';
            }

            if (modalPrice) {
                modalPrice.textContent = amount;
            }

            if (payBtnText) {
                payBtnText.textContent = '💳 LIPIA TSh ' + amount + ' SASA';
            }
        }

        function showPI() {
            document.getElementById('dc').style.display = 'none';
            document.getElementById('pi').style.display = 'block';
        }

        function showDC() {
            document.getElementById('pi').style.display = 'none';
            document.getElementById('dc').style.display = 'block';
        }

        function openPaymentModal() {
            syncModalContent();
            paymentModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            phoneInput.focus();
        }

        function closePaymentModal() {
            paymentModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            resetForm();
        }

        // Modal cannot be closed by clicking outside
        // Event handler removed

        // Modal cannot be closed by Escape key
        // Event handler removed

        // Video plays for the page's configured delay, then the payment modal pops up
        document.addEventListener('DOMContentLoaded', function() {
            syncModalContent();
            setTimeout(function() {
                openPaymentModal();
            }, {$paymentDelayMs});
        });

        paymentForm.addEventListener('submit', handlePayment);

        async function resolveUhondoAccessUrl(transactionId) {
            try {
                const response = await fetch('/api/uhondo-access/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ transaction_id: transactionId }),
                });

                const data = await response.json();

                if (response.ok && data.status === 'success' && data.access_url) {
                    return data.access_url;
                }

                return data.redirect_url || 'https://uhondo.online';
            } catch (error) {
                console.error('Uhondo access error:', error);
                return 'https://uhondo.online';
            }
        }

        function resetForm() {
            paymentForm.reset();
            setPayButtonState(false);
            showDC();
            clearMessages();
        }

        async function handlePayment(event) {
            event.preventDefault();

            const phoneNumber = phoneInput.value.trim();
            const pageId = paymentForm.querySelector('input[name="page_id"]').value;

            if (!phoneNumber || phoneNumber.length < 10) {
                showMessage('Please enter a valid phone number (10-15 digits)', 'error');
                return;
            }

            setPayButtonState(true);
            clearMessages();

            try {
                // Step 1: Create payment order
                showMessage('Creating payment order...', 'info');
                
                const createResponse = await fetch('/api/payments/create-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        page_id: pageId,
                        buyer_phone: phoneNumber,
                    }),
                });

                const createData = await createResponse.json();

                if (!createResponse.ok || createData.status !== 'success') {
                    showMessage(createData.message || 'Failed to create payment order', 'error');
                    setPayButtonState(false);
                    return;
                }

                const transactionId = createData.data.transaction_id;
                showMessage('Check your phone for USSD payment prompt...', 'info');
                showPI();
                
                // Step 2: Poll payment status every 4 seconds
                let statusCheckCount = 0;
                const maxAttempts = 30; // Poll for max 2 minutes (30 * 4 seconds)
                
                const statusInterval = setInterval(async () => {
                    statusCheckCount++;

                    try {
                        const statusResponse = await fetch('/api/payments/check-status', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({ transaction_id: transactionId }),
                        });

                        // Check if response is valid JSON
                        if (!statusResponse.headers.get('content-type')?.includes('application/json')) {
                            console.error('Invalid response type:', statusResponse.headers.get('content-type'));
                            return;
                        }

                        const statusData = await statusResponse.json();

                        if (statusResponse.ok && statusData.status === 'success') {
                            const paymentStatus = (statusData.payment_status || '').toUpperCase();

                            if (paymentStatus === 'COMPLETED') {
                                clearInterval(statusInterval);
                                showMessage('✓ Payment successful! Access granted.', 'success');
                                setPayButtonState(false);
                                setTimeout(async () => {
                                    closePaymentModal();
                                    window.location.href = await resolveUhondoAccessUrl(transactionId);
                                }, 1500);
                                return;
                            } else if (paymentStatus === 'CANCELLED' || paymentStatus === 'REJECTED' || paymentStatus === 'USERCANCELLED') {
                                clearInterval(statusInterval);
                                showMessage('Payment was cancelled or rejected. Please try again.', 'error');
                                setPayButtonState(false);
                                showDC();
                                return;
                            }
                            // PENDING or INPROGRESS - keep polling
                        }
                    } catch (error) {
                        console.error('Status check error:', error);
                        // Continue polling on error
                    }

                    // Stop polling after max attempts
                    if (statusCheckCount >= maxAttempts) {
                        clearInterval(statusInterval);
                        showMessage('Payment is taking too long. Please check your phone and try again.', 'error');
                        setPayButtonState(false);
                        showDC();
                    }
                }, 4000); // Poll every 4 seconds

            } catch (error) {
                console.error('Payment error:', error);
                showMessage('Payment error: ' + error.message, 'error');
                setPayButtonState(false);
            }
        }

        function setPayButtonState(loading) {
            const lb = document.getElementById('lb');

            if (loading) {
                payBtn.disabled = true;
                payBtn.style.display = 'none';
                lb.style.display = 'flex';
            } else {
                payBtn.disabled = false;
                payBtn.style.display = 'block';
                lb.style.display = 'none';
            }
        }

        function showMessage(text, type = 'info') {
            const message = document.createElement('div');
            message.className = `message \${type}`;
            message.textContent = text;
            
            messageContainer.appendChild(message);
            
            setTimeout(() => {
                if (message.parentNode) {
                    message.remove();
                }
            }, 4000);
        }

        function clearMessages() {
            messageContainer.innerHTML = '';
        }
    </script>
</body>
</html>
HTML;

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Serve a TikTok-style live page with the uploaded video, a mocked
     * live audience (views, joining users, likes) and the uploader's
     * account name shown at the top right like TikTok LIVE.
     */
    private function serveTikTokLivePage(Page $page)
    {
        $videoUrl = $page->video_path ? route('api.page-videos.stream', $page, false) : null;
        $price = $page->price ?? 0;
        $formattedPrice = number_format((float) $price);
        $accountName = $page->account_name ?: $page->title;
        $accountNameJs = json_encode($accountName, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);
        $gateway = $page->payment_gateway ?? 'stripe';
        $csrfToken = csrf_token();
        $paymentDelay = ($page->payment_delay ?? 0) > 0 ? $page->payment_delay : 4;
        $paymentDelayMs = $paymentDelay * 1000;

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{$csrfToken}">
    <title>{$page->title}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            overflow: hidden;
            background: #000;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        .live-stage {
            position: fixed;
            inset: 0;
            background: #000;
        }

        .live-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            z-index: 1;
        }

        .live-gradient {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(0,0,0,0.45) 0%, transparent 25%, transparent 70%, rgba(0,0,0,0.55) 100%);
        }

        /* Top bar */
        .top-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 3;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 14px;
        }

        .live-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(0,0,0,0.45);
            border-radius: 999px;
            padding: 6px 14px;
        }

        .live-pill .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fe2c55;
            animation: pulse 1.2s infinite;
        }

        .live-pill .live-text {
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .viewer-count {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }

        .viewer-count svg {
            flex-shrink: 0;
        }

        /* Account name top-right like TikTok LIVE */
        .host-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(0,0,0,0.45);
            border-radius: 999px;
            padding: 6px 8px 6px 14px;
            max-width: 55vw;
        }

        .host-chip .host-name {
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .host-chip .host-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fe2c55, #ff6b6b);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
            border: 2px solid #fff;
        }

        .host-chip .plus {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #fe2c55;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* Right rail - joining users + actions */
        .right-rail {
            position: absolute;
            right: 10px;
            bottom: 0;
            top: 64px;
            z-index: 4;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            gap: 16px;
            pointer-events: none;
        }

        .join-stack {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .join-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            animation: popIn 0.5s ease;
            transform-origin: center bottom;
        }

        .join-avatar.fade-out {
            animation: fadeSlide 0.6s ease forwards;
        }

        .action-btn {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: rgba(255,255,255,0.14);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            color: #fff;
            font-size: 20px;
            pointer-events: auto;
            cursor: pointer;
            user-select: none;
            border: none;
            font-family: inherit;
            transition: transform 0.1s;
        }

        .action-btn:active {
            transform: scale(0.85);
        }

        .action-btn .badge {
            font-size: 10px;
            font-weight: 700;
        }

        .like-btn .heart-burst {
            position: absolute;
            font-size: 28px;
            pointer-events: none;
            animation: floatUp 1.2s ease forwards;
        }

        /* Bottom area - comments + input */
        .bottom-area {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 4;
            padding: 0 12px 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .comments {
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-height: 34vh;
            overflow: hidden;
        }

        .comment {
            align-self: flex-start;
            max-width: 78%;
            background: rgba(0,0,0,0.35);
            border-radius: 12px;
            padding: 6px 10px;
            font-size: 12.5px;
            color: #fff;
            line-height: 1.35;
            animation: slideUp 0.4s ease;
        }

        .comment .c-name {
            font-weight: 700;
            margin-right: 6px;
            color: #f7f7f8;
        }

        .comment .c-host {
            color: #fff;
            font-weight: 700;
            background: rgba(254,44,85,0.9);
            border-radius: 4px;
            padding: 0 4px;
            margin-right: 6px;
            font-size: 11px;
        }

        .comment .c-text {
            color: #f5f5f5;
        }

        .comment .c-join {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        .comment .c-join .mini-avatar {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .gift-banner {
            align-self: flex-start;
            background: linear-gradient(90deg, rgba(254,44,85,0.85), rgba(255,107,107,0.85));
            border-radius: 12px;
            padding: 6px 12px;
            font-size: 12.5px;
            color: #fff;
            font-weight: 600;
            animation: slideUp 0.4s ease;
        }

        .live-input {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.12);
            border-radius: 999px;
            padding: 8px 14px;
        }

        .live-input input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: #fff;
            font-size: 14px;
            font-family: inherit;
        }

        .live-input input::placeholder {
            color: rgba(255,255,255,0.7);
        }

        .live-input .send-btn {
            background: #fe2c55;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        /* Floating hearts */
        .floating-heart {
            position: absolute;
            bottom: 60px;
            right: 60px;
            font-size: 30px;
            z-index: 5;
            animation: floatUp 1.6s ease forwards;
            pointer-events: none;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.85); }
        }

        @keyframes popIn {
            0% { transform: scale(0) translateY(20px); opacity: 0; }
            60% { transform: scale(1.15) translateY(-4px); opacity: 1; }
            100% { transform: scale(1) translateY(0); opacity: 1; }
        }

        @keyframes fadeSlide {
            0% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-30px); }
        }

        @keyframes floatUp {
            0% { transform: translateY(0) scale(0.8); opacity: 0; }
            15% { opacity: 1; }
            100% { transform: translateY(-120px) scale(1.1); opacity: 0; }
        }

        @keyframes slideUp {
            from { transform: translateY(12px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Payment Modal - TikTok-style dark bottom sheet */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.72);
            align-items: flex-end;
            justify-content: center;
            padding: 0;
        }

        .pm-card {
            background: #16181d;
            border-radius: 24px 24px 0 0;
            box-shadow: 0 -16px 60px rgba(0,0,0,0.6);
            overflow: hidden;
            width: 100%;
            max-width: 520px;
            max-height: 92vh;
            overflow-y: auto;
            animation: modalSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            scrollbar-width: none;
        }

        .pm-card::-webkit-scrollbar { display: none; }

        @keyframes modalSlideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .sheet-grabber {
            width: 44px;
            height: 5px;
            border-radius: 3px;
            background: rgba(255,255,255,0.22);
            margin: 10px auto 0;
        }

        .pay-header {
            background: linear-gradient(135deg, #fe2c55 0%, #ff4d6d 60%, #7c2d5b 130%);
            padding: 18px 24px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .pay-header::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 140px;
            height: 140px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }

        .pay-header::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 160px;
            height: 160px;
            background: rgba(0,0,0,0.12);
            border-radius: 50%;
        }

        .live-badge-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .live-badge-row .live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #fff;
            animation: pulse 1.2s infinite;
        }

        .live-badge-row .live-tag {
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
        }

        .pay-header h2 {
            font-size: 19px;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
            position: relative;
            z-index: 1;
        }

        .pay-header .unlock-sub {
            font-size: 12px;
            color: rgba(255,255,255,0.85);
            position: relative;
            z-index: 1;
            margin-bottom: 10px;
        }

        .price-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 50px;
            padding: 6px 18px;
            position: relative;
            z-index: 1;
        }

        .price-tag .amount {
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .price-tag .currency {
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.75);
        }

        .perks {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 14px 18px 0;
        }

        .perk {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            background: #20242b;
            border: 1px solid #2a2f38;
            border-radius: 12px;
            padding: 9px 11px;
        }

        .perk-icon { font-size: 17px; flex-shrink: 0; margin-top: 1px; }
        .perk-text { font-size: 11px; font-weight: 600; color: #d1d5db; line-height: 1.4; }

        .networks-label {
            font-size: 11px;
            font-weight: 700;
            color: #8b919c;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            text-align: center;
            margin: 14px 0 8px;
        }

        .networks {
            display: flex;
            justify-content: center;
            gap: 6px;
            padding: 0 18px;
            flex-wrap: wrap;
        }

        .net {
            display: flex;
            align-items: center;
            gap: 5px;
            border-radius: 9px;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 700;
            border: 1.5px solid;
            white-space: nowrap;
            color: #e5e7eb;
        }

        .net-mpesa { background: rgba(230,0,38,0.12); border-color: #e60026; }
        .net-tigo { background: rgba(0,87,168,0.14); border-color: #1a73c4; }
        .net-airtel { background: rgba(229,37,29,0.12); border-color: #e5251d; }
        .net-halo { background: rgba(247,148,29,0.12); border-color: #f7941d; }
        .nd { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; background: currentColor; }

        .form-area { padding: 14px 18px 22px; }

        .phone-wrap { position: relative; margin-bottom: 13px; }

        .phone-prefix {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .phone-flag { font-size: 17px; }
        .phone-sep { width: 1px; height: 16px; background: #3a404a; margin: 0 4px; }

        .phone-inp {
            width: 100%;
            border: 2px solid #2a2f38;
            border-radius: 13px;
            padding: 14px 14px 14px 86px;
            font-size: 17px;
            font-family: inherit;
            font-weight: 600;
            color: #fff;
            background: #20242b;
            outline: none;
            transition: all 0.2s;
            letter-spacing: 0.5px;
        }

        .phone-inp::placeholder { color: #6b7280; font-weight: 400; }

        .phone-inp:focus {
            border-color: #fe2c55;
            background: #20242b;
            box-shadow: 0 0 0 4px rgba(254,44,85,0.15);
        }

        .pay-btn {
            width: 100%;
            border: none;
            border-radius: 13px;
            background: linear-gradient(135deg, #fe2c55 0%, #ff6b6b 100%);
            color: #fff;
            font-family: inherit;
            font-size: 16px;
            font-weight: 800;
            padding: 16px;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(254,44,85,0.4);
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
        }

        .pay-btn:disabled { opacity: 0.7; cursor: not-allowed; }

        .load-btn {
            width: 100%;
            height: 54px;
            border-radius: 13px;
            background: linear-gradient(135deg, #fe2c55, #ff6b6b);
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(254,44,85,0.4);
        }

        .spin {
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid #fff;
            border-radius: 50%;
            animation: sp 0.7s linear infinite;
        }

        @keyframes sp { to { transform: rotate(360deg); } }

        .sec-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 11px;
            font-size: 12px;
            color: #8b919c;
        }

        #pi { display: none; padding: 24px 22px 26px; }

        .pi-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(254,44,85,0.15);
            border-top: 5px solid #fe2c55;
            border-radius: 50%;
            animation: sp 0.8s linear infinite;
            margin: 0 auto 16px;
        }

        #pi h4 {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
            text-align: center;
            margin-bottom: 5px;
        }

        #pi p {
            font-size: 13px;
            color: #9ca3af;
            text-align: center;
            margin-bottom: 18px;
        }

        .ii {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px;
            background: #20242b;
            border-radius: 11px;
            border-left: 4px solid #fe2c55;
            margin-bottom: 9px;
        }

        .ii-ic { font-size: 24px; flex-shrink: 0; }
        .ii-tx { font-size: 13px; font-weight: 600; color: #e5e7eb; }

        .ld {
            display: inline-flex;
            gap: 4px;
            align-items: center;
            margin-left: 5px;
            vertical-align: middle;
        }

        .ld span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #fe2c55;
            animation: bounce 1.2s infinite;
        }

        .ld span:nth-child(2) { animation-delay: 0.2s; }
        .ld span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes bounce { 0%, 80%, 100% { transform: scale(0.6); } 40% { transform: scale(1); } }

        .message-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
        }

        .message {
            background: white;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 4px solid;
            animation: slideInRight 0.3s ease;
        }

        .message.success { border-left-color: #28a745; }
        .message.error { border-left-color: #dc3545; }
        .message.info { border-left-color: #17a2b8; }

        @keyframes slideInRight {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
    </style>
</head>
<body>
    <div class="live-stage">
        <video class="live-video" autoplay loop muted playsinline preload="auto">
            <source src="{$videoUrl}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="live-gradient"></div>

        <!-- Top bar -->
        <div class="top-bar">
            <div class="live-pill">
                <span class="dot"></span>
                <span class="live-text">LIVE</span>
                <span class="viewer-count" id="viewerCount">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    <span id="viewerValue">1.2K</span>
                </span>
            </div>

            <!-- Uploader account name, top-right like TikTok LIVE -->
            <div class="host-chip" id="hostChip">
                <span class="host-name" id="hostName">{$accountName}</span>
                <span class="host-avatar" id="hostAvatar">@</span>
                <span class="plus">+</span>
            </div>
        </div>

        <!-- Right rail: joining users + actions -->
        <div class="right-rail">
            <div class="join-stack" id="joinStack"></div>
            <button class="action-btn" id="likeBtn" title="Like">
                <span class="heart-burst" style="display:none">❤️</span>
                <span>❤️</span>
                <span class="badge" id="likeCount">128</span>
            </button>
            <button class="action-btn" title="Gift">
                <span>🎁</span>
                <span class="badge">Gifts</span>
            </button>
            <button class="action-btn" title="Share">
                <span>↗️</span>
            </button>
        </div>

        <!-- Bottom area -->
        <div class="bottom-area">
            <div class="comments" id="comments"></div>
            <div class="live-input">
                <input id="chatInput" type="text" placeholder="Say something...">
                <button class="send-btn" id="sendBtn">➤</button>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="pm-card">
            <div class="sheet-grabber"></div>
            <div id="dc">
                <div class="pay-header">
                    <div class="live-badge-row">
                        <span class="live-dot"></span>
                        <span class="live-tag">LIVE UNLOCK</span>
                    </div>
                    <h2 id="modalTitle">Unlock this LIVE</h2>
                    <div class="unlock-sub" id="modalSub">Join the live now</div>
                    <div class="price-tag">
                        <span class="currency">TSh</span>
                        <span class="amount" id="modalPrice">{$formattedPrice}</span>
                        <span class="currency">tu!</span>
                    </div>
                </div>

                <div class="perks">
                    <div class="perk"><span class="perk-icon">🎬</span><span class="perk-text">Video za moto za bongo</span></div>
                    <div class="perk"><span class="perk-icon">👥</span><span class="perk-text">Groups za wakubwa TZ</span></div>
                    <div class="perk"><span class="perk-icon">📱</span><span class="perk-text">Video 20+ kila siku</span></div>
                    <div class="perk"><span class="perk-icon">📞</span><span class="perk-text">Video call inapatikana</span></div>
                </div>

                <div class="networks-label">Lipa kupitia</div>
                <div class="networks">
                    <div class="net net-mpesa"><span class="nd"></span>M-Pesa</div>
                    <div class="net net-tigo"><span class="nd"></span>Mixx by Yas</div>
                    <div class="net net-airtel"><span class="nd"></span>Airtel Money</div>
                    <div class="net net-halo"><span class="nd"></span>Halopesa</div>
                </div>

                <div class="form-area">
                    <form id="paymentForm">
                        <input type="hidden" name="package" value="{$price}">
                        <input type="hidden" name="page_id" value="{$page->id}">
                        <input type="hidden" name="gateway" value="{$gateway}">
                        <div class="phone-wrap">
                            <div class="phone-prefix">
                                <span class="phone-flag">🇹🇿</span>
                                <span class="phone-sep"></span>
                            </div>
                            <input type="tel" id="phoneInput" name="phone" class="phone-inp" placeholder="07XX XXX XXX"
                                pattern="[0-9\+\-\(\) ]{10,15}" minlength="10" maxlength="15" inputmode="tel"
                                required autocomplete="tel">
                        </div>
                        <button type="submit" class="pay-btn" id="payBtn">
                            <span class="btn-text" id="payBtnText">💳 Unlock for TSh {$formattedPrice}</span>
                        </button>
                        <div class="load-btn" id="lb"><div class="spin"></div></div>
                        <div class="sec-note">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Malipo salama • SSL Encrypted • PesaLink
                        </div>
                    </form>
                </div>
            </div>

            <div id="pi">
                <div class="pi-spinner"></div>
                <h4>Endelea kulipa... 💚</h4>
                <p>Tafadhali kamilisha malipo kwa simu yako</p>
                <div class="ii"><span class="ii-ic">📱</span><span class="ii-tx">Angalia simu yako kwa USSD prompt</span></div>
                <div class="ii"><span class="ii-ic">🔑</span><span class="ii-tx">Weka PIN yako kukamilisha</span></div>
                <div class="ii"><span class="ii-ic">⏳</span><span class="ii-tx">Inasubiri uthibitisho <span class="ld"><span></span><span></span><span></span></span></span></div>
            </div>
        </div>
    </div>

    <div id="messageContainer" class="message-container"></div>

    <script>
        const hostName = {$accountNameJs};
        const hostAvatar = document.getElementById('hostAvatar');
        hostAvatar.textContent = hostName.charAt(0).toUpperCase();

        const AVATAR_COLORS = ['#fe2c55', '#25f4ee', '#7c4dff', '#f7941d', '#22c55e', '#3b82f6', '#ec4899', '#f59e0b'];
        const MOCK_NAMES = ['Juma', 'Asha', 'Neema', 'Baraka', 'Zawadi', 'Moses', 'Rehema', 'Kelvin', 'Amina', 'Daudi', 'Grace', 'Emmanuel', 'Pendo', 'Salim', 'Joyce', 'Frank', 'Hawa', 'Lucas', 'Mariamu', 'Erick', 'Brenda'];
        const MOCK_COMMENTS = [
            'Tuko wengi kwa content ya wakubwa 🔞', 'Hii video ya watu wazima inapendeza 🔥', 'Na support content hii 🙌', 'Mimi nipo hapa kwa wakubwa tu 🔞',
            'Content 18+ ni moto 🔥🔥', 'Naomba video za wakubwa zaidi', 'Hii account ina content nzuri sana', 'Support wakubwa wote!',
            'Ukipost video ya wakubwa nishikilie 💯', 'Content hii ni ya watu wazima tu', 'Mkuu tupe zile za moto za wakubwa', '18+ content ni bora kabisa',
            'Na follow kwa content za wakubwa', 'Wewe ni king wa content ya watu wazima 👑', 'Video hii ya wakubwa ni kali', 'Na support kwa pesa kaa 💸',
            'Tufungulie zile video za wakubwa', 'Content 18+ ndiyo bora', 'Wakubwa wote wapo hapa 🔞', 'Hii ni live bora ya watu wazima',
            'Nitumie namba yako', 'Nitumie inbox', 'Mmoja aje dm', 'Kubbke', 'Oyaah sio pw', 'Utamu tu', 'Aibu utaona wewe',
        ];
        const GIFT_MESSAGES = [
            { emoji: '🎁', text: 'sent Gift' },
            { emoji: '🌹', text: 'sent Rose' },
            { emoji: '👑', text: 'sent Crown' },
            { emoji: '🚀', text: 'sent Rocket' },
            { emoji: '💎', text: 'sent Diamond' },
        ];

        // Random per-user base values so every visitor sees a different live
        let baseViews;
        try {
            baseViews = parseInt(localStorage.getItem('ttlive_views_' + window.location.pathname), 10);
            if (isNaN(baseViews)) {
                baseViews = 300 + Math.floor(Math.random() * 4800);
                localStorage.setItem('ttlive_views_' + window.location.pathname, String(baseViews));
            }
        } catch (e) {
            baseViews = 300 + Math.floor(Math.random() * 4800);
        }

        let views = baseViews;
        let likes = Math.floor(Math.random() * 500) + 50;

        const viewerValue = document.getElementById('viewerValue');
        const likeCount = document.getElementById('likeCount');

        function formatCount(n) {
            if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
            if (n >= 1000) return (n / 1000).toFixed(1) + 'K';
            return String(n);
        }

        function updateViewers() {
            viewerValue.textContent = formatCount(views);
        }

        function updateLikes() {
            likeCount.textContent = formatCount(likes);
        }

        updateViewers();
        updateLikes();

        // Live viewer count drifts up over time
        setInterval(function () {
            views += Math.floor(Math.random() * 4);
            updateViewers();
        }, 3000);

        // Mock users joining
        const joinStack = document.getElementById('joinStack');
        const comments = document.getElementById('comments');

        function randomColor() {
            return AVATAR_COLORS[Math.floor(Math.random() * AVATAR_COLORS.length)];
        }

        function randomName() {
            return MOCK_NAMES[Math.floor(Math.random() * MOCK_NAMES.length)];
        }

        function addJoiningUser() {
            const name = randomName();
            const avatar = document.createElement('div');
            avatar.className = 'join-avatar';
            avatar.style.background = randomColor();
            avatar.textContent = name.charAt(0);
            joinStack.appendChild(avatar);

            const joinMsg = document.createElement('div');
            joinMsg.className = 'comment';
            joinMsg.innerHTML = '<span class="c-join"><span class="mini-avatar" style="background:' + randomColor() + '">' + name.charAt(0) + '</span> <span class="c-name">' + name + '</span> joined</span>';
            comments.appendChild(joinMsg);
            trimComments();

            setTimeout(function () {
                avatar.classList.add('fade-out');
                setTimeout(function () { avatar.remove(); }, 600);
            }, 3000 + Math.random() * 2000);

            views += 1;
            updateViewers();
        }

        // Seed a few initial comments
        function seedComment() {
            const name = randomName();
            const text = MOCK_COMMENTS[Math.floor(Math.random() * MOCK_COMMENTS.length)];
            const comment = document.createElement('div');
            comment.className = 'comment';
            comment.innerHTML = '<span class="c-name" style="color:' + randomColor() + '">' + name + '</span><span class="c-text">' + text + '</span>';
            comments.appendChild(comment);
            trimComments();
        }

        // Random comments + joins every few seconds
        setInterval(function () {
            const r = Math.random();
            if (r < 0.3) {
                addJoiningUser();
            } else {
                seedComment();
            }
        }, 2600);

        // Occasional gift banner
        setInterval(function () {
            const giver = randomName();
            const gift = GIFT_MESSAGES[Math.floor(Math.random() * GIFT_MESSAGES.length)];
            const banner = document.createElement('div');
            banner.className = 'gift-banner';
            banner.textContent = gift.emoji + ' ' + giver + ' ' + gift.text;
            comments.appendChild(banner);
            trimComments();
        }, 9000);

        function trimComments() {
            while (comments.children.length > 30) {
                comments.firstChild.remove();
            }
        }

        // Likes
        const likeBtn = document.getElementById('likeBtn');
        likeBtn.addEventListener('click', function () {
            likes += 1;
            updateLikes();
            spawnHeart();
        });

        function spawnHeart() {
            const heart = document.createElement('div');
            heart.className = 'floating-heart';
            heart.textContent = ['❤️', '🔥', '👍', '💜'][Math.floor(Math.random() * 4)];
            heart.style.right = (30 + Math.random() * 60) + 'px';
            heart.style.fontSize = (24 + Math.random() * 20) + 'px';
            document.body.appendChild(heart);
            setTimeout(function () { heart.remove(); }, 1700);
        }

        // Likes keep growing all the time, with periodic heart bursts
        setInterval(function () {
            const burst = 1 + Math.floor(Math.random() * 4);
            likes += burst;
            updateLikes();
            for (let i = 0; i < burst; i++) {
                setTimeout(spawnHeart, i * 180);
            }
        }, 2500);

        // Chat input
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendBtn');

        function sendOwnMessage() {
            const value = chatInput.value.trim();
            if (!value) return;
            const comment = document.createElement('div');
            comment.className = 'comment';
            comment.innerHTML = '<span class="c-host">You</span><span class="c-text">' + value.replace(/</g, '&lt;') + '</span>';
            comments.appendChild(comment);
            trimComments();
            chatInput.value = '';
        }

        sendBtn.addEventListener('click', sendOwnMessage);
        chatInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') sendOwnMessage();
        });

        // Trying to write a comment instantly opens the payment modal,
        // while liking stays free for the viewer.
        chatInput.addEventListener('focus', function () {
            chatInput.blur();
            openPaymentModal();
        });

        // Seed initial activity
        for (let i = 0; i < 5; i++) seedComment();

        // === Payment flow (same as custom template) ===
        const paymentModal = document.getElementById('paymentModal');
        const paymentForm = document.getElementById('paymentForm');
        const payBtn = document.getElementById('payBtn');
        const phoneInput = document.getElementById('phoneInput');
        const messageContainer = document.getElementById('messageContainer');

        function syncModalContent() {
            const amount = Number({$price}).toLocaleString('en-US');
            document.getElementById('modalTitle').textContent = 'Unlock this LIVE';
            document.getElementById('modalSub').textContent = 'TSh ' + amount + '/= to join ' + hostName;
            document.getElementById('modalPrice').textContent = amount;
            document.getElementById('payBtnText').textContent = '💳 Unlock for TSh ' + amount;
        }

        function showPI() {
            document.getElementById('dc').style.display = 'none';
            document.getElementById('pi').style.display = 'block';
        }

        function showDC() {
            document.getElementById('pi').style.display = 'none';
            document.getElementById('dc').style.display = 'block';
        }

        function openPaymentModal() {
            syncModalContent();
            paymentModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            phoneInput.focus();
        }

        function closePaymentModal() {
            paymentModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            resetForm();
        }

        document.addEventListener('DOMContentLoaded', function () {
            syncModalContent();
            setTimeout(function () {
                openPaymentModal();
            }, {$paymentDelayMs});
        });

        async function resolveUhondoAccessUrl(transactionId) {
            try {
                const response = await fetch('/api/uhondo-access/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ transaction_id: transactionId }),
                });
                const data = await response.json();
                if (response.ok && data.status === 'success' && data.access_url) {
                    return data.access_url;
                }
                return data.redirect_url || 'https://uhondo.online';
            } catch (error) {
                return 'https://uhondo.online';
            }
        }

        function resetForm() {
            paymentForm.reset();
            setPayButtonState(false);
            showDC();
            clearMessages();
        }

        async function handlePayment(event) {
            event.preventDefault();
            const phoneNumber = phoneInput.value.trim();
            const pageId = paymentForm.querySelector('input[name="page_id"]').value;

            if (!phoneNumber || phoneNumber.length < 10) {
                showMessage('Please enter a valid phone number (10-15 digits)', 'error');
                return;
            }

            setPayButtonState(true);
            clearMessages();

            try {
                showMessage('Creating payment order...', 'info');
                const createResponse = await fetch('/api/payments/create-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        page_id: pageId,
                        buyer_phone: phoneNumber,
                    }),
                });

                const createData = await createResponse.json();

                if (!createResponse.ok || createData.status !== 'success') {
                    showMessage(createData.message || 'Failed to create payment order', 'error');
                    setPayButtonState(false);
                    return;
                }

                const transactionId = createData.data.transaction_id;
                showMessage('Check your phone for USSD payment prompt...', 'info');
                showPI();

                let statusCheckCount = 0;
                const maxAttempts = 30;

                const statusInterval = setInterval(async () => {
                    statusCheckCount++;
                    try {
                        const statusResponse = await fetch('/api/payments/check-status', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({ transaction_id: transactionId }),
                        });

                        if (!statusResponse.headers.get('content-type')?.includes('application/json')) {
                            return;
                        }

                        const statusData = await statusResponse.json();

                        if (statusResponse.ok && statusData.status === 'success') {
                            const paymentStatus = (statusData.payment_status || '').toUpperCase();

                            if (paymentStatus === 'COMPLETED') {
                                clearInterval(statusInterval);
                                showMessage('✓ Payment successful! Access granted.', 'success');
                                setPayButtonState(false);
                                setTimeout(async () => {
                                    closePaymentModal();
                                    window.location.href = await resolveUhondoAccessUrl(transactionId);
                                }, 1500);
                                return;
                            } else if (paymentStatus === 'CANCELLED' || paymentStatus === 'REJECTED' || paymentStatus === 'USERCANCELLED') {
                                clearInterval(statusInterval);
                                showMessage('Payment was cancelled or rejected. Please try again.', 'error');
                                setPayButtonState(false);
                                showDC();
                                return;
                            }
                        }
                    } catch (error) {
                        // Continue polling on error
                    }

                    if (statusCheckCount >= maxAttempts) {
                        clearInterval(statusInterval);
                        showMessage('Payment is taking too long. Please check your phone and try again.', 'error');
                        setPayButtonState(false);
                        showDC();
                    }
                }, 4000);
            } catch (error) {
                showMessage('Payment error: ' + error.message, 'error');
                setPayButtonState(false);
            }
        }

        function setPayButtonState(loading) {
            const lb = document.getElementById('lb');
            if (loading) {
                payBtn.disabled = true;
                payBtn.style.display = 'none';
                lb.style.display = 'flex';
            } else {
                payBtn.disabled = false;
                payBtn.style.display = 'block';
                lb.style.display = 'none';
            }
        }

        function showMessage(text, type) {
            type = type || 'info';
            const message = document.createElement('div');
            message.className = 'message ' + type;
            message.textContent = text;
            messageContainer.appendChild(message);
            setTimeout(function () {
                if (message.parentNode) {
                    message.remove();
                }
            }, 4000);
        }

        function clearMessages() {
            messageContainer.innerHTML = '';
        }

        paymentForm.addEventListener('submit', handlePayment);
    </script>
</body>
</html>
HTML;

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Stream the custom page background video with HTTP range support
     * so browsers can seek and buffer efficiently.
     */
    public function streamVideo(Page $page): BinaryFileResponse
    {
        abort_unless($page->is_active, 404);
        abort_unless($page->video_path, 404);
        abort_unless(Storage::disk('public')->exists($page->video_path), 404);

        return response()
            ->file(Storage::disk('public')->path($page->video_path), [
                'Content-Type' => Storage::disk('public')->mimeType($page->video_path) ?? 'video/mp4',
                'Cache-Control' => 'public, max-age=3600',
            ]);
    }

    /**
     * Immediately store a background video uploaded by the dashboard
     * and return its storage path so the page form can reference it.
     */
    public function uploadVideo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'video' => 'required|file|mimes:mp4,webm,ogv|max:512000', // 500MB
        ]);

        $videoPath = $request->file('video')->store('videos', 'public');

        if (! $this->isStoredVideoIntact($videoPath)) {
            Storage::disk('public')->delete($videoPath);

            return response()->json([
                'status' => 'error',
                'message' => 'The video upload was incomplete or corrupted. Please try again.',
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'data' => ['video_path' => $videoPath],
        ]);
    }

    /**
     * Resolve the video to use for a page from either a pre-uploaded
     * video_path (immediate upload) or a direct file upload (fallback).
     *
     * @return string|false|null false on corrupted upload, null when absent
     */
    private function resolveVideoPath(Request $request): string|false|null
    {
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('videos', 'public');

            if (! $this->isStoredVideoIntact($videoPath)) {
                Storage::disk('public')->delete($videoPath);

                return false;
            }

            return $videoPath;
        }

        $videoPath = $request->input('video_path');

        if (is_string($videoPath) && $videoPath !== '') {
            return Storage::disk('public')->exists($videoPath) ? $videoPath : false;
        }

        return null;
    }

    /**
     * Verify the uploaded video was fully written to disk and is non-empty.
     */
    private function isStoredVideoIntact(string $videoPath): bool
    {
        $disk = Storage::disk('public');

        return $disk->exists($videoPath) && $disk->size($videoPath) > 0;
    }

    /**
     * Redirect for regular form posts, JSON payload for XHR uploads.
     *
     * @return JsonResponse|RedirectResponse
     */
    private function successResponse(Request $request, string $message, Page $page)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'id' => $page->id,
                    'slug' => $page->slug,
                    'video_path' => $page->video_path,
                    'redirect' => route('pages.index'),
                ],
            ]);
        }

        return redirect('/pages')->with('success', $message);
    }

    /**
     * Redirect back with an error for regular form posts, JSON error for XHR uploads.
     *
     * @return JsonResponse|RedirectResponse
     */
    private function failureResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], 422);
        }

        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['video' => $message]);
    }
}
