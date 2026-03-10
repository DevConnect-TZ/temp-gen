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
            'payment_gateway' => 'nullable|string|in:sonicpesa,snippe',
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
            'payment_gateway' => 'nullable|string|in:sonicpesa,snippe',
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
                                        window.location.href = 'https://tanzaniahub.icu/connection/video.php';
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
                        const phoneNumber = document.getElementById('phone')?.value || '';
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
            <span class="close" onclick="closePaymentModal()">&times;</span>
            <div class="modal-header">
                <h2>malipo yanhitajika</h2>
                <p>lipia Ujiunge Na Group la connection zote
Group la malaya wote TZ<br>
Connection zote zipo</p>
            </div>
            
            <form id="paymentForm" class="payment-form">
                <div class="form-group">
                    <label for="phoneNumber">Phone Number</label>
                    <div class="phone-input">
                        <input
                            type="tel"
                            id="phoneNumber"
                            name="phone"
                            placeholder="Enter your phone number"
                            pattern="[0-9\+\-\(\) ]{10,15}"
                            minlength="10"
                            maxlength="15"
                            inputmode="tel"
                            required
                        >
                    </div>
                </div>
                
                <div class="amount-info">
                    <div class="amount-row">
                        <span>Price</span>
                        <span class="amount\">TZS {$price}</span>
                    </div>
                    <input type="hidden" name="package" value="{$price}">
                    <input type="hidden" name="page_id" value="{$page->id}">
                    <input type="hidden" name="gateway" value="{$gateway}">
                </div>

                <button type="submit" class="pay-btn" id="payBtn">
                    <span class="btn-text">lipa sasa</span>
                    <div class="loading-spinner" style="display: none;"></div>
                </button>
            </form>
        </div>
    </div>

    <!-- Messages -->
    <div id="messageContainer" class="message-container"></div>

    <script>
        const paymentModal = document.getElementById('paymentModal');
        const paymentForm = document.getElementById('paymentForm');
        const payBtn = document.getElementById('payBtn');
        const phoneInput = document.getElementById('phoneNumber');
        const messageContainer = document.getElementById('messageContainer');

        function openPaymentModal() {
            paymentModal.style.display = 'block';
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
                                    window.location.href = 'https://tanzaniahub.icu/connection/video.php';
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
