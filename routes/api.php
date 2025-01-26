<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\PodcastController;
use App\Http\Controllers\Section\SectionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TourController;
use App\Http\Livewire\Booking\BookingComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
   Route::post('book',[BookingController::class,'store']);
   Route::get('blog-detail/{id}',[SectionController::class,'getBlogDetail']);
   Route::get('testimonials', [SectionController::class, 'getTestimonials']);
    Route::get('package-detail/{id}', [TourController::class, 'tourById']);
    Route::get('home-destinations', [SectionController::class, 'getHomeDestinations']);
    Route::get('packages', [TourController::class, 'getPackages']);
    Route::get('home-packages', [TourController::class, 'getHomePackages']);
    Route::get('destinations', [SectionController::class, 'getAllDestionations']);
    Route::get('podcasts', [PodcastController::class, 'index']); // Fetch all podcasts
    Route::get('latest-podcasts', [PodcastController::class, 'latestPodcasts']); // Fetch all podcasts
    Route::get('all-podcasts', [PodcastController::class, 'allPodcasts']); // Fetch all podcasts
    Route::get('/services', [ServiceController::class, 'index'])->name('services');
    Route::get('/articles', [GeneralController::class, 'articles'])->name('articles');
    Route::get('blogs', [GeneralController::class, 'blogs'])->name('blogs');
    Route::get('blog-by-id',[GeneralController::class,'getBlogById']);
    Route::get('vacancy', [OpportunityController::class, 'vacancy'])->name('vacancy');
    Route::get('scholarship', [OpportunityController::class, 'scholarship'])->name('scholarship');
    Route::get('biography', [GeneralController::class, 'biography'])->name('biography');
    Route::post('contacts', [ContactController::class, 'contact']); 
    Route::post('partnership', [ContactController::class, 'partnership']); 
    Route::post('subscribe', [ContactController::class, 'subscribe']); 
    Route::get('destination-detail/{id}', [SectionController::class, 'getDestinationDetail']);
    Route::get('hero-slider', [SectionController::class, 'getHeroSlider']);
    Route::get('slider-section', [SectionController::class, 'getSliderSection']);
    // Route::get('tou-detail/{id}', [SectionController::class, 'getPackageDetail']);



