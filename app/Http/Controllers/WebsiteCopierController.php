<?php

namespace App\Http\Controllers;

use App\Jobs\CopyWebsite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;  // Use Laravel's logging feature to log errors
use Exception;

class WebsiteCopierController extends Controller
{
    protected $seoData;

    public function __construct()
    {
        // Default SEO data
        $this->seoData = [
            'title' => 'WebReplicate - Download Websites for Offline Viewing',
            'description' => 'WebReplicate is a powerful tool that allows you to download and archive websites for offline viewing. Copy HTML, CSS, JS, images, PDFs, and more.',
            'keywords' => 'website copier, website downloader, offline website, website archiver, website backup, html downloader, web scraper, website crawler, offline browser',
            'og_image' => asset('images/webreplicate-social.jpg'),
            'canonical' => url()->current(),
            'robots' => 'index, follow',
        ];
    }

    public function index()
    {
        return view('welcome', ['seo' => $this->seoData]);
    }

    public function copy(Request $request)
    {
        try {
            // Increase execution time limit for large websites
            set_time_limit(300); // 5 minutes
            
            $request->validate([
                'url' => 'required|url|max:255',
                'file_types' => 'required|array|min:1',
                'file_types.*' => 'in:html,css,js,images,gif,pdf,doc,zip,video,audio',
                'max_pages' => 'required|integer|min:1|max:1000', // Dynamic page limit
            ]);

            $filename = md5($request->url . implode('', $request->file_types) . $request->max_pages);
            
            // Make sure the downloads directory exists
            Storage::makeDirectory('public/downloads');

            // Initialize session status with more detailed tracking
            session()->put("status_{$filename}", [
                'pages_copied' => 0, 
                'total_pages' => $request->max_pages,
                'started_at' => now(),
                'error' => null
            ]);

            // Create and handle the job synchronously
            $copyWebsiteJob = new CopyWebsite($request->url, $request->file_types, $filename, $request->max_pages);
            $copyWebsiteJob->handle();

            // Verify if the zip file was created
            $zipExists = Storage::exists("public/downloads/{$filename}.zip");
            Log::info("Copy process completed. ZIP file exists: " . ($zipExists ? 'Yes' : 'No'));

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'message' => "Copying started for " . implode(', ', $request->file_types) . " (up to {$request->max_pages} pages)!",
                'zip_created' => $zipExists
            ]);
        } catch (Exception $e) {
            // Log the full error for server-side debugging
            Log::error("Website copy error: " . $e->getMessage() . "\n" . $e->getTraceAsString());

            // Return a JSON error response
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while copying the website.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function status($filename)
    {
        try {
            // Retrieve the status from the session with default values
            $status = session("status_{$filename}", [
                'pages_copied' => 0, 
                'total_pages' => 0, 
                'assets_copied' => 0,
                'assets_by_type' => [],
                'error' => null
            ]);

            // Check if the file exists in storage
            $fileExists = Storage::exists("public/downloads/{$filename}.zip");
            
            // Log the status check
            Log::info("Status check for {$filename}: Pages: {$status['pages_copied']}/{$status['total_pages']}, Assets: {$status['assets_copied']}, ZIP exists: " . ($fileExists ? 'Yes' : 'No'));

            // If there's an error, return it
            if (isset($status['error']) && $status['error']) {
                return response()->json([
                    'success' => false,
                    'error' => $status['error'],
                    'pages_copied' => $status['pages_copied'] ?? 0,
                    'total_pages' => $status['total_pages'] ?? 0,
                    'assets_copied' => $status['assets_copied'] ?? 0,
                    'assets_by_type' => $status['assets_by_type'] ?? [],
                    'completed' => false,
                ], 500);
            }

            // If the ZIP file exists, mark as completed regardless of page count
            if ($fileExists) {
                return response()->json([
                    'success' => true,
                    'pages_copied' => $status['pages_copied'] ?? 0,
                    'total_pages' => $status['total_pages'] ?? 0,
                    'assets_copied' => $status['assets_copied'] ?? 0,
                    'assets_by_type' => $status['assets_by_type'] ?? [],
                    'completed' => true,
                    'download_url' => route('download', $filename),
                ]);
            }

            // Return the status as JSON
            return response()->json([
                'success' => true,
                'pages_copied' => $status['pages_copied'] ?? 0,
                'total_pages' => $status['total_pages'] ?? 0,
                'assets_copied' => $status['assets_copied'] ?? 0,
                'assets_by_type' => $status['assets_by_type'] ?? [],
                'completed' => false,
                'download_url' => null,
            ]);
        } catch (Exception $e) {
            Log::error("Status retrieval error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while retrieving the status.',
                'pages_copied' => 0,
                'total_pages' => 0,
                'completed' => false,
            ], 500);
        }
    }

    public function download($filename)
    {
        try {
            $path = storage_path("app/public/downloads/{$filename}.zip");
            
            Log::info("Download requested for {$filename}. File exists: " . (file_exists($path) ? 'Yes' : 'No'));

            // Check if the file exists
            if (file_exists($path)) {
                session()->forget("status_{$filename}");
                
                // Return the file for download and delete it after sending
                return response()->download($path, 'website_copy.zip')->deleteFileAfterSend(true);
            }

            // If file doesn't exist, return a JSON error
            return response()->json([
                'success' => false,
                'error' => 'File not found or expired.'
            ], 404);
        } catch (Exception $e) {
            Log::error("Error while downloading the file {$filename}: " . $e->getMessage());
            
            // Return a JSON error
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while downloading the file.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function sitemap()
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>' . url('/') . '</loc>
        <lastmod>' . now()->toW3cString() . '</lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
</urlset>';

        return response($content)->header('Content-Type', 'text/xml');
    }
}
