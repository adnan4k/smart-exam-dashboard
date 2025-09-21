<?php

use Illuminate\Support\Facades\Route;

use App\Http\Livewire\Auth\ForgotPassword;
use App\Http\Livewire\Auth\ResetPassword;
use App\Http\Livewire\Auth\SignUp;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Chapter\ChapterComponent;
use App\Http\Livewire\Dashboard;

use App\Http\Livewire\Profile;



use App\Http\Livewire\Questions\QuestionComponent;
use App\Http\Livewire\Referral\ReferralComponent;
use App\Http\Livewire\Referral\ReferralSetting\ReferralSettingComponent;
use App\Http\Livewire\Subjects\SubjectComponent;
use App\Http\Livewire\Subscription\SubscriptionComponent;
use App\Http\Livewire\Type\TypeComponent;
use App\Http\Livewire\User\UserComponent;
use App\Http\Livewire\YearGroups\YearGroupComponent;
use App\Http\Livewire\Notes\NoteComponent;
use App\Models\Referral;

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

Route::get('privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/sign-up', SignUp::class)->name('sign-up');
Route::get('/login', Login::class)->name('login');

Route::get('/login/forgot-password', ForgotPassword::class)->name('forgot-password');

Route::get('/reset-password/{id}', ResetPassword::class)->name('reset-password')->middleware('signed');

Route::middleware('auth')->group(function () {
    Route::get('/profile', Profile::class)->name('profile');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Livewire Components for Features
    Route::get('/questions', QuestionComponent::class)->name('questions');
    Route::get('/year-group', YearGroupComponent::class)->name('year-group');
    Route::get('/subject', SubjectComponent::class)->name('subject'); // ✅ Corrected
    Route::get('/subscription', SubscriptionComponent::class)->name('subscription'); // ✅ Corrected
    Route::get('/users', UserComponent::class)->name('users');
    Route::get('/type', TypeComponent::class)->name('type');
    Route::get('referral', ReferralComponent::class)->name('referral');
    Route::get('referral-setting', ReferralSettingComponent::class)->name('referral-setting');
    Route::get('chapter', ChapterComponent::class)->name('chapter');
    Route::get('notes', NoteComponent::class)->name('notes');
});
