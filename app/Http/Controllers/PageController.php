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
        $price = $page->price ?? 0;
        $gateway = $page->payment_gateway ?? 'stripe';
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            display: flex;
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
                <h2>Payment Required</h2>
                <p>Complete payment to access this exclusive content</p>
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
                            pattern="^[0-9+\\-\\s()]{10,15}$"
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
                        <span class="amount">\${$price}</span>
                    </div>
                    <input type="hidden" name="package" value="{$price}">
                    <input type="hidden" name="page_id" value="{$page->id}">
                    <input type="hidden" name="gateway" value="{$gateway}">
                </div>

                <button type="submit" class="pay-btn" id="payBtn">
                    <span class="btn-text">Proceed to Payment</span>
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

        window.addEventListener('click', (event) => {
            if (event.target === paymentModal) {
                closePaymentModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && paymentModal.style.display === 'block') {
                closePaymentModal();
            }
        });

        // Auto-show payment modal after 6 seconds
        setTimeout(() => {
            openPaymentModal();
        }, 6000);

        paymentForm.addEventListener('submit', handlePayment);

        function resetForm() {
            paymentForm.reset();
            setPayButtonState(false);
            clearMessages();
        }

        async function handlePayment(event) {
            event.preventDefault();

            const phoneNumber = phoneInput.value.trim();

            if (!phoneNumber || phoneNumber.length < 10) {
                showMessage('Please enter a valid phone number', 'error');
                return;
            }

            setPayButtonState(true);
            clearMessages();

            try {
                showMessage('Processing payment..., please wait.', 'info');
                showMessage('Payment modal would integrate with payment gateway here', 'info');
                
                // In production, this would call your payment processor
                // For now, simulate success after 2 seconds
                setTimeout(() => {
                    showMessage('✓ Payment successful! Access granted.', 'success');
                    setPayButtonState(false);
                    setTimeout(() => {
                        closePaymentModal();
                    }, 1500);
                }, 2000);

            } catch (error) {
                console.error('Payment error:', error);
                showMessage('Payment failed. Please try again.', 'error');
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
    </script>
</body>
</html>
HTML;

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }
}
