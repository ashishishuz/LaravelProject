<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\JobsController;
use App\Http\Middleware\RedirectIfNotAuthenticated; 

// home page
Route::get('/',[HomeController::class,'index'])->name('home');

// showJob
Route::get('/jobs',[JobsController::class,'index'])->name('jobs');

// jobDetails page
Route::get('/jobs/detail/{id}',[JobsController::class,'detail'])->name('jobDetail');

// Apply Job
Route::post('/apply-job',[JobsController::class,'applyJob'])->name('applyJob');

// SaveJob
Route::post('/save-job',[JobsController::class,'saveJob'])->name('saveJob');





    //guest routes
    //    after loggedin route again login not wotking
        // register route
        Route::get('/account/register',[AccountController::class,'registration'])->name('account.registration');

        Route::post('/account/process-register',[AccountController::class,'processRegistration'])->name('account.processRegistration');

        Route::get('/account/login',[AccountController::class,'login'])->name('account.login');

        Route::post('/account/authenticate',[AccountController::class,'authenticate'])->name('account.authenticate');



    // Authenticated Routes if not loggedin and tries to access profile page it will redirect to login page
   
    Route::middleware([RedirectIfNotAuthenticated::class])->group(function () {
        Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');

        // UpdateProfile Route
        Route::put('/account/update-profile', [AccountController::class, 'updateProfile'])->name('account.updateProfile');
        
        // Login Route
        Route::get('/account/logout', [AccountController::class, 'logout'])->name('account.logout');

        //ProfilePic update Route
        Route::post('/account/update-profile-pic', [AccountController::class, 'updateProfilePic'])->name('account.updateProfilePic');

        // createJob Route
        Route::get('/account/create-job', [AccountController::class, 'createJob'])->name('account.createJob');

        // SaveJob Route
        Route::post('/account/save-job', [AccountController::class, 'saveJob'])->name('account.saveJob');

        // PostingJob
        Route::get('/account/my-jobs', [AccountController::class, 'myJobs'])->name('account.myJobs');


        //editJob
        Route::get('/account/my-jobs/edit/{jobId}', [AccountController::class, 'editJob'])->name('account.editJob');

        // updateJob
        Route::post('/account/update-job/{jobId}', [AccountController::class, 'updateJob'])->name('account.updateJob');

        // deleteJob
        Route::post('/account/delete-job', [AccountController::class, 'deleteJob'])->name('account.deleteJob');
       
        // myJobApplications 
        Route::get('/account/my-job-applications', [AccountController::class, 'myJobApplications'])->name('account.myJobApplications');

        // reomoveJobs
        Route::post('/account/remove-job-application', [AccountController::class, 'reomoveJobs'])->name('account.reomoveJobs');


        // SavedJobs
        Route::get('/account/saved-jobs', [AccountController::class, 'savedJobs'])->name('account.savedJobs');

        // removeSavedJob
        Route::post('/account/remove-saved-job', [AccountController::class, 'removeSavedJob'])->name('account.removeSavedJob');

        // updatePassword
        Route::post('/account/update-password', [AccountController::class, 'updatePassword'])->name('account.updatePassword');

        







        
        
    });

        


