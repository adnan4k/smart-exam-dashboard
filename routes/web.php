<?php

use App\Http\Livewire\Packages\PackageComponent;
use Illuminate\Support\Facades\Route;

use App\Http\Livewire\Auth\ForgotPassword;
use App\Http\Livewire\Auth\ResetPassword;
use App\Http\Livewire\Auth\SignUp;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Billing;
use App\Http\Livewire\Blogs\BlogComponent;
use App\Http\Livewire\Blogs\Form as BlogsForm;
use App\Http\Livewire\Boiography\BiographyComponent;
use App\Http\Livewire\Boiography\Form as BoiographyForm;
use App\Http\Livewire\Booking\BookingComponent;
use App\Http\Livewire\Categories\CategoryComponent;
use App\Http\Livewire\Categories\Form as CategoriesForm;
use App\Http\Livewire\Contact\ContactComponent;
use App\Http\Livewire\Contact\PartnershipComponent;
use App\Http\Livewire\Profile;
use App\Http\Livewire\Tables;
use App\Http\Livewire\StaticSignIn;
use App\Http\Livewire\StaticSignUp;
use App\Http\Livewire\Rtl;

use App\Http\Livewire\LaravelExamples\UserProfile;
use App\Http\Livewire\LaravelExamples\UserManagement;
use App\Http\Livewire\Opportunity\Scholarship\Form as ScholarshipForm;
use App\Http\Livewire\Opportunity\Scholarship\SholarshipComponent;
use App\Http\Livewire\Opportunity\Vacancy\Detail;
use App\Http\Livewire\Opportunity\Vacancy\Form as VacancyForm;
use App\Http\Livewire\Opportunity\Vacancy\VacancyComponent;
use App\Http\Livewire\Podcast\Detial;
use App\Http\Livewire\Podcast\Form;
use App\Http\Livewire\Podcast\PodcastComponent;
use App\Http\Livewire\Schedule\ScheduleComponent;
use App\Http\Livewire\Sections\SectionComponent;
use App\Http\Livewire\Services\Form as ServicesForm;
use App\Http\Livewire\Services\ServiceComponent;
use App\Http\Livewire\Subscription\SubscriptionController;
use App\Http\Livewire\Testimony\TestimonyComponent;
use App\Http\Livewire\Tour\TourComponent;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function() {
    return redirect('/login');
});

Route::get('/sign-up', SignUp::class)->name('sign-up');
Route::get('/login', Login::class)->name('login');

Route::get('/login/forgot-password', ForgotPassword::class)->name('forgot-password');

Route::get('/reset-password/{id}',ResetPassword::class)->name('reset-password')->middleware('signed');

Route::middleware('auth')->group(function () {
    Route::get('packages',PackageComponent::class)->name('packages');
    Route::get('sections',SectionComponent::class)->name('sections');
    Route::get('booking',BookingComponent::class)->name('booking');

    Route::get('testimonials',TestimonyComponent::class)->name('testimonials');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/billing', Billing::class)->name('billing');
    Route::get('/profile', Profile::class)->name('profile');
    Route::get('/tables', Tables::class)->name('tables');
    Route::get('/static-sign-in', StaticSignIn::class)->name('sign-in');
    Route::get('/static-sign-up', StaticSignUp::class)->name('static-sign-up');
    Route::get('/rtl', Rtl::class)->name('rtl');
    Route::get('/laravel-user-profile', UserProfile::class)->name('user-profile');
    Route::get('/laravel-user-management', UserManagement::class)->name('user-management');
    Route::get('/podcast', PodcastComponent::class)->name('podcast');
    Route::get('/podcast-detail/{podcast}',Detial::class)->name('podcast.detail');
    Route::get('/add-podcast',Form::class)->name(name: 'add-podcast');
    Route::get('/category',CategoryComponent::class)->name('category');
    Route::get('/add-category',CategoriesForm::class)->name('add-category');
    Route::get('blogs',BlogComponent::class)->name('blogs');
    Route::get('add-blog',BlogsForm::class)->name('add-blog');
    Route::get('sliders',ServiceComponent::class)->name('sliders');
    Route::get('add-service',ServicesForm::class)->name('add-service');
    Route::get('add-biography',BoiographyForm::class)->name('add-biography');
    
    Route::get('/opportunity/add-vacancy',VacancyForm::class)->name('opportunity.add-vacancy');
    Route::get('vacancies',VacancyComponent::class)->name('vacancies');
    Route::get('scholarships',SholarshipComponent::class)->name('scholarships');
    Route::get('add-sholarship',ScholarshipForm::class)->name('add-sholarship');
    Route::get('detail/{vacancy}',Detail::class)->name('detail');
    Route::get('contact',ContactComponent::class)->name('contact');
    Route::get('partnership',PartnershipComponent::class)->name('partnership');
    Route::get('subscription',SubscriptionController::class)->name('subscription');
    Route::get('/schedule',ScheduleComponent::class)->name('schedule');
    Route::get('tour',TourComponent::class)->name('tour');

});

