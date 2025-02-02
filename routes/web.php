<?php

use Illuminate\Support\Facades\Route;

use App\Http\Livewire\Auth\ForgotPassword;
use App\Http\Livewire\Auth\ResetPassword;
use App\Http\Livewire\Auth\SignUp;
use App\Http\Livewire\Auth\Login;
use App\Http\Livewire\Dashboard;

use App\Http\Livewire\Profile;



use App\Http\Livewire\Questions\QuestionComponent;
use App\Http\Livewire\Subjects\SubjectComponent;
use App\Http\Livewire\YearGroups\YearGroupComponent;

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

Route::get('/reset-password/{id}', ResetPassword::class)->name('reset-password')->middleware('signed');

Route::middleware('auth')->group(function () {
    Route::get('/profile', Profile::class)->name('profile');
   
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    
    // Livewire Components for Features
    Route::get('/questions', QuestionComponent::class)->name('questions');
    Route::get('/year-group', YearGroupComponent::class)->name('year-group');
    Route::get('/subject', SubjectComponent::class)->name('subject'); // ✅ Corrected
});
