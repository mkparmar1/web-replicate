# WebReplicate

A powerful Laravel application that allows you to download and archive websites for offline viewing. WebReplicate crawls websites and downloads HTML, CSS, JavaScript, images, and various file types including PDFs, DOCs, ZIPs, videos, and audio files.

## Features

-   **Multi-file type support**: Download HTML, CSS, JS, images, GIFs, PDFs, DOCs, ZIPs, videos, and audio files
-   **Customizable page limit**: Set how many pages to crawl and download
-   **Real-time progress tracking**: See pages and assets being downloaded in real-time
-   **Organized file structure**: Files are saved in a logical directory structure
-   **Responsive design**: Works on desktop and mobile devices
-   **Modern UI**: Features 3D effects, animations, and a smokey background
-   **SEO-friendly**: Includes meta tags, sitemap, and structured data

## Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/mkparmar1/web-replicate-.git
    cd webreplicate
    ```

2. Install dependencies:

    ```bash
    composer install
    npm install && npm run dev
    ```

3. Set up environment:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. Create storage link:

    ```bash
    php artisan storage:link
    ```

5. Start the server:
    ```bash
    php artisan serve
    ```

## Usage

1. Enter the URL of the website you want to copy
2. Select which file types to download (HTML, CSS, JS, images, etc.)
3. Set the maximum number of pages to copy
4. Click "Copy Website" and wait for the process to complete
5. Download the ZIP file containing the copied website

## Technical Details

-   Built with Laravel 10
-   Uses GuzzleHTTP for making requests
-   ZipArchive for creating downloadable archives
-   Tailwind CSS and Alpine.js for the frontend
-   Font Awesome for icons

## SEO Features

WebReplicate includes several SEO optimizations:

-   Meta tags for title, description, and keywords
-   Open Graph and Twitter Card support for social sharing
-   Structured data (JSON-LD) for better search engine understanding
-   XML sitemap generation
-   Semantic HTML5 markup
-   Mobile-friendly responsive design

## Configuration

You can adjust the following settings in the `.env` file:

-   `MAX_EXECUTION_TIME`: Maximum execution time for PHP scripts (default: 300 seconds)
-   `MAX_ASSETS_PER_TYPE`: Maximum number of assets to download per type (default: 1000)
-   `APP_NAME`: Change the application name displayed in the browser tab
-   `META_DESCRIPTION`: Default meta description for SEO
-   `META_KEYWORDS`: Default meta keywords for SEO

## Limitations

-   Some websites may block web crawlers
-   JavaScript-rendered content may not be captured
-   Very large websites may time out during copying
-   Some assets may be protected or require authentication

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Acknowledgements

-   [Laravel](https://laravel.com/)
-   [Tailwind CSS](https://tailwindcss.com/)
-   [Alpine.js](https://alpinejs.dev/)
-   [Font Awesome](https://fontawesome.com/)
