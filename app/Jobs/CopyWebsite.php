<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;
use ZipArchive;
use Illuminate\Support\Facades\Log;
use DOMDocument;

class CopyWebsite
{
    protected $url;
    protected $fileTypes;
    protected $filename;
    protected $maxPages;
    protected $visited = [];
    protected $baseUrl;
    protected $baseDomain;
    protected $baseDir;
    protected $assetMap = [];
    protected $pageCount = 0;
    protected $assetCount = 0;
    protected $maxAssetsPerType = 1000;
    protected $assetCountByType = [];
    protected $queuedAssets = [];
    protected $siteName;

    public function __construct($url, $fileTypes, $filename, $maxPages)
    {
        $this->url = $url;
        $this->fileTypes = $fileTypes;
        $this->filename = $filename;
        $this->maxPages = $maxPages;
        
        // Extract base URL and domain
        $parsedUrl = parse_url($url);
        $this->baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        $this->baseDomain = $parsedUrl['host'];
        
        // Create a site name from the domain
        $this->siteName = str_replace('.', '_', $this->baseDomain);
        
        // Initialize asset counts
        foreach ($fileTypes as $type) {
            $this->assetCountByType[$type] = 0;
        }
    }

    public function handle()
    {
        try {
            // Increase execution time limit for large websites
            set_time_limit(300); // 5 minutes
            
            $client = new Client([
                'timeout' => 10,
                'connect_timeout' => 5,
                'verify' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ]
            ]);
            
            $this->baseDir = 'websites/' . $this->filename;
            
            // Create base directory with site name
            Storage::makeDirectory($this->baseDir . '/' . $this->siteName);
            
            // Create directory for each file type
            foreach ($this->fileTypes as $type) {
                Storage::makeDirectory($this->baseDir . '/' . $this->siteName . '/assets/' . $type);
            }

            $this->visited = [];
            $this->pageCount = 0;
            $this->assetCount = 0;

            // Initialize session status
            $this->updateStatus(0);

            // Start the crawling process
            $this->crawl($this->url, $client, 0);
            
            // Force download of assets from the initial page
            $this->forceDownloadAssets($this->url, $client);
            
            $this->createZip(); // Create the ZIP after successful crawl

            // Update final status
            $this->updateStatus($this->pageCount, true);

        } catch (\Exception $e) {
            // Log the error and update session status
            $errorMessage = "Website copy failed: " . $e->getMessage();
            Log::error($errorMessage);

            $status = session("status_{$this->filename}", []);
            $status['error'] = $errorMessage;
            session()->put("status_{$this->filename}", $status);

            // Rethrow the exception to be caught by the controller
            throw $e;
        }
    }

    protected function forceDownloadAssets($url, $client)
    {
        try {
            Log::info("Force downloading assets from {$url}");
            
            $response = $client->get($url);
            $html = $response->getBody()->getContents();
            
            // Process CSS
            if (in_array('css', $this->fileTypes)) {
                preg_match_all('/<link[^>]*rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $cssMatches);
                if (!empty($cssMatches[1])) {
                    foreach ($cssMatches[1] as $cssUrl) {
                        $absUrl = $this->resolveUrl($cssUrl, $url);
                        Log::info("Force downloading CSS: {$absUrl}");
                        $this->forceDownloadFile($client, $absUrl, 'css');
                    }
                }
            }
            
            // Process JS
            if (in_array('js', $this->fileTypes)) {
                preg_match_all('/<script[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $jsMatches);
                if (!empty($jsMatches[1])) {
                    foreach ($jsMatches[1] as $jsUrl) {
                        $absUrl = $this->resolveUrl($jsUrl, $url);
                        Log::info("Force downloading JS: {$absUrl}");
                        $this->forceDownloadFile($client, $absUrl, 'js');
                    }
                }
            }
            
            // Process images
            if (in_array('images', $this->fileTypes)) {
                preg_match_all('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $imgMatches);
                if (!empty($imgMatches[1])) {
                    foreach ($imgMatches[1] as $imgUrl) {
                        $absUrl = $this->resolveUrl($imgUrl, $url);
                        $ext = strtolower(pathinfo($absUrl, PATHINFO_EXTENSION));
                        
                        if ($ext === 'gif' && in_array('gif', $this->fileTypes)) {
                            Log::info("Force downloading GIF: {$absUrl}");
                            $this->forceDownloadFile($client, $absUrl, 'gif');
                        } else {
                            Log::info("Force downloading image: {$absUrl}");
                            $this->forceDownloadFile($client, $absUrl, 'images');
                        }
                    }
                }
            }
            
            // Process PDF files
            if (in_array('pdf', $this->fileTypes)) {
                preg_match_all('/<a[^>]*href=["\']([^"\']+\.pdf)["\'][^>]*>/i', $html, $pdfMatches);
                if (!empty($pdfMatches[1])) {
                    foreach ($pdfMatches[1] as $pdfUrl) {
                        $absUrl = $this->resolveUrl($pdfUrl, $url);
                        Log::info("Force downloading PDF: {$absUrl}");
                        $this->forceDownloadFile($client, $absUrl, 'pdf');
                    }
                }
            }
            
            // Process DOC files
            if (in_array('doc', $this->fileTypes)) {
                preg_match_all('/<a[^>]*href=["\']([^"\']+\.(doc|docx))["\'][^>]*>/i', $html, $docMatches);
                if (!empty($docMatches[1])) {
                    foreach ($docMatches[1] as $docUrl) {
                        $absUrl = $this->resolveUrl($docUrl, $url);
                        Log::info("Force downloading DOC: {$absUrl}");
                        $this->forceDownloadFile($client, $absUrl, 'doc');
                    }
                }
            }
            
            // Process ZIP files
            if (in_array('zip', $this->fileTypes)) {
                preg_match_all('/<a[^>]*href=["\']([^"\']+\.(zip|rar|7z))["\'][^>]*>/i', $html, $zipMatches);
                if (!empty($zipMatches[1])) {
                    foreach ($zipMatches[1] as $zipUrl) {
                        $absUrl = $this->resolveUrl($zipUrl, $url);
                        Log::info("Force downloading ZIP: {$absUrl}");
                        $this->forceDownloadFile($client, $absUrl, 'zip');
                    }
                }
            }
            
            // Process video files
            if (in_array('video', $this->fileTypes)) {
                preg_match_all('/<(video[^>]*src|source[^>]*src|a[^>]*href)=["\']([^"\']+\.(mp4|webm|avi|mov|wmv))["\'][^>]*>/i', $html, $videoMatches);
                if (!empty($videoMatches[2])) {
                    foreach ($videoMatches[2] as $videoUrl) {
                        $absUrl = $this->resolveUrl($videoUrl, $url);
                        Log::info("Force downloading video: {$absUrl}");
                        $this->forceDownloadFile($client, $absUrl, 'video');
                    }
                }
            }
            
            // Process audio files
            if (in_array('audio', $this->fileTypes)) {
                preg_match_all('/<(audio[^>]*src|source[^>]*src|a[^>]*href)=["\']([^"\']+\.(mp3|wav|ogg|aac))["\'][^>]*>/i', $html, $audioMatches);
                if (!empty($audioMatches[2])) {
                    foreach ($audioMatches[2] as $audioUrl) {
                        $absUrl = $this->resolveUrl($audioUrl, $url);
                        Log::info("Force downloading audio: {$absUrl}");
                        $this->forceDownloadFile($client, $absUrl, 'audio');
                    }
                }
            }
            
            // Update status after forced downloads
            $this->updateStatus($this->pageCount);
            
        } catch (\Exception $e) {
            Log::error("Error during forced asset download: " . $e->getMessage());
        }
    }

    protected function forceDownloadFile($client, $url, $type)
    {
        try {
            // Skip if already downloaded
            if (isset($this->assetMap[$url])) {
                return $this->assetMap[$url];
            }
            
            // Generate a filename for the asset
            $filename = basename(parse_url($url, PHP_URL_PATH));
            if (empty($filename) || $filename === '/') {
                $filename = md5($url) . $this->getExtensionFromType($type);
            }
            
            // Ensure filename is unique
            $path = $this->baseDir . '/' . $this->siteName . '/assets/' . $type . '/' . $filename;
            $i = 1;
            while (Storage::exists($path)) {
                $pathInfo = pathinfo($filename);
                $newFilename = $pathInfo['filename'] . '-' . $i;
                if (isset($pathInfo['extension'])) {
                    $newFilename .= '.' . $pathInfo['extension'];
                } else {
                    $newFilename .= $this->getExtensionFromType($type);
                }
                $path = $this->baseDir . '/' . $this->siteName . '/assets/' . $type . '/' . $newFilename;
                $i++;
            }
            
            // Download the asset
            try {
                $response = $client->get($url, ['timeout' => 5]);
                Storage::put($path, $response->getBody()->getContents());
                
                // Increment asset count
                $this->assetCount++;
                $this->assetCountByType[$type]++;
                
                Log::info("Successfully downloaded {$type} asset: {$url}");
                
                // Store the mapping from URL to local path
                $relativePath = 'assets/' . $type . '/' . basename($path);
                $this->assetMap[$url] = $relativePath;
                
                return $relativePath;
            } catch (\Exception $e) {
                Log::warning("Failed to download {$url}: " . $e->getMessage());
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Error in forceDownloadFile: " . $e->getMessage());
            return null;
        }
    }

    protected function updateStatus($pagesCopied, $completed = false)
    {
        $status = session("status_{$this->filename}", []);
        $status['pages_copied'] = $pagesCopied;
        $status['total_pages'] = $this->maxPages;
        $status['assets_copied'] = $this->assetCount;
        $status['assets_by_type'] = $this->assetCountByType;
        
        if ($completed) {
            $status['completed_at'] = now();
        }
        
        session()->put("status_{$this->filename}", $status);
        
        // Log status update for debugging
        Log::info("Status updated: {$pagesCopied}/{$this->maxPages} pages, assets: {$this->assetCount} (" . 
            implode(', ', array_map(function($k, $v) { return "$k: $v"; }, array_keys($this->assetCountByType), $this->assetCountByType)) . ")");
    }

    protected function crawl($url, $client, $depth = 0)
    {
        // Check if we're approaching the time limit
        $timeLimit = ini_get('max_execution_time');
        $scriptStartTime = $_SERVER['REQUEST_TIME'] ?? time();
        $elapsedTime = time() - $scriptStartTime;
        
        // If we've used 80% of our time limit, stop crawling and create the zip
        if ($timeLimit > 0 && $elapsedTime > ($timeLimit * 0.8)) {
            Log::warning("Approaching time limit, stopping crawl at {$this->pageCount} pages");
            return;
        }

        // Stop if we've reached the maximum number of pages
        if ($this->pageCount >= $this->maxPages) {
            return;
        }

        // Skip if we've already visited this URL
        if (in_array($url, $this->visited)) {
            return;
        }

        // Add to visited list
        $this->visited[] = $url;
        $this->pageCount++;

        try {
            Log::info("Crawling page {$this->pageCount}/{$this->maxPages}: {$url}");
            
            $response = $client->get($url);
            $html = $response->getBody()->getContents();

            if (in_array('html', $this->fileTypes)) {
                // Generate a filename based on the URL path
                $filename = $this->getFilenameFromUrl($url);
                
                // Save the HTML
                Storage::put($this->baseDir . '/' . $this->siteName . '/' . $filename, $html);
                Log::info("Saved HTML file: {$filename}");
                
                // Extract and download assets
                $this->extractAndDownloadAssets($html, $url, $client);
            }

            // Update status after each page
            $this->updateStatus($this->pageCount);

            // Find and crawl linked pages
            if ($this->pageCount < $this->maxPages) {
                $crawler = new Crawler($html);
                $links = [];
                $crawler->filter('a[href]')->each(function (Crawler $node) use (&$links, $url) {
                    $link = $node->attr('href');
                    $absLink = $this->resolveUrl($link, $url);
                    
                    // Only follow links on the same domain
                    if (strpos($absLink, $this->baseDomain) !== false && !in_array($absLink, $this->visited)) {
                        $links[] = $absLink;
                    }
                });
                
                // Sort links to ensure consistent crawling order
                sort($links);
                
                // Crawl each link
                foreach ($links as $link) {
                    if ($this->pageCount < $this->maxPages) {
                        $this->crawl($link, $client, $depth + 1);
                    } else {
                        break;
                    }
                }
            }
        } catch (RequestException $e) {
            Log::error("Failed to fetch URL: {$url}. Error: {$e->getMessage()}");
        } catch (\Exception $e) {
            Log::error("Unexpected error during crawl of {$url}: " . $e->getMessage());
        }
    }

    protected function extractAndDownloadAssets($html, $pageUrl, $client)
    {
        try {
            // Process CSS links
            if (in_array('css', $this->fileTypes)) {
                preg_match_all('/<link[^>]*rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $cssMatches);
                if (!empty($cssMatches[1])) {
                    foreach ($cssMatches[1] as $cssUrl) {
                        $absUrl = $this->resolveUrl($cssUrl, $pageUrl);
                        $this->forceDownloadFile($client, $absUrl, 'css');
                    }
                }
            }
            
            // Process JS scripts
            if (in_array('js', $this->fileTypes)) {
                preg_match_all('/<script[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $jsMatches);
                if (!empty($jsMatches[1])) {
                    foreach ($jsMatches[1] as $jsUrl) {
                        $absUrl = $this->resolveUrl($jsUrl, $pageUrl);
                        $this->forceDownloadFile($client, $absUrl, 'js');
                    }
                }
            }
            
            // Process images
            if (in_array('images', $this->fileTypes) || in_array('gif', $this->fileTypes)) {
                preg_match_all('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $imgMatches);
                if (!empty($imgMatches[1])) {
                    foreach ($imgMatches[1] as $imgUrl) {
                        $absUrl = $this->resolveUrl($imgUrl, $pageUrl);
                        $ext = strtolower(pathinfo($absUrl, PATHINFO_EXTENSION));
                        
                        if ($ext === 'gif' && in_array('gif', $this->fileTypes)) {
                            $this->forceDownloadFile($client, $absUrl, 'gif');
                        } else if (in_array('images', $this->fileTypes)) {
                            $this->forceDownloadFile($client, $absUrl, 'images');
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error extracting assets from {$pageUrl}: " . $e->getMessage());
        }
    }

    protected function getFilenameFromUrl($url)
    {
        $urlPath = parse_url($url, PHP_URL_PATH);
        
        // If URL has no path or ends with a slash, use index.html
        if (empty($urlPath) || $urlPath === '/' || substr($urlPath, -1) === '/') {
            return 'index.html';
        }
        
        // Extract filename from path
        $filename = basename($urlPath);
        
        // If filename has no extension, add .html
        if (!pathinfo($filename, PATHINFO_EXTENSION)) {
            $filename .= '.html';
        }
        
        // If filename doesn't end with .html, replace extension with .html
        if (pathinfo($filename, PATHINFO_EXTENSION) !== 'html') {
            $filename = pathinfo($filename, PATHINFO_FILENAME) . '.html';
        }
        
        return $filename;
    }

    protected function resolveUrl($relativeUrl, $baseUrl)
    {
        // If URL is already absolute, return it
        if (filter_var($relativeUrl, FILTER_VALIDATE_URL)) {
            return $relativeUrl;
        }
        
        // If URL starts with //, add scheme
        if (strpos($relativeUrl, '//') === 0) {
            return parse_url($baseUrl, PHP_URL_SCHEME) . ':' . $relativeUrl;
        }
        
        // If URL starts with /, add base URL
        if (strpos($relativeUrl, '/') === 0) {
            return rtrim($this->baseUrl, '/') . $relativeUrl;
        }
        
        // Otherwise, resolve relative to the current page
        $basePath = dirname(parse_url($baseUrl, PHP_URL_PATH));
        if ($basePath === '/') $basePath = '';
        
        return $this->baseUrl . $basePath . '/' . $relativeUrl;
    }

    protected function getExtensionFromType($type)
    {
        switch ($type) {
            case 'css': return '.css';
            case 'js': return '.js';
            case 'images': return '.jpg';
            case 'gif': return '.gif';
            case 'pdf': return '.pdf';
            case 'doc': return '.doc';
            case 'zip': return '.zip';
            case 'video': return '.mp4';
            case 'audio': return '.mp3';
            default: return '';
        }
    }

    protected function createZip()
    {
        $zipPath = 'public/downloads/' . $this->filename . '.zip';
        $zipFullPath = storage_path('app/' . $zipPath);
        
        Log::info("Creating ZIP file at: {$zipFullPath}");
        
        try {
            // Make sure the directory exists
            $downloadDir = dirname($zipFullPath);
            if (!is_dir($downloadDir)) {
                mkdir($downloadDir, 0755, true);
            }
            
            $zip = new ZipArchive();
            $result = $zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            
            if ($result !== true) {
                Log::error("Failed to create ZIP file. ZipArchive error code: {$result}");
                throw new \Exception("Could not create ZIP file (error code: {$result})");
            }
            
            $files = Storage::allFiles($this->baseDir);
            Log::info("Found " . count($files) . " files to add to ZIP");
            
            if (count($files) === 0) {
                Log::warning("No files found in {$this->baseDir} to add to ZIP");
                // Add a dummy file so the ZIP isn't empty
                $zip->addFromString('README.txt', 'No files were found to download.');
            } else {
                foreach ($files as $file) {
                    $sourcePath = storage_path('app/' . $file);
                    $entryName = str_replace($this->baseDir . '/', '', $file);
                    
                    if (file_exists($sourcePath)) {
                        $zip->addFile($sourcePath, $entryName);
                    } else {
                        Log::warning("File not found: {$sourcePath}");
                    }
                }
            }
            
            $zip->close();
            
            // Verify the ZIP was created
            if (file_exists($zipFullPath)) {
                Log::info("ZIP file created successfully: {$zipFullPath} (size: " . filesize($zipFullPath) . " bytes)");
            } else {
                Log::error("ZIP file was not created: {$zipFullPath}");
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to create ZIP file: {$e->getMessage()}\n{$e->getTraceAsString()}");
            throw $e; // Re-throw to be caught by the main handler
        }

        // Clean up by deleting the temporary directory after creating the ZIP
        Storage::deleteDirectory($this->baseDir);
    }
}
