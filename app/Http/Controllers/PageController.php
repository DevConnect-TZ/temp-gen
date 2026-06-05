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
            'template' => 'required|in:template1,template2,template3,custom',
            'price' => 'nullable|numeric|min:0',
            'payment_gateway' => 'nullable|string|in:sonicpesa,snippe,fastlipa,mobilipa,pesalink',
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
            $slug = $baseSlug.'-'.$counter;
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

        return redirect('/pages')->with('success', 'Page created successfully! Access it at: /'.$slug);
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
            'price' => 'nullable|numeric|min:0',
            'payment_gateway' => 'nullable|string|in:sonicpesa,snippe,fastlipa,mobilipa,pesalink',
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
        if (! $page->is_active) {
            abort(404);
        }

        // Handle custom pages with video uploads
        if ($page->template === 'custom') {
            return $this->serveCustomPage($page);
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
                        const phone = document.getElementById('phone').value.trim();
                        if (!phone || phone.length < 10) {
                            showMsg('Tafadhali weka namba ya simu sahihi', 'error');
                            return;
                        }

                        // Disable button and show spinner immediately
                        const btn = document.getElementById('pay-btn');
                        if (btn) {
                            btn.disabled = true;
                            btn.querySelector('.btn-text').style.display = 'none';
                            btn.querySelector('.loading-spinner').style.display = 'inline';
                        }

                        try {
                            const response = await fetch('/api/payments/create-order', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
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
                                    btn.querySelector('.btn-text').style.display = 'inline';
                                    btn.querySelector('.loading-spinner').style.display = 'none';
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
                                            setTimeout(() => {
                                                window.location.href = 'https://utamu.nyonyatu.store';
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
                                btn.querySelector('.btn-text').style.display = 'inline';
                                btn.querySelector('.loading-spinner').style.display = 'none';
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
                                        window.location.href = 'https://utamu.nyonyatu.store';
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
        $videoUrl = $page->video_path ? asset('storage/'.$page->video_path) : null;
        $price = $page->price ?? 0;
        $gateway = $page->payment_gateway ?? 'stripe';
        $csrfToken = csrf_token();

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{$csrfToken}">
    <title>{$page->title}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.3) 100%);
            display: none;
            align-items: center;
            justify-content: center;
        }

        .content {
            text-align: center;
            color: white;
            max-width: 600px;
            padding: 2rem;
        }

        .content h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .download-btn {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,123,255,0.4);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            padding: 30px 30px 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
            position: relative;
        }

        .modal-header h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .modal-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
            display: none;
        }

        .close:hover {
            color: #000;
        }

        .payment-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .phone-input input {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .phone-input input:focus {
            outline: none;
            border-color: #007bff;
        }

        .input-help {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }

        .amount-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            color: #333;
        }

        .amount {
            color: #007bff;
            font-size: 1.1rem;
        }

        .pay-btn {
            width: 100%;
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 15px;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .pay-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(40,167,69,0.3);
        }

        .pay-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .modal .modal-content {
            background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
            border-radius: 36px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.65);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
        }

        .payment-modal {
            padding: 22px 18px 16px;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 18px;
        }

        .payment-title {
            color: #294f8e;
            font-size: 1.9rem;
            line-height: 0.98;
            font-weight: 900;
            letter-spacing: -0.03em;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .payment-title i {
            color: #1f6cf0;
            font-size: 1.1em;
        }

        .payment-copy {
            margin: 16px auto 0;
            background: linear-gradient(180deg, #edf5ff 0%, #e6f1ff 100%);
            border-radius: 24px;
            padding: 20px 18px;
            color: #2a4e8b;
            font-size: 1.08rem;
            line-height: 1.5;
            font-weight: 600;
            border: 1px solid #d8e7ff;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.55);
        }

        .payment-copy i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 6px;
            background: #294f8e;
            color: #fff;
            font-size: 0.95rem;
        }

        .price-box {
            text-align: center;
            margin: 18px 0 22px;
        }

        .price-amount {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 210px;
            padding: 12px 28px;
            border-radius: 999px;
            background: linear-gradient(180deg, #315a9e 0%, #244b8e 100%);
            color: #fff;
            font-size: 1.7rem;
            font-weight: 900;
            box-shadow: 0 12px 24px rgba(39, 72, 135, 0.28);
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-size: 1.03rem;
            color: #2d4d83;
            font-weight: 800;
        }

        .form-group input {
            width: 100%;
            padding: 17px 20px;
            background: #f4f7fd;
            border: 2px solid #e3e9f3;
            border-radius: 999px;
            color: #294f8e;
            font-size: 1.02rem;
            font-weight: 700;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #315a9e;
            box-shadow: 0 0 0 4px rgba(49, 90, 158, 0.12);
        }

        .form-group input::placeholder {
            color: #b0b8c7;
            font-weight: 700;
        }

        .submit-btn {
            width: 100%;
            padding: 16px 18px;
            background: linear-gradient(180deg, #315a9e 0%, #244b8e 100%);
            border: none;
            border-radius: 999px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 900;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 12px 24px rgba(39, 72, 135, 0.26);
        }

        .submit-btn:hover {
            transform: translateY(-1px);
        }

        .submit-btn:disabled {
            background: #8693aa;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .submit-btn i {
            font-size: 1rem;
        }

        .info-box {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 18px;
            padding: 16px 18px;
            background: linear-gradient(180deg, #edf5ff 0%, #e8f2ff 100%);
            border-radius: 22px;
            border: 1px solid #dae7fb;
            color: #2b4e84;
        }

        .info-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #315a9e;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex: 0 0 auto;
            box-shadow: 0 10px 18px rgba(49, 90, 158, 0.2);
        }

        .info-copy strong {
            display: block;
            font-size: 1.05rem;
            font-weight: 900;
            line-height: 1.2;
        }

        .info-copy span {
            display: block;
            color: #6a7fa4;
            font-size: 0.95rem;
            line-height: 1.2;
            margin-top: 2px;
        }

        .modal-close-text {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            margin-top: 16px;
            background: transparent;
            border: none;
            color: #8a94a7;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
        }

        .modal-close-text i {
            font-size: 0.85rem;
        }

        @media (max-width: 768px) {
            .modal-content {
                border-radius: 28px;
            }

            .payment-modal {
                padding: 18px 14px 14px;
            }

            .payment-title {
                font-size: 1.55rem;
            }

            .payment-copy {
                font-size: 0.98rem;
                padding: 16px 14px;
            }

            .price-amount {
                min-width: 180px;
                font-size: 1.45rem;
            }

            .form-group label {
                font-size: 0.95rem;
            }

            .form-group input {
                padding: 15px 16px;
                font-size: 0.96rem;
            }
        }

        .waiting-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        .step {
            padding: 8px 0;
            border-left: 3px solid #007bff;
            padding-left: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 0 5px 5px 0;
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

        @media (max-width: 768px) {
            .content h1 { font-size: 2rem; }
            .modal-content { margin: 10% auto; width: 95%; }
            .modal-header, .payment-form { padding: 20px; }
        }
    </style>
</head>
<body>
    <video class="video" autoplay loop muted playsinline>
        <source src="{$videoUrl}" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="overlay">
        <div class="content">
            <h1>{$page->title}</h1>
            <button class="download-btn" onclick="openPaymentModal()">
                <span>Get Access</span>
            </button>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="payment-modal">
                <div class="payment-header">
                    <div class="payment-title" id="modalTitle">
                        <i class="bi bi-gem"></i>
                        <span>LIPIA TSH {$price}/= KUENDELEA</span>
                    </div>
                    <div class="payment-copy" id="modalCopy">
                        <i class="bi bi-crown-fill"></i>
                        <span>Tazama connections kali za bongo, jiunge na groups za wakubwa, kuwa updated kwa video za moto kupitiza app, video 20 kila siku, Huduma ya makojozo kwa video call inapatikana</span>
                    </div>
                </div>

                <div class="price-box">
                    <div class="price-amount" id="modalPrice">TSh {$price}</div>
                </div>

                <form id="paymentForm" class="payment-form">
                    <input type="hidden" name="package" value="{$price}">
                    <input type="hidden" name="page_id" value="{$page->id}">
                    <input type="hidden" name="gateway" value="{$gateway}">

                    <div class="form-group">
                        <label for="phoneInput"><i class="bi bi-phone"></i> Namba ya simu (M-Pesa, Tigo Pesa, Airtel Money, Halopesa)</label>
                        <div class="phone-input">
                            <input
                                type="tel"
                                id="phoneInput"
                                name="phone"
                                placeholder="07xxxxxxxx au 06xxxxxxxx"
                                pattern="[0-9\+\-\(\) ]{10,15}"
                                minlength="10"
                                maxlength="15"
                                inputmode="tel"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="submit-btn" id="payBtn">
                        <i class="bi bi-lock-fill"></i>
                        <span class="btn-text" id="payBtnText">LIPIA TSh {$price}</span>
                        <div class="loading-spinner" style="display: none;"></div>
                    </button>
                </form>

                <div id="paymentInstructions" class="payment-instructions" style="display: none;">
                    <div class="payment-instructions-spinner"></div>
                    <h4 class="fw-bold text-center mb-4" style="color: #28a745;">Endelea kulipia...</h4>
                    <p class="text-center mb-4" style="color: #666; font-size: 16px;">Tafashali Kamilisha malipo kwa simu yako</p>

                    <div class="instruction-item">
                        <span class="instruction-icon">📱</span>
                        <span class="instruction-text">Check your phone for USSD prompt</span>
                    </div>

                    <div class="instruction-item">
                        <span class="instruction-icon">💳</span>
                        <span class="instruction-text">Enter your PIN to complete payment</span>
                    </div>

                    <div class="instruction-item">
                        <span class="instruction-icon">⏳</span>
                        <span class="instruction-text">Waiting for confirmation
                            <div class="loading-dots">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </span>
                    </div>
                </div>

                <div class="info-box">
                    <div class="info-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div class="info-copy">
                        <strong>Lipia kufungua zote</strong>
                        <span>Pata ruhusa ya kuangalia video zote</span>
                    </div>
                </div>

                <button type="button" class="modal-close-text" onclick="closePaymentModal()">
                    <i class="bi bi-x-circle-fill"></i>
                    <span>Funga</span>
                </button>
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
                modalTitle.innerHTML = '<i class="bi bi-gem"></i><span>LIPIA TSH ' + amount + '/= KUENDELEA</span>';
            }

            if (modalPrice) {
                modalPrice.textContent = 'TSh ' + amount;
            }

            if (payBtnText) {
                payBtnText.textContent = 'LIPIA TSh ' + amount;
            }
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

        // Auto-open payment modal on page load with 4 second delay
        document.addEventListener('DOMContentLoaded', function() {
            syncModalContent();
            setTimeout(function() {
                openPaymentModal();
            }, 4000);
        });

        paymentForm.addEventListener('submit', handlePayment);

        function resetForm() {
            paymentForm.reset();
            setPayButtonState(false);
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
                                setTimeout(() => {
                                    closePaymentModal();
                                    window.location.href = 'https://utamu.nyonyatu.store';
                                }, 1500);
                                return;
                            } else if (paymentStatus === 'CANCELLED' || paymentStatus === 'REJECTED' || paymentStatus === 'USERCANCELLED') {
                                clearInterval(statusInterval);
                                showMessage('Payment was cancelled or rejected. Please try again.', 'error');
                                setPayButtonState(false);
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
                    }
                }, 4000); // Poll every 4 seconds

            } catch (error) {
                console.error('Payment error:', error);
                showMessage('Payment error: ' + error.message, 'error');
                setPayButtonState(false);
            }
        }

        function setPayButtonState(loading) {
            const btnText = payBtn.querySelector('.btn-text');
            const spinner = payBtn.querySelector('.loading-spinner');
            
            if (loading) {
                payBtn.disabled = true;
                btnText.style.display = 'none';
                spinner.style.display = 'block';
            } else {
                payBtn.disabled = false;
                btnText.style.display = 'block';
                spinner.style.display = 'none';
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

        // Auto-open payment modal on page load with 4 second delay
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                openPaymentModal();
            }, 4000);
        });
    </script>
</body>
</html>
HTML;

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }
}
