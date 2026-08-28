@extends('layouts.app')

@section('title', 'Edit Page')
@section('page_title', 'Edit: ' . $page->title)

@section('content')
<form method="POST" action="{{ route('pages.update', $page) }}" class="space-y-8 max-w-4xl">
    @csrf
    @method('PUT')

    <!-- Page Title Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Page Information</h2>

        <!-- Page Title -->
        <div class="mb-6">
            <label for="title" class="block text-sm font-medium text-gray-900 mb-2">Page Title</label>
            <input
                type="text"
                id="title"
                name="title"
                placeholder="Enter page title"
                value="{{ old('title', $page->title) }}"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            >
            <p class="text-xs text-gray-600 mt-1">This title will appear in the page header</p>
        </div>

        <!-- Page Slug (Display Only) -->
        <div class="mb-6">
            <label for="slug" class="block text-sm font-medium text-gray-900 mb-2">Page Slug</label>
            <div class="flex gap-2">
                <input
                    type="text"
                    id="slug"
                    readonly
                    value="{{ $page->slug }}"
                    class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-600 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                >
            </div>
            <p class="text-xs text-gray-600 mt-1">URL: <strong>/{{ $page->slug }}</strong></p>
        </div>
    </div>

    <!-- Template Information Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Template Information</h2>

        <!-- Template Preview -->
        <div class="mb-6 border border-gray-200 rounded-lg overflow-hidden">
            <div class="h-40 bg-gray-900 flex items-center justify-center">
                @if($page->template === 'template1')
                    <img src="/images/youtubex.jpeg" alt="YouTubeX Template" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <svg class="w-12 h-12 text-gray-600 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @elseif($page->template === 'template2')
                    <img src="/images/utamuplus.png" alt="UTAMU+ Template" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <svg class="w-12 h-12 text-gray-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16a1 1 0 001 1h8a1 1 0 001-1V4m0 0H4m12 0h4"/>
                    </svg>
            @elseif($page->template === 'template3')
                <img src="/images/template3.png" alt="MAUTAMU Template" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
            @elseif($page->template === 'template4')
                <img src="/images/template4.png" alt="WhatsApp Group Template" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
            @elseif($page->template === 'template5')
                <img src="/images/tiktoklive.png" alt="TikTok Live Template" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
            @elseif($page->template === 'template6')
                <img src="/images/reel.png" alt="Reel Template" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
            @else
                <div class="text-center">
                        <svg class="w-12 h-12 text-indigo-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <p class="text-xs text-indigo-600 font-medium">Custom Template</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Template Details -->
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-600">Template Type</p>
            <p class="text-lg font-medium text-gray-900 mt-1">
                @if($page->template === 'template1')
                    YouTubeX Template
                @elseif($page->template === 'template2')
                    UTAMU+ Template
                @elseif($page->template === 'template3')
                    MAUTAMU Template
                @elseif($page->template === 'template4')
                    WhatsApp Group Template
                @elseif($page->template === 'template5')
                    TikTok Live Template
                @elseif($page->template === 'template6')
                    Reel Template
                @else
                    Custom Template
                @endif
            </p>
            <p class="text-xs text-gray-600 mt-2">
                @if(in_array($page->template, ['custom', 'template5', 'template6'], true))
                    Template with uploaded video
                @else
                    Pre-built template (cannot be changed)
                @endif
            </p>
        </div>
    </div>

    <!-- Video Upload Section (only for custom/tiktok template) -->
    @if(in_array($page->template, ['custom', 'template5', 'template6'], true))
    <div id="videoSection" class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Background Video</h2>

        @if($page->video_path)
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm font-medium text-green-800">✓ Video currently uploaded</p>
            <p class="text-xs text-green-700 mt-1">Upload a new video to replace the current one</p>
        </div>
        @endif

        <input type="hidden" id="videoPathInput" name="video_path" value="{{ $page->video_path }}">

        <!-- Drag & Drop Area -->
        <div
            id="dragDropZone"
            class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-indigo-500 hover:bg-indigo-50 transition cursor-pointer"
        >
            <input type="file" id="videoFile" name="video" accept="video/*" class="hidden">

            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>

            <p class="text-base font-medium text-gray-900 mb-1">Drag and drop your video here</p>
            <p class="text-sm text-gray-600 mb-4">or click to browse</p>
            <p class="text-xs text-gray-500">MP4, WebM, OGG (Max 500MB)</p>

            <div id="videoDetails" class="mt-4 hidden text-left bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p id="videoFileName" class="text-sm font-medium text-gray-900 truncate"></p>
                        <p id="videoFileSize" class="text-xs text-gray-600 mt-0.5"></p>
                    </div>
                    <button type="button" id="videoRemoveBtn" class="text-xs font-medium text-red-600 hover:text-red-800 flex-shrink-0">Remove</button>
                </div>
                <div id="videoMetaInfo" class="mt-2 hidden text-xs text-gray-600"></div>
                <div id="videoInspectStatus" class="mt-2 hidden text-xs font-medium"></div>
            </div>

            <!-- Upload Progress -->
            <div id="uploadProgress" class="mt-6 hidden">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-gray-900">
                        <span id="uploadStage">Checking video...</span>
                    </p>
                    <p class="text-sm font-bold text-indigo-600" id="uploadPercent">0%</p>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div id="uploadProgressBar" class="bg-indigo-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <p class="text-xs text-gray-600" id="uploadStats">Preparing upload...</p>
                    <button type="button" id="uploadCancelBtn" class="text-xs font-medium text-red-600 hover:text-red-800">Cancel</button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Keep this page open while the video uploads</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Account Name Section (only for TikTok Live template) -->
    @if($page->template === 'template5')
    <div id="accountNameSection" class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Live Account Name</h2>

        <label for="account_name" class="block text-sm font-medium text-gray-900 mb-2">Account Name</label>
        <input
            type="text"
            id="account_name"
            name="account_name"
            placeholder="e.g. @juma_live"
            value="{{ old('account_name', $page->account_name) }}"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
        >
        <p class="text-xs text-gray-600 mt-1">Shown at the top-right of the TikTok LIVE screen</p>
    </div>
    @endif

    <!-- Payment Settings Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <h2 class="text-lg font-bold text-gray-900 mb-6">Payment Settings</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Price Input -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-900 mb-2">Price (TZS)</label>
                <div class="relative">
                    <span class="absolute left-4 top-3 text-gray-600">TZS</span>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        value="{{ old('price', $page->price ?? '') }}"
                        class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    >
                </div>
                <p class="text-xs text-gray-600 mt-1">Set the price in Tanzanian Shilling (TZS) for accessing this page</p>
            </div>

            <!-- Payment Gateway Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-900 mb-2">Payment Gateway</label>
                <select
                    name="payment_gateway"
                    id="payment_gateway"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                >
                    <option value="">None</option>
                    <option value="sonicpesa" {{ old('payment_gateway', $page->payment_gateway) === 'sonicpesa' ? 'selected' : '' }}>SonicPesa</option>
                    <option value="snippe" {{ old('payment_gateway', $page->payment_gateway) === 'snippe' ? 'selected' : '' }}>Snippe</option>
                    <option value="fastlipa" {{ old('payment_gateway', $page->payment_gateway) === 'fastlipa' ? 'selected' : '' }}>FastLipa</option>
                    <option value="mobilipa" {{ old('payment_gateway', $page->payment_gateway) === 'mobilipa' ? 'selected' : '' }}>Mobilipa</option>
                    <option value="pesalink" {{ old('payment_gateway', $page->payment_gateway) === 'pesalink' ? 'selected' : '' }}>PesaLink</option>
                </select>

                <!-- PesaLink Account Selection (shown only when PesaLink is selected) -->
                <div id="pesalinkAccountWrapper" class="mt-4 {{ $page->payment_gateway === 'pesalink' ? '' : 'hidden' }}">
                    <label for="pesalink_account_id" class="block text-sm font-medium text-gray-900 mb-2">PesaLink Sub-Account</label>
                    <select name="pesalink_account_id" id="pesalink_account_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                        <option value="">Select a PesaLink account...</option>
                        @foreach(\App\Models\PesaLinkAccount::where('is_active', true)->get() as $account)
                            <option value="{{ $account->id }}" {{ old('pesalink_account_id', $page->pesalink_account_id) == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Payment Delay -->
            <div>
                <label for="delay" class="block text-sm font-medium text-gray-900 mb-2">Payment Delay (seconds)</label>
                <input
                    type="number"
                    id="delay"
                    name="payment_delay"
                    placeholder="0"
                    min="0"
                    value="{{ old('payment_delay', $page->payment_delay ?? '') }}"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                >
                <p class="text-xs text-gray-600 mt-1">Video plays for N seconds before the payment popup appears</p>
            </div>
        </div>
    </div>

    <!-- Status Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Page Status</h2>
                <p class="text-sm text-gray-600 mt-1">Activate or deactivate this page</p>
            </div>

            <!-- Toggle Switch -->
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="active" name="is_active" class="sr-only peer" {{ $page->is_active ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4 pt-6">
        <button
            type="submit"
            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            Save Changes
        </button>
        <a
            href="{{ route('pages.index') }}"
            class="flex-1 border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-3 px-6 rounded-lg transition duration-200 text-center"
        >
            Cancel
        </a>
    </div>
</form>

<script>
    const dragDropZone = document.getElementById('dragDropZone');
    const videoFile = document.getElementById('videoFile');

    @if(in_array($page->template, ['custom', 'template5', 'template6'], true))
    const videoDetails = document.getElementById('videoDetails');
    const videoFileName = document.getElementById('videoFileName');
    const videoFileSize = document.getElementById('videoFileSize');
    const videoMetaInfo = document.getElementById('videoMetaInfo');
    const videoInspectStatus = document.getElementById('videoInspectStatus');
    const videoRemoveBtn = document.getElementById('videoRemoveBtn');
    const videoPathInput = document.getElementById('videoPathInput');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadStage = document.getElementById('uploadStage');
    const uploadPercent = document.getElementById('uploadPercent');
    const uploadProgressBar = document.getElementById('uploadProgressBar');
    const uploadStats = document.getElementById('uploadStats');
    const uploadCancelBtn = document.getElementById('uploadCancelBtn');
    const pageForm = document.querySelector('form[action*="/pages/"]');
    const uploadVideoUrl = @json(route('pages.upload-video'));

    const MAX_VIDEO_SIZE = 500 * 1024 * 1024;
    const ALLOWED_EXTENSIONS = ['mp4', 'webm', 'ogv'];
    let activeUpload = null;
    let uploadInProgress = false;

    // Video drag & drop
    dragDropZone.addEventListener('click', () => videoFile.click());

    dragDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dragDropZone.classList.add('border-indigo-500', 'bg-indigo-50');
    });

    dragDropZone.addEventListener('dragleave', () => {
        dragDropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
    });

    dragDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dragDropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
        if (e.dataTransfer.files.length > 0) {
            videoFile.files = e.dataTransfer.files;
            handleVideoSelected();
        }
    });

    videoFile.addEventListener('change', handleVideoSelected);

    videoRemoveBtn.addEventListener('click', () => {
        if (activeUpload) {
            activeUpload.abort();
        }
        activeUpload = null;
        videoFile.value = '';
        videoPathInput.value = '{{ $page->video_path ?? '' }}';
        hideUploadUI();
        resetVideoUI();
    });

    function resetVideoUI() {
        videoDetails.classList.add('hidden');
        videoMetaInfo.classList.add('hidden');
        videoInspectStatus.classList.add('hidden');
        videoInspectStatus.className = 'mt-2 hidden text-xs font-medium';
        uploadInProgress = false;
    }

    function formatBytes(bytes) {
        if (bytes >= 1024 * 1024 * 1024) {
            return (bytes / (1024 * 1024 * 1024)).toFixed(2) + ' GB';
        }
        if (bytes >= 1024 * 1024) {
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }
        return (bytes / 1024).toFixed(0) + ' KB';
    }

    function formatDuration(seconds) {
        if (!isFinite(seconds)) {
            return 'unknown';
        }
        const mins = Math.floor(seconds / 60);
        const secs = Math.round(seconds % 60);
        return mins + ':' + String(secs).padStart(2, '0');
    }

    function setInspectStatus(text, type) {
        videoInspectStatus.classList.remove('hidden');
        if (type === 'error') {
            videoInspectStatus.className = 'mt-2 text-xs font-medium text-red-600';
        } else if (type === 'ok') {
            videoInspectStatus.className = 'mt-2 text-xs font-medium text-green-600';
        } else {
            videoInspectStatus.className = 'mt-2 text-xs font-medium text-gray-600';
        }
        videoInspectStatus.textContent = text;
    }

    /**
     * Verify the browser can actually decode and play the selected file by
     * loading its metadata, so we know it will play before uploading.
     */
    function verifyVideoIsPlayable(file) {
        return new Promise((resolve) => {
            const objectUrl = URL.createObjectURL(file);
            const probe = document.createElement('video');
            probe.preload = 'metadata';
            probe.muted = true;

            const cleanup = () => URL.revokeObjectURL(objectUrl);
            const timeout = setTimeout(() => {
                cleanup();
                resolve({ ok: false, error: 'Could not read the video file. It may be corrupted.' });
            }, 20000);

            probe.addEventListener('loadedmetadata', () => {
                clearTimeout(timeout);
                const meta = {
                    ok: probe.videoWidth > 0 && probe.duration > 0,
                    duration: probe.duration,
                    width: probe.videoWidth,
                    height: probe.videoHeight,
                };
                cleanup();
                resolve(meta.ok ? meta : { ...meta, error: 'The video has no playable track.' });
            });

            probe.addEventListener('error', () => {
                clearTimeout(timeout);
                cleanup();
                resolve({ ok: false, error: 'This file cannot be played by the browser. Please upload a valid MP4, WebM or OGV video.' });
            });

            probe.src = objectUrl;
        });
    }

    async function handleVideoSelected() {
        resetVideoUI();

        if (videoFile.files.length === 0) {
            return;
        }

        const file = videoFile.files[0];
        const extension = file.name.split('.').pop().toLowerCase();

        videoDetails.classList.remove('hidden');
        videoFileName.textContent = file.name;
        videoFileSize.textContent = formatBytes(file.size);

        if (!ALLOWED_EXTENSIONS.includes(extension)) {
            setInspectStatus('✗ Unsupported format ".' + extension + '". Allowed: MP4, WebM, OGV.', 'error');
            return;
        }

        if (file.size === 0) {
            setInspectStatus('✗ The selected file is empty.', 'error');
            return;
        }

        if (file.size > MAX_VIDEO_SIZE) {
            setInspectStatus('✗ File is ' + formatBytes(file.size) + '. Maximum allowed size is 500 MB.', 'error');
            return;
        }

        setInspectStatus('Checking video...', 'checking');

        const probe = await verifyVideoIsPlayable(file);

        if (!probe.ok) {
            setInspectStatus('✗ ' + probe.error, 'error');
            return;
        }

        videoMetaInfo.classList.remove('hidden');
        videoMetaInfo.textContent = 'Duration ' + formatDuration(probe.duration) + ' • ' + probe.width + '×' + probe.height;
        setInspectStatus('✓ Video verified - uploading now...', 'ok');

        startVideoUpload(file);
    }

    /**
     * Upload the selected video immediately so the upload progress is shown
     * right after the file is chosen. On success the returned path is stored
     * in the hidden video_path field referenced by the page form.
     */
    function startVideoUpload(file) {
        const csrfToken = pageForm.querySelector('input[name="_token"]').value;
        const formData = new FormData();
        formData.append('video', file);

        uploadInProgress = true;
        resetUploadBar();
        showUploadUI();

        const xhr = new XMLHttpRequest();
        activeUpload = xhr;
        const startTime = Date.now();

        xhr.open('POST', uploadVideoUrl);
        xhr.responseType = 'json';
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

        xhr.upload.addEventListener('progress', (event) => updateUploadProgress(event, startTime));
        xhr.addEventListener('load', () => {
            uploadInProgress = false;
            activeUpload = null;

            if (xhr.status === 200 && xhr.response?.status === 'success') {
                videoPathInput.value = xhr.response.data.video_path;
                uploadStage.textContent = 'Upload complete';
                uploadPercent.textContent = '100%';
                uploadProgressBar.style.width = '100%';
                uploadStats.textContent = formatBytes(file.size) + ' uploaded - video ready to play';
                setInspectStatus('✓ Video uploaded successfully', 'ok');
            } else if (xhr.status === 422) {
                const message = Object.values(xhr.response?.errors || {}).flat().join(' ')
                    || (xhr.response?.message || 'The video was rejected. Please try another file.');
                showUploadError(message);
                setInspectStatus('✗ Upload failed - select another video or try again', 'error');
            } else {
                showUploadError(xhr.response?.message || 'Upload failed (HTTP ' + xhr.status + '). Please try again.');
                setInspectStatus('✗ Upload failed - select another video or try again', 'error');
            }
        });
        xhr.addEventListener('error', () => {
            uploadInProgress = false;
            activeUpload = null;
            showUploadError('Network error during upload. Please check your connection and try again.');
            setInspectStatus('✗ Upload failed - select another video or try again', 'error');
        });
        xhr.addEventListener('abort', () => {
            uploadInProgress = false;
            activeUpload = null;
            showUploadError('Upload cancelled.');
            setInspectStatus('✗ Upload cancelled - select the video again to retry', 'error');
        });

        xhr.send(formData);
    }

    function showUploadUI() {
        uploadProgress.classList.remove('hidden');
        uploadStage.textContent = 'Uploading video...';
        uploadPercent.textContent = '0%';
        uploadProgressBar.style.width = '0%';
        uploadStats.textContent = 'Preparing upload...';
    }

    function hideUploadUI() {
        uploadProgress.classList.add('hidden');
    }

    function updateUploadProgress(event, startTime) {
        if (!event.lengthComputable) {
            return;
        }

        const percent = Math.round((event.loaded / event.total) * 100);
        const uploaded = formatBytes(event.loaded);
        const total = formatBytes(event.total);
        const elapsedSeconds = Math.max((Date.now() - startTime) / 1000, 0.5);
        const speed = event.loaded / elapsedSeconds;
        const remainingBytes = Math.max(event.total - event.loaded, 0);
        const etaSeconds = speed > 0 ? remainingBytes / speed : 0;
        const etaMinutes = Math.floor(etaSeconds / 60);
        const eta = etaMinutes + ':' + String(Math.round(etaSeconds % 60)).padStart(2, '0');

        uploadPercent.textContent = percent + '%';
        uploadProgressBar.style.width = percent + '%';
        uploadStats.textContent = uploaded + ' of ' + total + ' • ' + formatBytes(speed) + '/s • ETA ' + eta;
    }

    function showUploadError(message) {
        uploadStage.textContent = 'Upload failed';
        uploadPercent.textContent = '';
        uploadProgressBar.classList.remove('bg-indigo-600');
        uploadProgressBar.classList.add('bg-red-600');
        uploadStats.textContent = message;
    }

    function resetUploadBar() {
        uploadProgressBar.classList.remove('bg-red-600');
        uploadProgressBar.classList.add('bg-indigo-600');
    }

    uploadCancelBtn.addEventListener('click', () => {
        if (activeUpload) {
            activeUpload.abort();
        }
    });

    pageForm.addEventListener('submit', (event) => {
        if (uploadInProgress) {
            event.preventDefault();
            setInspectStatus('Please wait for the video upload to finish.', 'error');
            uploadProgress.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
    @endif

    // Toggle PesaLink account selector based on gateway selection
    const gatewaySelect = document.getElementById('payment_gateway');
    const pesalinkAccountWrapper = document.getElementById('pesalinkAccountWrapper');

    if (gatewaySelect && pesalinkAccountWrapper) {
        gatewaySelect.addEventListener('change', function () {
            if (this.value === 'pesalink') {
                pesalinkAccountWrapper.classList.remove('hidden');
            } else {
                pesalinkAccountWrapper.classList.add('hidden');
            }
        });
    }
</script>
@endsection
