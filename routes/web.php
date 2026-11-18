<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DestinationController as AdminDestinationController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Admin\SessionController as AdminSessionController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SitemapController as AdminSitemapController;
use App\Http\Controllers\Admin\WebsiteContentController;
use App\Http\Controllers\Admin\SetupController as AdminSetupController;
use App\Http\Controllers\Admin\TourPackageController as AdminTourPackageController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\TourController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/about-us', [\App\Http\Controllers\TravelPageController::class, 'about'])->name('about');
Route::get('/responsible-travel', [\App\Http\Controllers\TravelPageController::class, 'responsible'])->name('responsible');
Route::get('/travel-guide', [\App\Http\Controllers\TravelPageController::class, 'guide'])->name('travel-guide');
Route::get('/safaris', [TourController::class, 'index'])->name('tours.index');
Route::get('/safaris/{tour:slug}', [TourController::class, 'show'])->name('tours.show');
Route::get('/accommodations', [HomeController::class, 'accommodations'])->name('accommodations.index');
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination:slug}', [DestinationController::class, 'show'])->name('destinations.show');
Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
Route::get('/journal/{article:slug}', [JournalController::class, 'show'])->name('journal.show');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/contact', [InquiryController::class, 'create'])->name('contact');
Route::post('/inquiries', [InquiryController::class, 'store'])->middleware('throttle:5,1')->name('inquiries.store');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/setup', [AdminSetupController::class, 'create'])->middleware('throttle:10,1')->name('setup');
    Route::post('/setup', [AdminSetupController::class, 'store'])->middleware('throttle:5,1')->name('setup.store');
    Route::get('/login', [AdminSessionController::class, 'create'])->name('login');
    Route::post('/login', [AdminSessionController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
    Route::post('/logout', [AdminSessionController::class, 'destroy'])->middleware('auth')->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::resource('/tours', AdminTourPackageController::class)->except(['show']);
        Route::prefix('package-generator')->name('generator.')->controller(\App\Http\Controllers\Admin\TourGeneratorController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/imports', 'index')->name('imports');
            Route::post('/runs', 'generate')->middleware('throttle:6,1')->name('generate');
            Route::get('/runs/{run}', 'run')->whereNumber('run')->name('run');
            Route::get('/templates/{format}', 'template')->name('template');
            Route::post('/imports', 'upload')->middleware('throttle:10,1')->name('upload');
            Route::post('/imports/tour', 'fromTour')->name('from-tour');
            Route::get('/imports/{batch}', 'review')->whereNumber('batch')->name('review');
            Route::get('/imports/{batch}/preview/{record}', 'importPreview')->whereNumber(['batch', 'record'])->name('import-preview');
            Route::patch('/imports/{batch}/records/{record}', 'correct')->whereNumber(['batch', 'record'])->name('correct');
            Route::post('/imports/{batch}/approve', 'approve')->whereNumber('batch')->name('approve');
            Route::get('/drafts/{tour}/preview', 'preview')->name('preview');
        });
        Route::resource('/countries', \App\Http\Controllers\Admin\CountryController::class)->except(['show']);
        Route::resource('/tour-categories', \App\Http\Controllers\Admin\TourCategoryController::class)->except(['show']);
        Route::resource('/accommodations', \App\Http\Controllers\Admin\AccommodationController::class)->except(['show']);
        Route::resource('/destinations', AdminDestinationController::class)->except(['show']);
        Route::resource('/articles', AdminArticleController::class)->except(['show']);
        Route::resource('/faqs', AdminFaqController::class)->except(['show']);
        Route::get('/gallery', [AdminGalleryController::class, 'index'])->name('gallery.index');
        Route::post('/gallery', [AdminGalleryController::class, 'store'])->name('gallery.store');
        Route::post('/gallery/{image}/compress', [AdminGalleryController::class, 'compress'])->name('gallery.compress');
        Route::put('/gallery/{image}', [AdminGalleryController::class, 'update'])->name('gallery.update');
        Route::delete('/gallery/{image}', [AdminGalleryController::class, 'destroy'])->name('gallery.destroy');
        Route::get('/inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('inquiries.show');
        Route::patch('/inquiries/{inquiry}', [AdminInquiryController::class, 'update'])->name('inquiries.update');
        Route::delete('/inquiries/{inquiry}', [AdminInquiryController::class, 'destroy'])->name('inquiries.destroy');
        Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
        Route::get('/sitemap', [AdminSitemapController::class, 'index'])->name('sitemap.index');
        Route::post('/sitemap/generate', [AdminSitemapController::class, 'generate'])->name('sitemap.generate');
        Route::get('/package-days', [WebsiteContentController::class, 'packageDays'])->name('package-days.index');
        Route::put('/package-days', [WebsiteContentController::class, 'updatePackageDays'])->name('package-days.update');

        Route::prefix('/website-content')->name('website-content.')->controller(WebsiteContentController::class)->group(function () {
            Route::get('/hero-images', 'heroImages')->name('hero-images');
            Route::put('/hero-images', 'updateHeroImages')->name('hero-images.update');
            Route::get('/homepage', 'homepage')->name('homepage');
            Route::put('/homepage', 'updateHomepage')->name('homepage.update');
            Route::get('/traveller-stories', 'travellers')->name('travellers');
            Route::put('/traveller-stories', 'updateTravellers')->name('travellers.update');
            Route::get('/subscribe', 'subscribe')->name('subscribe');
            Route::put('/subscribe', 'updateSubscribe')->name('subscribe.update');
            Route::get('/footer', 'footer')->name('footer');
            Route::put('/footer', 'updateFooter')->name('footer.update');
            Route::get('/start-end-points', 'startEndPoints')->name('route-points');
            Route::put('/start-end-points', 'updateStartEndPoints')->name('route-points.update');
            Route::get('/listing-titles', 'listingTitles')->name('listing-titles');
            Route::put('/listing-titles', 'updateListingTitles')->name('listing-titles.update');
            Route::get('/our-story', 'storyImages')->name('story-images');
            Route::put('/our-story', 'updateStoryImages')->name('story-images.update');
        });
    });
});
