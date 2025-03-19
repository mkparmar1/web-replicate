<?php

use App\Http\Controllers\WebsiteCopierController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [WebsiteCopierController::class, 'index'])->name('home');
Route::post('/copy', [WebsiteCopierController::class, 'copy'])->name('copy')->middleware('throttle:10,1');
Route::get('/status/{filename}', [WebsiteCopierController::class, 'status'])->name('status'); // New route for status
Route::get('/download/{filename}', [WebsiteCopierController::class, 'download'])->name('download');
Route::get('/sitemap.xml', [WebsiteCopierController::class, 'sitemap'])->name('sitemap');
