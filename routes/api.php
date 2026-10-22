<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CommonController;
use App\Http\Controllers\API\Employer\JobController;
use App\Http\Controllers\API\EmployerInformationController;
use App\Http\Controllers\API\Jobseeker\JobApplicationController;
use App\Http\Controllers\API\Jobseeker\OthersController;
use App\Http\Controllers\API\Jobseeker\PrivateDocumentController;
use App\Http\Controllers\API\Admin\PrivateDocumentReviewController;
use App\Http\Controllers\Interview\InterviewController;
use App\Http\Controllers\Chat\ApplicationChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Auth routes...............
Route::post('registration', [AuthController::class, 'registerEmployer'])->middleware('throttle:5,1');
Route::post('registration/verify', [AuthController::class, 'confirmRegistration'])->middleware('throttle:5,1');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('update-password', [AuthController::class, 'updatePassword'])->middleware(['auth:sanctum', 'throttle:5,1']);
Route::post('confirm-password-otp', [AuthController::class, 'confirmPasswordOtp'])->middleware(['auth:sanctum', 'throttle:5,1']);
//Home Page  route.....
Route::get('home-page', [CommonController::class, 'getHomePageData']);
Route::get('search-job', [JobApplicationController::class, 'jobSearch']);
Route::get('view-job-details/{id}', [CommonController::class, 'jobDetails']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'employer', 'middleware' => 'auth:sanctum'], function () {
    // Employers Profile
    Route::get('get-information', [EmployerInformationController::class, 'getEmployeeInfo']);
    Route::get('country-list', [EmployerInformationController::class, 'getCountryList']);
    Route::post('update/profile', [EmployerInformationController::class, 'updateEmployeeInfo']);
    //Job management routes..............
    Route::get('job-analytics', [JobController::class, 'jobAnalytics']);
    Route::get('jobs', [JobController::class, 'jobList']);
    Route::get('job/{id}', [JobController::class, 'jobDetails']);
    Route::get('archive-jobs', [JobController::class, 'archiveJob']);
    Route::post('job/archive/{id}', [JobController::class, 'destroy']);
    Route::post('restore-jobs/{id}', [JobController::class, 'restoreJob']);
    Route::post('job/create', [JobController::class, 'store']);
    Route::post('job/update/{id}', [JobController::class, 'update']);
    Route::get('job/{job_id}/applicant-list', [JobController::class, 'jobApplicantList']);
    Route::get('view-jobseeker-details/{id}', [JobController::class, 'viewJobseekerProfile']);
    Route::get('job/{job_id}/shortlisted-applicant', [JobController::class, 'getShortListedApplicant']);
    Route::get('job/{job_id}/interviewed-applicant', [JobController::class, 'getInterviewedApplicant']);
    Route::get('view-resume/{user_id}/{application_id}', [JobController::class, 'getApplicantResume']);
    Route::post('update-application-status', [JobController::class, 'updateApplicationStatus']);

    //Company Info routes.......
    Route::get('company-information', [EmployerInformationController::class, 'companyInformation']);
    Route::get('company-sizes', [EmployerInformationController::class, 'companySize']);
    Route::post('update/company-information', [EmployerInformationController::class, 'updateCompanyInfo']);
});

Route::group(['prefix' => 'jobseeker', 'middleware' => 'auth:sanctum'], function () {
    // New versioned, private document workflow; legacy CV URLs stay unchanged.
    Route::get('private-documents', [PrivateDocumentController::class, 'index']);
    Route::post('private-documents', [PrivateDocumentController::class, 'upload'])
        ->middleware('throttle:10,1');
    Route::post('private-documents/{id}/revisions', [PrivateDocumentController::class, 'revise'])
        ->middleware('throttle:10,1');
    Route::get('private-documents/{id}/history', [PrivateDocumentController::class, 'history']);
    Route::get('private-documents/{id}/versions/{version}', [PrivateDocumentController::class, 'download']);

    Route::get('resume-views', [JobApplicationController::class, 'resumeViewd']);
    Route::post('apply-job', [JobApplicationController::class, 'applyJob']);
    Route::get('job-details/{id}', [JobApplicationController::class, 'jobDetails']);
    Route::get('view-jobseeker-profile', [JobApplicationController::class, 'viewJobseekerProfile']);
    Route::post('save-personal-information', [JobApplicationController::class, 'saveProfileInfo']);
    Route::post('/upload/profile/pictute', [JobApplicationController::class, 'uploadPhoto']);
    //Language Routes.......
    Route::post('store-language', [OthersController::class, 'storeLanguage']);
    Route::post('update-language/{id}', [OthersController::class, 'updateLanguage']);
    Route::delete('delete-language/{id}', [OthersController::class, 'deleteLanguage']);
    //Skill Routes.......
    Route::post('store-skill', [OthersController::class, 'storeSkill']);
    Route::post('update-skill/{id}', [OthersController::class, 'updateSkill']);
    Route::delete('delete-skill/{id}', [OthersController::class, 'deleteSkill']);
    //Education Routes.......
    Route::post('store-education', [OthersController::class, 'storeEducation']);
    Route::post('update-education/{id}', [OthersController::class, 'updateEducation']);
    Route::delete('delete-education/{id}', [OthersController::class, 'deleteEducation']);
    //Training Routes.......
    Route::post('store-training', [OthersController::class, 'storeTraining']);
    Route::post('update-training/{id}', [OthersController::class, 'updateTraining']);
    Route::delete('delete-training/{id}', [OthersController::class, 'deleteTraining']);
    //Work Experience Routes.......
    Route::post('store-work-experience', [OthersController::class, 'storeWorkExperience']);
    Route::post('update-work-experience/{id}', [OthersController::class, 'updateWorkExperience']);
    Route::delete('delete-work-experience/{id}', [OthersController::class, 'deleteWorkExperience']);

    //My Library..................................................................
    Route::post('store-import-cv', [JobApplicationController::class, 'uploadCv']);
    Route::get('/generate-pdf', [JobApplicationController::class, 'generatePDF']);

    Route::get('/cv-list', [OthersController::class, 'getCVList']);
    Route::get('/cv-download/{id}', [OthersController::class, 'downloadCv']);
    Route::delete('/cv-delete/{id}', [OthersController::class, 'deleteCv']);

    Route::get('/certificate-list', [OthersController::class, 'getCertificateList']);
    Route::get('/certificate-download/{id}', [OthersController::class, 'downloadCertificate']);
    Route::delete('/certificate-delete/{id}', [OthersController::class, 'deleteCertificate']);

    Route::get('/documents-list', [OthersController::class, 'getDocumentsList']);
    Route::get('/documents-download/{id}', [OthersController::class, 'downloadDocuments']);
    Route::delete('/documents-delete/{id}', [OthersController::class, 'deleteDocuments']);

    Route::get('/get-coverletter-info', [JobApplicationController::class, 'coverLetter']);
    Route::get('/export-to-word', [JobApplicationController::class, 'exportToWord'])->name('download-cover-letter');
    Route::get('/download-pdf-cover-letter', [JobApplicationController::class, 'exportToPDF'])->name('download-pdf-cover-letter');
    Route::post('save-person-details', [JobApplicationController::class, 'savePersonInfo'])->name('save-person-details');
    Route::post('save-person-contact-details', [JobApplicationController::class, 'saveContactInfo'])->name('save-person-contact-details');
    Route::post('save-person-content-details', [JobApplicationController::class, 'saveContentInfo'])->name('save-person-content-details');
    Route::post('save-person-closing-details', [JobApplicationController::class, 'saveClosingInfo'])->name('save-person-closing-details');

    Route::post('store-jobseeer-certificate', [JobApplicationController::class, 'storeCertificate']);
    Route::post('jobseeker-upload-document', [JobApplicationController::class, 'jobseekerDocUpload']);

    Route::get('get-all-applied-jobs', [JobApplicationController::class, 'getAppliedJob']);
    Route::get('applications/{id}/timeline', [JobApplicationController::class, 'applicationTimeline']);
    Route::get('get-all-shortlist-jobs', [JobApplicationController::class, 'getShortlistedJob']);
    Route::post('send-resume', [JobApplicationController::class, 'sendResume']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('all-locations', [CommonController::class,  'getAllLocations']);
    Route::get('job-categories', [CommonController::class,  'getJobCategories']);
    Route::get('degree-level', [CommonController::class,  'getDegreeLevel']);
    Route::get('degree-name', [CommonController::class,  'getDegreeName']);
    Route::get('job-type', [CommonController::class,  'getJobType']);
    Route::get('industry-type', [CommonController::class,  'getIndustryType']);
    Route::post('contact-submit', [CommonController::class,  'contactStore']);
    Route::get('category-wise-skills', [CommonController::class,  'categoryWiseSkills']);
});

Route::middleware(['auth:sanctum', 'throttle:30,1'])->prefix('admin')->group(function () {
    Route::get('private-documents/{id}', [PrivateDocumentReviewController::class, 'show']);
    Route::get('private-documents/{id}/versions/{version}', [PrivateDocumentReviewController::class, 'download']);
    Route::post('private-documents/{id}/review', [PrivateDocumentReviewController::class, 'decide']);
});


// All interview data is scoped by applicant ownership or authorized organizer.
Route::middleware(['auth:sanctum', 'throttle:30,1'])->prefix('interviews')->group(function () {
    Route::get('/my', [InterviewController::class, 'applicantIndex']);
    Route::get('/my/{application}', [InterviewController::class, 'applicantShow']);
    Route::post('/my/{application}/reply', [InterviewController::class, 'act']);

    Route::get('/manage', [InterviewController::class, 'managerIndex']);
    Route::get('/manage/{application}', [InterviewController::class, 'managerShow']);
    Route::post('/manage/{application}/schedule', [InterviewController::class, 'schedule']);
    Route::post('/manage/{application}/action', [InterviewController::class, 'act']);
});
