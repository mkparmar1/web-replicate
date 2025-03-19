<?php

namespace Tests\Feature;

use App\Jobs\CopyWebsite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteCopierTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Queue::fake();
    }

    public function test_home_page_loads()
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('Website Copier');
        $response->assertSee('HTML');
        $response->assertSee('CSS');
        $response->assertSee('JS');
        $response->assertSee('Images');
        $response->assertSee('GIFs');
        $response->assertSee('Max Pages');
    }

    public function test_copy_request_validates_input()
    {
        // Invalid URL
        $response = $this->postJson(route('copy'), ['url' => 'invalid', 'file_types' => ['html'], 'max_pages' => 50]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('url');

        // No file types
        $response = $this->postJson(route('copy'), ['url' => 'http://example.com', 'file_types' => [], 'max_pages' => 50]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file_types');

        // Invalid max_pages
        $response = $this->postJson(route('copy'), ['url' => 'http://example.com', 'file_types' => ['html'], 'max_pages' => 0]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('max_pages');
    }

    public function test_copy_request_queues_job_with_max_pages()
    {
        $url = 'http://example.com';
        $fileTypes = ['html', 'css'];
        $maxPages = 100;
        $response = $this->postJson(route('copy'), ['url' => $url, 'file_types' => $fileTypes, 'max_pages' => $maxPages]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('message');
        Queue::assertPushed(CopyWebsite::class, function ($job) use ($url, $fileTypes, $maxPages) {
            return $job->url === $url && $job->fileTypes === $fileTypes && $job->maxPages === $maxPages;
        });
    }

    public function test_status_returns_progress()
    {
        $filename = 'testfile';
        session()->put("status_{$filename}", ['pages_copied' => 5, 'total_pages' => 10]);
        $response = $this->getJson(route('status', $filename));

        $response->assertStatus(200);
        $response->assertJson(['pages_copied' => 5, 'total_pages' => 10, 'completed' => false]);
    }

    public function test_status_returns_completed()
    {
        $filename = 'testfile';
        session()->put("status_{$filename}", ['pages_copied' => 10, 'total_pages' => 10]);
        Storage::put("public/downloads/{$filename}.zip", 'test content');
        $response = $this->getJson(route('status', $filename));

        $response->assertStatus(200);
        $response->assertJson(['pages_copied' => 10, 'total_pages' => 10, 'completed' => true]);
        $response->assertJsonStructure(['download_url']);
    }

    public function test_download_returns_file_if_exists()
    {
        Storage::put('public/downloads/testfile.zip', 'test content');
        $response = $this->get(route('download', 'testfile'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=website_copy.zip');
    }

    public function test_download_fails_if_file_missing()
    {
        $response = $this->get(route('download', 'nonexistent'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }
}
