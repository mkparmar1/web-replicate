<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title>{{ $seo['title'] ?? 'WebReplicate - Download Websites for Offline Viewing' }}</title>
    <meta name="description" content="{{ $seo['description'] ?? 'WebReplicate is a powerful tool that allows you to download and archive websites for offline viewing. Copy HTML, CSS, JS, images, PDFs, and more.' }}">
    <meta name="keywords" content="{{ $seo['keywords'] ?? 'website copier, website downloader, offline website, website archiver, website backup, html downloader, web scraper, website crawler, offline browser' }}">
    <meta name="robots" content="{{ $seo['robots'] ?? 'index, follow' }}">
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $seo['title'] ?? 'WebReplicate - Download Websites for Offline Viewing' }}">
    <meta property="og:description" content="{{ $seo['description'] ?? 'WebReplicate is a powerful tool that allows you to download and archive websites for offline viewing. Copy HTML, CSS, JS, images, PDFs, and more.' }}">
    <meta property="og:image" content="{{ $seo['og_image'] ?? asset('images/webreplicate-social.jpg') }}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $seo['title'] ?? 'WebReplicate - Download Websites for Offline Viewing' }}">
    <meta property="twitter:description" content="{{ $seo['description'] ?? 'WebReplicate is a powerful tool that allows you to download and archive websites for offline viewing. Copy HTML, CSS, JS, images, PDFs, and more.' }}">
    <meta property="twitter:image" content="{{ $seo['og_image'] ?? asset('images/webreplicate-social.jpg') }}">
    
    <!-- Structured Data / JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "WebReplicate",
        "applicationCategory": "WebApplication",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "description": "WebReplicate is a powerful tool that allows you to download and archive websites for offline viewing. Copy HTML, CSS, JS, images, PDFs, and more."
    }
    </script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    <!-- Stylesheets and Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Smokey background effect */
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            position: relative;
            overflow-x: hidden;
            min-height: 100vh;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPgogIDxmaWx0ZXIgaWQ9Im5vaXNlIj4KICAgIDxmZVR1cmJ1bGVuY2UgdHlwZT0iZnJhY3RhbE5vaXNlIiBiYXNlRnJlcXVlbmN5PSIwLjA1IiBudW1PY3RhdmVzPSIyIiBzdGl0Y2hUaWxlcz0ic3RpdGNoIi8+CiAgICA8ZmVCbGVuZCBtb2RlPSJzb2Z0LWxpZ2h0IiBpbj0iU291cmNlR3JhcGhpYyIvPgogIDwvZmlsdGVyPgogIDxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbHRlcj0idXJsKCNub2lzZSkiIG9wYWNpdHk9IjAuMiIvPgo8L3N2Zz4=');
            opacity: 0.4;
            z-index: -1;
            animation: smokeDrift 20s linear infinite;
        }
        
        @keyframes smokeDrift {
            0% {
                background-position: 0% 0%;
            }
            100% {
                background-position: 100% 100%;
            }
        }
        
        /* Floating particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
            }
            25% {
                transform: translateY(-30px) translateX(30px);
            }
            50% {
                transform: translateY(-60px) translateX(-30px);
            }
            75% {
                transform: translateY(-30px) translateX(30px);
            }
        }
        
        /* 3D card effect */
        .card-3d {
            transform-style: preserve-3d;
            transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.6s cubic-bezier(0.23, 1, 0.32, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card-3d:hover {
            transform: perspective(1000px) rotateX(5deg) rotateY(5deg) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }
        
        /* Glowing effect for buttons */
        .btn-glow {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .btn-glow::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0) 100%);
            transform: rotate(45deg);
            animation: glowSweep 3s infinite;
        }
        
        @keyframes glowSweep {
            0% {
                transform: rotate(45deg) translateX(-100%);
            }
            100% {
                transform: rotate(45deg) translateX(100%);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        .slide-up {
            animation: slideUp 0.5s ease-out;
        }
        
        /* Progress bar animation */
        @keyframes progressPulse {
            0%, 100% {
                box-shadow: 0 0 0 rgba(59, 130, 246, 0.5);
            }
            50% {
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.8);
            }
        }
        
        .progress-bar-animate {
            animation: progressPulse 2s infinite;
        }
        
        /* Add responsive fixes */
        @media (max-width: 768px) {
            .card-3d {
                padding: 1rem !important;
            }
            
            .grid-cols-2 {
                grid-template-columns: 1fr 1fr;
            }
            
            .text-4xl {
                font-size: 1.75rem;
            }
            
            .p-4 {
                padding: 0.75rem;
            }
        }
        
        @media (max-width: 480px) {
            .grid-cols-2 {
                grid-template-columns: 1fr;
            }
            
            .space-x-4 {
                margin-top: 0.5rem;
            }
            
            .text-4xl {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center py-6 px-4">
    <!-- Floating particles -->
    <div class="particles">
        <div class="particle" style="width: 20px; height: 20px; top: 10%; left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="width: 15px; height: 15px; top: 20%; left: 80%; animation-delay: 1s;"></div>
        <div class="particle" style="width: 25px; height: 25px; top: 80%; left: 15%; animation-delay: 2s;"></div>
        <div class="particle" style="width: 10px; height: 10px; top: 40%; left: 40%; animation-delay: 3s;"></div>
        <div class="particle" style="width: 18px; height: 18px; top: 70%; left: 70%; animation-delay: 4s;"></div>
        <div class="particle" style="width: 12px; height: 12px; top: 30%; left: 60%; animation-delay: 5s;"></div>
        <div class="particle" style="width: 22px; height: 22px; top: 60%; left: 30%; animation-delay: 6s;"></div>
    </div>

    <div class="w-full max-w-4xl mx-auto p-4 sm:p-8 rounded-lg card-3d slide-up">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-4 sm:mb-6 text-center">Website Copier</h1>
        <p class="text-gray-600 mb-6 sm:mb-8 text-center text-base sm:text-lg">Enter a URL, select file types, and choose how many pages to copy</p>

        <form id="website-form" method="POST" action="{{ route('copy') }}" class="space-y-6 sm:space-y-8">
            @csrf
            <div class="space-y-4">
                <input type="url" name="url" placeholder="https://example.com" required
                    class="w-full p-3 sm:p-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-base sm:text-lg">

                <div class="flex flex-wrap items-center gap-2 sm:gap-4">
                    <label class="text-gray-700 font-medium">Max Pages:</label>
                    <input type="number" name="max_pages" min="1" max="1000" value="10"
                        class="w-24 sm:w-32 p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 sm:gap-4">
                    <div class="flex items-center space-x-2 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                        <input type="checkbox" name="file_types[]" value="html" id="html" checked
                            class="w-5 h-5 text-blue-600">
                        <label for="html" class="flex items-center">
                            <i class="fab fa-html5 text-orange-500 mr-2"></i> HTML
                        </label>
                    </div>
                    
                    <div class="flex items-center space-x-2 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                        <input type="checkbox" name="file_types[]" value="css" id="css" checked
                            class="w-5 h-5 text-blue-600">
                        <label for="css" class="flex items-center">
                            <i class="fab fa-css3-alt text-blue-500 mr-2"></i> CSS
                        </label>
                    </div>
                    
                    <div class="flex items-center space-x-2 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                        <input type="checkbox" name="file_types[]" value="js" id="js" checked
                            class="w-5 h-5 text-blue-600">
                        <label for="js" class="flex items-center">
                            <i class="fab fa-js text-yellow-500 mr-2"></i> JS
                        </label>
                    </div>
                    
                    <div class="flex items-center space-x-2 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                        <input type="checkbox" name="file_types[]" value="images" id="images" checked
                            class="w-5 h-5 text-blue-600">
                        <label for="images" class="flex items-center">
                            <i class="far fa-image text-green-500 mr-2"></i> Images
                        </label>
                    </div>
                    
                    <div class="flex items-center space-x-2 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                        <input type="checkbox" name="file_types[]" value="gif" id="gif"
                            class="w-5 h-5 text-blue-600">
                        <label for="gif" class="flex items-center">
                            <i class="far fa-file-image text-purple-500 mr-2"></i> GIFs
                        </label>
                    </div>
                    
                    <!-- New file type options -->
                    <div class="flex items-center space-x-2 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                        <input type="checkbox" name="file_types[]" value="pdf" id="pdf"
                            class="w-5 h-5 text-blue-600">
                        <label for="pdf" class="flex items-center">
                            <i class="far fa-file-pdf text-red-500 mr-2"></i> PDFs
                        </label>
                    </div>
                    
                    <div class="flex items-center space-x-2 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                        <input type="checkbox" name="file_types[]" value="doc" id="doc"
                            class="w-5 h-5 text-blue-600">
                        <label for="doc" class="flex items-center">
                            <i class="far fa-file-word text-blue-600 mr-2"></i> DOCs
                        </label>
                    </div>
                    
                    <div class="flex items-center space-x-2 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                        <input type="checkbox" name="file_types[]" value="zip" id="zip"
                            class="w-5 h-5 text-blue-600">
                        <label for="zip" class="flex items-center">
                            <i class="far fa-file-archive text-yellow-600 mr-2"></i> ZIPs
                        </label>
                    </div>
                    
                    <div class="flex items-center space-x-2 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                        <input type="checkbox" name="file_types[]" value="video" id="video"
                            class="w-5 h-5 text-blue-600">
                        <label for="video" class="flex items-center">
                            <i class="far fa-file-video text-red-600 mr-2"></i> Videos
                        </label>
                    </div>
                    
                    <div class="flex items-center space-x-2 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                        <input type="checkbox" name="file_types[]" value="audio" id="audio"
                            class="w-5 h-5 text-blue-600">
                        <label for="audio" class="flex items-center">
                            <i class="far fa-file-audio text-green-600 mr-2"></i> Audio
                        </label>
                    </div>
                </div>
            </div>

            <button id="submit-button" type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 sm:py-4 px-4 rounded-lg transition duration-300 btn-glow">
                Copy Website
            </button>

            <div id="processing-container" style="display: none;" class="mt-6 bg-white p-4 rounded-lg shadow-md">
                <h3 id="progress-text" class="text-xl font-semibold text-center mb-4">Processing...</h3>
                
                <div class="relative pt-1">
                    <div class="overflow-hidden h-4 mb-4 text-xs flex rounded-full bg-blue-200">
                        <div id="progress-bar" class="progress-bar-animate shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
                
                <p id="progress-count" class="text-gray-700 text-base sm:text-lg">Pages copied: 0 / 0</p>
                <div id="asset-count" class="text-gray-700 text-base sm:text-lg mt-2" style="display: none;">Assets: 0</div>
                
                <div id="processing-indicator" class="text-center mt-4 sm:mt-6">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
                </div>
                
                <div id="download-container" style="display: none; margin-top: 15px;" class="text-center">
                    <!-- Download link will be inserted here -->
                </div>
                
                <div id="error-container" style="display: none; margin-top: 15px;" class="text-red-500 text-center">
                    <!-- Error message will be inserted here -->
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('website-form');
            const submitButton = document.getElementById('submit-button');
            const processingContainer = document.getElementById('processing-container');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const progressCount = document.getElementById('progress-count');
            const assetCount = document.getElementById('asset-count');
            const processingIndicator = document.getElementById('processing-indicator');
            const downloadContainer = document.getElementById('download-container');
            const errorContainer = document.getElementById('error-container');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Disable submit button
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-50');
                    
                    // Show processing container
                    processingContainer.style.display = 'block';
                    progressText.textContent = 'Processing...';
                    progressBar.style.width = '0%';
                    progressCount.textContent = 'Pages copied: 0 / 0';
                    assetCount.style.display = 'none';
                    processingIndicator.style.display = 'block';
                    downloadContainer.style.display = 'none';
                    errorContainer.style.display = 'none';
                    
                    // Scroll to processing container
                    processingContainer.scrollIntoView({ behavior: 'smooth' });
                    
                    // Submit the form via AJAX
                    const formData = new FormData(this);
                    
                    fetch('/copy', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Copy response:', data);
                        
                        if (!data.success) {
                            throw new Error(data.error || 'Copy request failed');
                        }
                        
                        // If ZIP is already created, show download link immediately
                        if (data.zip_created) {
                            showDownloadLink(data.filename);
                        } else {
                            // Start polling for status
                            pollStatus(data.filename);
                        }
                    })
                    .catch(error => {
                        console.error('Copy error:', error);
                        showError(error.message);
                    });
                });
            }
            
            function pollStatus(filename) {
                console.log('Polling status for:', filename);
                
                fetch(`/status/${filename}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Status update:', data);
                    
                    if (!data.success) {
                        throw new Error(data.error || 'Status check failed');
                    }
                    
                    // Update progress display for pages
                    progressCount.textContent = `Pages copied: ${data.pages_copied} / ${data.total_pages}`;
                    
                    // Add asset information if available
                    if (data.assets_copied) {
                        let assetText = `Assets: ${data.assets_copied}`;
                        if (data.assets_by_type) {
                            let typeCounts = [];
                            for (const [type, count] of Object.entries(data.assets_by_type)) {
                                if (count > 0) {
                                    typeCounts.push(`${type}: ${count}`);
                                }
                            }
                            if (typeCounts.length > 0) {
                                assetText += ` (${typeCounts.join(', ')})`;
                            }
                        }
                        
                        assetCount.textContent = assetText;
                        assetCount.style.display = 'block';
                    }
                    
                    // Calculate percentage
                    const percent = data.total_pages > 0 
                        ? Math.min(Math.round((data.pages_copied / data.total_pages) * 100), 100)
                        : 0;
                    progressBar.style.width = `${percent}%`;
                    
                    // Check if completed
                    if (data.completed) {
                        showDownloadLink(filename);
                    } else {
                        // Continue polling
                        setTimeout(() => pollStatus(filename), 2000);
                    }
                })
                .catch(error => {
                    console.error('Status error:', error);
                    showError(error.message);
                });
            }
            
            function showDownloadLink(filename) {
                // Hide processing indicator
                processingIndicator.style.display = 'none';
                progressText.textContent = 'Download ready!';
                progressBar.style.width = '100%';
                progressBar.classList.remove('progress-bar-animate');
                progressBar.classList.add('bg-green-500');
                
                // Show download button
                const downloadUrl = `/download/${filename}`;
                downloadContainer.style.display = 'block';
                downloadContainer.innerHTML = `
                    <a href="${downloadUrl}" class="inline-block bg-green-600 text-white p-3 sm:p-4 rounded-lg hover:bg-green-700 transition duration-300 text-base sm:text-lg btn-glow">
                        <i class="fas fa-download mr-2"></i> Download ZIP
                    </a>
                `;
                
                // Scroll to download button
                downloadContainer.scrollIntoView({ behavior: 'smooth' });
                
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50');
            }
            
            function showError(message) {
                // Hide processing indicator
                processingIndicator.style.display = 'none';
                
                // Show error message
                progressText.textContent = 'Error';
                progressBar.style.width = '100%';
                progressBar.classList.remove('progress-bar-animate');
                progressBar.classList.add('bg-red-500');
                
                errorContainer.style.display = 'block';
                errorContainer.textContent = `Error: ${message}`;
                
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50');
                
                console.error('Error:', message);
            }
            
            // Add 3D tilt effect to the card
            const card = document.querySelector('.card-3d');
            if (card) {
                card.addEventListener('mousemove', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    
                    const angleX = (y - centerY) / 30;
                    const angleY = (centerX - x) / 30;
                    
                    this.style.transform = `perspective(1000px) rotateX(${angleX}deg) rotateY(${angleY}deg)`;
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
                });
            }
        });
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</body>

</html>
