<?php

use App\Http\Controllers\Admin\PageAdminController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\ProtectAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

// Calculators
Route::get('/calculators/fd-calculator', [CalculatorController::class, 'fd'])->name('calculators.fd');
Route::get('/calculators/sip-calculator', [CalculatorController::class, 'sip'])->name('calculators.sip');
Route::post('/calculators/fd-calculator', [CalculatorController::class, 'calculateFd'])->name('calculators.fd.calculate');
Route::post('/calculators/sip-calculator', [CalculatorController::class, 'calculateSip'])->name('calculators.sip.calculate');

// Enquiry form - max 5 enquiries per minute per visitor (IP) to stop spam
Route::post('/enquiry', [LeadController::class, 'store'])->middleware('throttle:5,1')->name('leads.store');

// Admin area (login: ADMIN_USER / ADMIN_PASSWORD in .env)
Route::middleware(ProtectAdmin::class)->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/pages');
    Route::resource('pages', PageAdminController::class)->except('show');
    Route::get('/leads', [LeadController::class, 'report'])->name('leads');
    Route::get('/utm-builder', [LeadController::class, 'utmBuilder'])->name('utm');
});

// Sitemaps
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap-{name}.xml', [SitemapController::class, 'show'])->name('sitemap.show');

// CMS pages
Route::get('/products/{slug}', [PageController::class, 'show'])->defaults('type', 'product')->name('products.show');
Route::get('/blog/{slug}', [PageController::class, 'show'])->defaults('type', 'blog')->name('blog.show');
Route::get('/branches/{slug}', [PageController::class, 'show'])->defaults('type', 'branch')->name('branches.show');
Route::get('/{slug}', [PageController::class, 'show'])->defaults('type', 'static')->name('pages.show');
