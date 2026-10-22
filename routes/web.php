<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\CompanySizeController;
use App\Http\Controllers\Admin\CvBankController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DegreeLavelController;
use App\Http\Controllers\Admin\DegreeNameController;
use App\Http\Controllers\Admin\FrontPageController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\IndustryTypeController;
use App\Http\Controllers\Admin\JobCategoryController;
use App\Http\Controllers\Admin\ApplicationStatusDefinitionController;
use App\Http\Controllers\Secure\ApplicantDocumentWebController;
use App\Http\Controllers\Secure\AdminDocumentWebController;
use App\Http\Controllers\Interview\InterviewController;
use App\Http\Controllers\Admin\JobSeekerController;
use App\Http\Controllers\Admin\JobTypeController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MassmailController;
use App\Http\Controllers\Admin\EmailStudioController;
use App\Http\Controllers\Admin\EmailStudioSmtpController;
use App\Http\Controllers\Chat\ApplicationChatController;
use App\Http\Controllers\Admin\PaymentCredentialsController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\SocialSettingsController;

use App\Http\Controllers\AdsController;
use App\Http\Controllers\AjaxSearchController;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RegistrationOtpController;
use App\Http\Controllers\Auth\SocialiteLoginController;

use App\Http\Controllers\ContactController;

use App\Http\Controllers\Employer\CompanyController;
use App\Http\Controllers\Employer\EmployeerController;
use App\Http\Controllers\Employer\EmployerCvBankController;
use App\Http\Controllers\Employer\EmployerInformationController;
use App\Http\Controllers\Employer\JobAnalyticsController;
use App\Http\Controllers\Employer\JobController;
use App\Http\Controllers\Employer\ManageJobController;

use App\Http\Controllers\Frontend\HomeController;

use App\Http\Controllers\JobSeeker\EmailResumeController;
use App\Http\Controllers\JobSeeker\JobApplicationController;
use App\Http\Controllers\JobSeeker\ApplicationTimelineController;
use App\Http\Controllers\JobSeeker\JobSeekerCoverletterController;
use App\Http\Controllers\JobSeeker\JobSeekerDashboardController;
use App\Http\Controllers\JobSeeker\JobSeekerEducationController;
use App\Http\Controllers\JobSeeker\JobSeekerExperienceController;
use App\Http\Controllers\JobSeeker\JobseekerImportController;
use App\Http\Controllers\JobSeeker\JobSeekerLanguageController;
use App\Http\Controllers\JobSeeker\JobSeekerProfileController;
use App\Http\Controllers\JobSeeker\JobSeekerProfileSettingController;
use App\Http\Controllers\JobSeeker\JobSeekerProfileShowController;
use App\Http\Controllers\JobSeeker\JobseekerResumeController;
use App\Http\Controllers\JobSeeker\JobSeekerSkillController;
use App\Http\Controllers\JobSeeker\JobSeekerTrainingController;
use App\Http\Controllers\JobSeeker\NotificationController;
use App\Http\Controllers\JobSeeker\PdfController;
use App\Http\Controllers\JobSeeker\UniqueCodeController;

use App\Http\Controllers\LangController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\UserAuthLoginController;
use App\Http\Controllers\Security\PasswordOtpController;
use App\Http\Controllers\WordController;

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');


/*
|--------------------------------------------------------------------------
| EMPLOYER REGISTRATION
|--------------------------------------------------------------------------
|
| This is the separate employer registration link:
| https://joingallagher.uk/registration/form
|
*/

Route::get('registration/form', [EmployeerController::class, 'register'])
    ->name('registration.form');

Route::post('employer-register', [EmployeerController::class, 'registerStore'])
    ->middleware('throttle:5,1')
    ->name('employer-register.store');


/*
|--------------------------------------------------------------------------
| JOB SEEKER REGISTRATION
|--------------------------------------------------------------------------
|
| Public Job Seeker registration:
| https://joingallagher.uk/register
|
*/

Route::middleware('guest')->group(function () {

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');

    Route::get('register/verify-email', [RegistrationOtpController::class, 'show'])
        ->name('registration.otp.show');
    Route::post('register/verify-email', [RegistrationOtpController::class, 'confirm'])
        ->middleware('throttle:5,1')
        ->name('registration.otp.confirm');

});


/*
|--------------------------------------------------------------------------
| LOGIN / LOGOUT
|--------------------------------------------------------------------------
*/

Route::get('login', [UserAuthLoginController::class, 'login'])
    ->name('login');

Route::post('user/login', [UserAuthLoginController::class, 'userLogin'])
    ->middleware('throttle:10,1')
    ->name('user.login');

Route::post('logout', [UserAuthLoginController::class, 'logout'])
    ->name('logout');

Route::post('account/password/verify-otp', [PasswordOtpController::class, 'confirm'])
    ->middleware(['auth', 'throttle:5,1'])
    ->name('account.password.verify_otp');


/*
|--------------------------------------------------------------------------
| CONTACT
|--------------------------------------------------------------------------
*/

Route::get('contact', [ContactController::class, 'contact'])
    ->name('contact');

Route::post('send-contact-data', [ContactController::class, 'contactStore'])
    ->name('send-contact-data');

Route::get('details/{url}', [HomeController::class, 'getPageData'])
    ->name('get-page-details');


/*
|--------------------------------------------------------------------------
| HOME PAGE ROUTES
|--------------------------------------------------------------------------
*/

Route::get('search-jobs', [HomeController::class, 'searchJobs'])
    ->name('search-jobs');

Route::get('/get-job-by-country', [AjaxSearchController::class, 'index']);

Route::get('/get-jobs/{name}', [AjaxSearchController::class, 'show']);


/*
|--------------------------------------------------------------------------
| AUTHENTICATED DASHBOARDS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/jobseeker/dashboard', [JobSeekerDashboardController::class, 'dashboard'])
        ->name('jobseeker.dashboard');

});


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin_user'])->group(function () {

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'dashboard'])
        ->name('admin.dashboard');


    Route::group(['prefix' => 'dashboard'], function () {

        Route::get('user-management', [DashboardController::class, 'index'])
            ->name('user-management.index');

        Route::get('user-permission', [DashboardController::class, 'userPermission'])
            ->name('user-permission.index');

        Route::get('user/create', [DashboardController::class, 'userCreate'])
            ->name('user.create');

        Route::post('user/store', [DashboardController::class, 'userStore'])
            ->name('user.store');

        Route::get('user/{id}/edit', [DashboardController::class, 'userEdit'])
            ->name('user.edit');

        Route::put('user/{id}', [DashboardController::class, 'userRoleUpdate'])
            ->name('user.update');


        Route::get('role/create', [RoleController::class, 'create'])
            ->name('role.create');

        Route::post('role', [RoleController::class, 'store'])
            ->name('role.store');

        Route::get('role', [RoleController::class, 'index'])
            ->name('role.index');

        Route::get('role/{role}/edit', [RoleController::class, 'edit'])
            ->name('role.edit');

        Route::get('role/show/{role}', [RoleController::class, 'show'])
            ->name('role.show');

        Route::put('role/{role}', [RoleController::class, 'update'])
            ->name('role.update');

        Route::delete('role/{role}', [RoleController::class, 'destroy'])
            ->name('role.destroy');
    });


    Route::group(['prefix' => 'admin'], function () {

        /*
        |--------------------------------------------------------------------------
        | EMPLOYERS
        |--------------------------------------------------------------------------
        */

        Route::get('employer/list', [CompanyController::class, 'index'])
            ->name('employer.index');

        Route::post('employer/update', [CompanyController::class, 'update'])
            ->name('employer.update');


        /*
        |--------------------------------------------------------------------------
        | JOB SEEKERS
        |--------------------------------------------------------------------------
        */

        Route::get('active-job-seeker', [JobSeekerController::class, 'getActiveJobSeeker'])
            ->name('active.jobseeker');

        Route::get('inactive-job-seeker', [JobSeekerController::class, 'getInActiveJobSeeker'])
            ->name('inactive.jobseeker');

        Route::get('job-seeker/show/{id}', [JobSeekerController::class, 'viewJobSeeker'])
            ->name('jobseeker.show');

        Route::get('job-seeker/delete/{id}', [JobSeekerController::class, 'getInActiveJobSeeker'])
            ->name('jobseeker.destroy');

        Route::post('jobSeeker-status-update-by-admin', [JobSeekerController::class, 'jobSeekerStatusUpdate'])
            ->name('jobSeeker-status-update-by-admin');


        /*
        |--------------------------------------------------------------------------
        | JOBS
        |--------------------------------------------------------------------------
        */

        Route::get('active-job', [JobController::class, 'getActiveJob'])
            ->name('active.job');

        Route::get('inactive-job', [JobController::class, 'getInActiveJob'])
            ->name('inactive.job');

        Route::get('job/show/{id}', [JobController::class, 'viewJob'])
            ->name('job.show');

        Route::get('job/show-apply/{id}', [JobController::class, 'viewJobApplicants'])
            ->name('job.show-apply');

        Route::patch('jobapplication-status-update', [JobController::class, 'jobApplicationStatusUpdate'])
            ->name('jobapplication.update_status');

        Route::get('application-statuses', [ApplicationStatusDefinitionController::class, 'index'])
            ->name('admin.application-statuses.index');
        Route::post('application-statuses', [ApplicationStatusDefinitionController::class, 'store'])
            ->name('admin.application-statuses.store');
        Route::patch('application-statuses/{id}', [ApplicationStatusDefinitionController::class, 'update'])
            ->name('admin.application-statuses.update');

        Route::post('job-status-update-by-admin', [JobController::class, 'jobStatusUpdate'])
            ->name('job-status-update-by-admin');


        /*
        |--------------------------------------------------------------------------
        | HISTORY
        |--------------------------------------------------------------------------
        */

        Route::get('employer-history', [HistoryController::class, 'employerJobHistory'])
            ->name('employer-history');

        Route::get('company-jobs/{id}', [HistoryController::class, 'companyJobs'])
            ->name('company-jobs');

        Route::get('job-applied-users/{id}', [HistoryController::class, 'jobAppliedUsers'])
            ->name('job-applied-users');

        Route::get('employer-cv-downloads', [HistoryController::class, 'cvDownloads'])
            ->name('employer-cv-downloads');

        Route::get('jobseeker-history', [HistoryController::class, 'jobSeekerHistory'])
            ->name('jobseeker-history');

        Route::get('user-applied-jobs/{id}', [HistoryController::class, 'appliedJobs'])
            ->name('user-applied-jobs');


        Route::get('subscription', [PlanController::class, 'subscriptions'])
            ->name('admin.subscriptions');


        /*
        |--------------------------------------------------------------------------
        | UNIQUE CODES
        |--------------------------------------------------------------------------
        */

        Route::get('all/code/list', [JobCategoryController::class, 'allUniqueCodeList'])
            ->name('uniquecode.index');

        Route::get('code/request', [JobCategoryController::class, 'codeRequestList'])
            ->name('code-request');

        Route::patch('unique_codes/update_code', [JobCategoryController::class, 'updateCode'])
            ->name('admin.unique_codes.update_code');

    });


    /*
    |--------------------------------------------------------------------------
    | APPLICATION SETTINGS
    |--------------------------------------------------------------------------
    */

    Route::prefix('application-setting')->group(function () {

        Route::get('/setting', [SettingController::class, 'index'])
            ->name('setting');

        Route::get('/cache-clear', [SettingController::class, 'cachClear'])
            ->name('cache-clear');

        Route::get('/email-setting', [SettingController::class, 'emailSetting'])
            ->name('email-setting');

        Route::get('/send-test-email', [SettingController::class, 'emailTesting'])
            ->name('send-test-email');

        Route::post('/database-backup', [SettingController::class, 'databaseBackup'])
            ->name('database-backup');

        Route::get('/user-logs', [SettingController::class, 'userLog'])
            ->name('user-logs');

        Route::get('/get-log-data', [SettingController::class, 'getLogData'])
            ->name('get-log-data');

        Route::post('/log-search', [SettingController::class, 'logSearch'])
            ->name('log-search');

        Route::post('/setting-update', [SettingController::class, 'update'])
            ->name('setting-update');

        Route::post('/email-setting-update', [SettingController::class, 'emailSettingUpdate'])
            ->name('email-setting-update');

        Route::get('application-cache-clear', [SettingController::class, 'cachClear'])
            ->name('application-cache-clear');

        Route::get('admin-profile-setting', [AdminProfileController::class, 'profileSetting'])
            ->name('admin-profile-setting');

        Route::post('profile-update', [AdminProfileController::class, 'updateProfile'])
            ->name('profile-update');

        Route::get('admin-change-password', [AdminProfileController::class, 'changePassword'])
            ->name('admin-change-password');

        Route::post('update-admin-password', [AdminProfileController::class, 'updateAdminPassword'])
            ->name('update-admin-password');
    });


    /*
    |--------------------------------------------------------------------------
    | ADMIN DASHBOARD DATA
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin-dashboard')->group(function () {

        Route::get('get-dashboard-data', [AdminDashboardController::class, 'getDashboardData'])
            ->name('get-dashboard-data');

        Route::get('users-chart-data', [AdminDashboardController::class, 'getUsersChartData'])
            ->name('users-chart-data');

        Route::get('jobs-chart-data', [AdminDashboardController::class, 'getJobChartData'])
            ->name('jobs-chart-data');

        Route::get('job-category-chart-data', [AdminDashboardController::class, 'getJobCategoryData'])
            ->name('job-category-chart-data');

        Route::get('get-recent-employer', [AdminDashboardController::class, 'getRecentEmp'])
            ->name('get-recent-employer');

    });


    /*
    |--------------------------------------------------------------------------
    | CONTACTS
    |--------------------------------------------------------------------------
    */

    Route::prefix('contacts')->group(function () {

        Route::get('send-mass-mail', [MassmailController::class, 'massMail'])
            ->name('send-mass-mail');

        Route::post('send-mail', [MassmailController::class, 'massMailSend'])
            ->name('send-mail');

        Route::get('contact-message', [MassmailController::class, 'contactMessage'])
            ->name('contact-message');

    });


    /*
    |--------------------------------------------------------------------------
    | FRONT PAGES
    |--------------------------------------------------------------------------
    */

    Route::prefix('front')->group(function () {

        Route::resource('pages', FrontPageController::class);

        Route::post('text-editor-file-upload', [FrontPageController::class, 'uploadEditorFile'])
            ->name('text-editor-file-upload');

    });

});


/*
|--------------------------------------------------------------------------
| EMPLOYER AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'employer', 'middleware' => 'auth'], function () {

    Route::get('dashboard', [EmployeerController::class, 'index'])
        ->name('employer.dashboard');

    Route::get('job-analytics-chart-data', [JobAnalyticsController::class, 'getAnalyticsJobData'])
        ->name('job-analytics-chart-data');

    Route::get('manage-jobs', [ManageJobController::class, 'managePage'])
        ->name('manage.job');

    Route::get('jobs/application/view/{id}', [ManageJobController::class, 'viewApplication'])
        ->name('view.application');

    Route::get('jobs/resume/view/{user_id}/{applicationId}', [ManageJobController::class, 'getResume'])
        ->name('view.resume');

    Route::post('short-list-cv', [ManageJobController::class, 'shortListCv'])
        ->name('shortList.cv');

    Route::get('profile/update/{id}', [EmployerInformationController::class, 'create'])
        ->name('employer-profile-update.page');

    Route::put('update/profile/{id}', [EmployerInformationController::class, 'store'])
        ->name('employer-profile.update');

    Route::get('change/password', [EmployeerController::class, 'changePass'])
        ->name('change.pass');

    Route::post('update/password', [EmployeerController::class, 'updatePassword'])
        ->name('update.pass');

    Route::post('download-pdfs', [CvBankController::class, 'downloadPDFs'])
        ->name('download-pdfs');

    Route::get('subscription', [EmployeerController::class, 'subscriptions'])
        ->name('subscriptions');

    Route::get('subscriptions', [EmployeerController::class, 'subscription'])
        ->name('subscriptions.index');

    Route::get('stripe-payment/{id}', [StripePaymentController::class, 'stripe'])
        ->name('stripe.payment');

    Route::post('stripe-post/{id}', [StripePaymentController::class, 'stripePost'])
        ->name('stripe.post');

    Route::get('company-information', [CompanyController::class, 'companyInformation'])
        ->name('company.information');

    Route::post('company/information/store', [CompanyController::class, 'store'])
        ->name('company.information.store');

});


Route::get('auto-email-receive/{id}', [EmployeerController::class, 'autoReceiveEmail']);


/*
|--------------------------------------------------------------------------
| GENERAL AUTHENTICATED JOB ROUTES
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'auth'], function () {

    Route::resource('jobs', JobController::class)
        ->except('show', 'index');

    Route::get('archive-jobs', [JobController::class, 'archiveJob'])
        ->name('archive-jobs.index');

    Route::post('restore-jobs/{id}', [JobController::class, 'restoreJob'])
        ->name('archive-jobs.restore');

    Route::resource('cv-bank', EmployerCvBankController::class)
        ->only('index');

    Route::get('download-cv', [EmployerCvBankController::class, 'cvDownload'])
        ->name('download..cv');

    Route::get('pay-message/{id}/{count}', [EmployerCvBankController::class, 'payMessage'])
        ->name('pay.message');

    Route::resource('job-analytics', JobAnalyticsController::class)
        ->only('index');

    Route::get('job-details/{slug}', [JobController::class, 'show'])
        ->name('job-details.show');

    Route::get('get-job-skills/{id}', [JobController::class, 'jobSkills']);

});


/*
|--------------------------------------------------------------------------
| JOB SEEKER AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('job_seeker')->middleware('auth')->group(function () {

    Route::get('applications/{application}/timeline', [ApplicationTimelineController::class, 'show'])
        ->name('jobseeker.application.timeline');

    Route::get('view-jobseeker-profile', [JobSeekerProfileShowController::class, 'show'])
        ->name('view-jobseeker-profile');

    Route::get('jobseeker-email-resume', [EmailResumeController::class, 'index'])
        ->name('jobseeker-email-resume');

    Route::post('send-resume', [EmailResumeController::class, 'sendResume'])
        ->name('send-resume');

    Route::get('notification-detail/{id}', [NotificationController::class, 'details'])
        ->name('notification-detail');

    Route::get('jobseeker-resume-views', [JobSeekerProfileController::class, 'resumeViewd'])
        ->name('jobseeker-resume-views');

    Route::get('find-jobs', [JobApplicationController::class, 'findJobs'])
        ->name('find-jobs');

    Route::get('get-application-form/{id}', [JobApplicationController::class, 'getApplicationForm'])
        ->name('get-application-form');

    Route::post('save-job-application', [JobApplicationController::class, 'savePost'])
        ->name('save-job-application');

    Route::get('job-details/{id}', [JobApplicationController::class, 'jobDetails'])
        ->name('job-details');

    Route::get('get-all-jobs', [JobApplicationController::class, 'getAllJobs'])
        ->name('get-all-jobs');

    Route::post('job-search', [JobApplicationController::class, 'search'])
        ->name('job-search');

    Route::get('applied-jobs', [JobApplicationController::class, 'appliedJob'])
        ->name('applied-jobs');

    Route::get('get-all-applied-jobs', [JobApplicationController::class, 'getAppliedJob'])
        ->name('get-all-applied-jobs');

    Route::get('shorlist-jobs', [JobApplicationController::class, 'shortListedJob'])
        ->name('shorlist-jobs');

    Route::get('get-all-shortlist-jobs', [JobApplicationController::class, 'getShortlistedJob'])
        ->name('get-all-shortlist-jobs');

    Route::get('/generate-pdf', [PdfController::class, 'generatePDF'])
        ->name('jobseeker-generate-pdf');

    Route::get('/dashboard', [JobSeekerDashboardController::class, 'dashboard'])
        ->name('job_seeker.dashboard');

    Route::post('/upload/profile/pictute', [JobSeekerDashboardController::class, 'uploadPhoto'])
        ->name('/upload/profile/pictute');

    Route::get('job-seeker-profile', [JobSeekerProfileController::class, 'profile'])
        ->name('job-seeker-profile');

    Route::delete('/delete-account', [JobSeekerProfileController::class, 'delete'])
        ->name('delete.account');

    Route::get('my-library', [JobSeekerProfileController::class, 'library'])
        ->name('library');

    Route::get('import-cv', [JobseekerImportController::class, 'inport'])
        ->name('import-cv');

    Route::post('store-import-cv', [JobseekerImportController::class, 'store'])
        ->name('store-import-cv');


    /*
    |--------------------------------------------------------------------------
    | COVER LETTER
    |--------------------------------------------------------------------------
    */

    Route::get('add-cover-letter', [JobSeekerCoverletterController::class, 'coverLetter'])
        ->name('add-cover-letter');

    Route::post('save-person-details', [JobSeekerCoverletterController::class, 'savePersonInfo'])
        ->name('save-person-details');

    Route::post('save-person-contact-details', [JobSeekerCoverletterController::class, 'saveContactInfo'])
        ->name('save-person-contact-details');

    Route::post('save-person-content-details', [JobSeekerCoverletterController::class, 'saveContentInfo'])
        ->name('save-person-content-details');

    Route::post('save-person-closing-details', [JobSeekerCoverletterController::class, 'saveClosingInfo'])
        ->name('save-person-closing-details');

    Route::get('/export-to-word', [WordController::class, 'exportToWord'])
        ->name('download-cover-letter');

    Route::get('/download-pdf-cover-letter', [WordController::class, 'exportToPDF'])
        ->name('download-pdf-cover-letter');


    /*
    |--------------------------------------------------------------------------
    | RESUME
    |--------------------------------------------------------------------------
    */

    Route::get('make-resume', [JobseekerResumeController::class, 'makeResume'])
        ->name('make-resume');

    Route::get('get-resume-template', [JobseekerResumeController::class, 'getResume'])
        ->name('get-resume-template');

    Route::get('get-job-seeker-profile-data', [JobSeekerProfileController::class, 'profileData'])
        ->name('get-job-seeker-profile-data');

    Route::get('jobseeker-profile-information-data/{id}', [JobSeekerProfileController::class, 'profileEdit'])
        ->name('jobseeker-profile-information-data');

    Route::post('save-personal-information', [JobSeekerProfileController::class, 'saveProfileInfo'])
        ->name('save-personal-information');

    Route::get('jobseeker-contact-information-data/{id}', [JobSeekerProfileController::class, 'contactEdit'])
        ->name('jobseeker-contact-information-data');

    Route::get('jobseeker-address-information-data/{id}', [JobSeekerProfileController::class, 'addressEdit'])
        ->name('jobseeker-address-information-data');


    /*
    |--------------------------------------------------------------------------
    | WORK EXPERIENCE
    |--------------------------------------------------------------------------
    */

    Route::get('create-work-experience', [JobSeekerExperienceController::class, 'create'])
        ->name('create-work-experience');

    Route::get('edit-work-experience/{id}', [JobSeekerExperienceController::class, 'edit'])
        ->name('edit-work-experience');

    Route::post('save-work-experience-information', [JobSeekerExperienceController::class, 'store'])
        ->name('save-work-experience-information');

    Route::put('update-work-experience/{id}', [JobSeekerExperienceController::class, 'update'])
        ->name('update-work-experience');

    Route::delete('delete-work-experience/{id}', [JobSeekerExperienceController::class, 'delete'])
        ->name('delete-work-experience');


    /*
    |--------------------------------------------------------------------------
    | TRAINING
    |--------------------------------------------------------------------------
    */

    Route::delete('delete-training/{id}', [JobSeekerTrainingController::class, 'delete'])
        ->name('delete-training');

    Route::get('add-new-training', [JobSeekerTrainingController::class, 'create'])
        ->name('add-training');

    Route::get('edit-training/{id}', [JobSeekerTrainingController::class, 'edit'])
        ->name('edit-training');

    Route::post('store-training', [JobSeekerTrainingController::class, 'store'])
        ->name('store-training');

    Route::put('update-training/{id}', [JobSeekerTrainingController::class, 'update'])
        ->name('update-training');


    /*
    |--------------------------------------------------------------------------
    | EDUCATION
    |--------------------------------------------------------------------------
    */

    Route::delete('delete-education/{id}', [JobSeekerEducationController::class, 'delete'])
        ->name('delete-education');

    Route::get('add-new-education', [JobSeekerEducationController::class, 'create'])
        ->name('add-new-education');

    Route::get('edit-education/{id}', [JobSeekerEducationController::class, 'edit'])
        ->name('edit-education');

    Route::post('store-education', [JobSeekerEducationController::class, 'store'])
        ->name('store-education');

    Route::put('update-education/{id}', [JobSeekerEducationController::class, 'update'])
        ->name('update-education');


    /*
    |--------------------------------------------------------------------------
    | SKILLS
    |--------------------------------------------------------------------------
    */

    Route::delete('delete-skill/{id}', [JobSeekerSkillController::class, 'delete'])
        ->name('delete-skill');

    Route::get('add-new-skill', [JobSeekerSkillController::class, 'create'])
        ->name('add-new-skill');

    Route::get('edit-skill/{id}', [JobSeekerSkillController::class, 'edit'])
        ->name('edit-skill');

    Route::post('store-skill', [JobSeekerSkillController::class, 'store'])
        ->name('store-skill');

    Route::put('update-skill/{id}', [JobSeekerSkillController::class, 'update'])
        ->name('update-skill');


    /*
    |--------------------------------------------------------------------------
    | LANGUAGE
    |--------------------------------------------------------------------------
    */

    Route::delete('delete-language/{id}', [JobSeekerLanguageController::class, 'delete'])
        ->name('delete-language');

    Route::get('edit-language/{id}', [JobSeekerLanguageController::class, 'edit'])
        ->name('edit-language');

    Route::get('add-new-language', [JobSeekerLanguageController::class, 'create'])
        ->name('add-new-language');

    Route::post('store-language', [JobSeekerLanguageController::class, 'store'])
        ->name('store-language');

    Route::put('update-language/{id}', [JobSeekerLanguageController::class, 'update'])
        ->name('update-language');


    /*
    |--------------------------------------------------------------------------
    | PASSWORD
    |--------------------------------------------------------------------------
    */

    Route::get('jobseeker-change-password', [JobSeekerProfileSettingController::class, 'changePassword'])
        ->name('jobseeker-change-password');

    Route::post('password-update', [JobSeekerProfileSettingController::class, 'updatePassword'])
        ->name('jobseeker-password-update');


    /*
    |--------------------------------------------------------------------------
    | CERTIFICATES
    |--------------------------------------------------------------------------
    */

    Route::get('jobseeker-certificate', [JobseekerImportController::class, 'certificate'])
        ->name('jobseeker-certificate');

    Route::post('store-jobseeer-certificate', [JobseekerImportController::class, 'storeCertificate'])
        ->name('store-jobseeer-certificate');

    Route::delete('delete-jobseeer-certificate/{id}', [JobseekerImportController::class, 'certificateDelete'])
        ->name('delete-jobseeer-certificate');


    /*
    |--------------------------------------------------------------------------
    | DOCUMENT UPLOAD
    |--------------------------------------------------------------------------
    */

    Route::get('jobseeker-upload', [JobseekerImportController::class, 'jobseekerUpload'])
        ->name('jobseeker-upload');

    Route::post('jobseeker-upload-document', [JobseekerImportController::class, 'jobseekerDocUpload'])
        ->name('jobseeker-upload-document');

    Route::delete('delete-upload-document/{id}', [JobseekerImportController::class, 'docsDelete'])
        ->name('delete-upload-document');

    Route::get('get-degree-name/{id}', [JobSeekerEducationController::class, 'getGegreeName'])
        ->name('get-degree-name');

    Route::delete('uploaded-cv-delete/{id}', [JobseekerImportController::class, 'cvDelete'])
        ->name('uploaded-cv-delete');


    /*
    |--------------------------------------------------------------------------
    | UNIQUE CODES
    |--------------------------------------------------------------------------
    */

    Route::get('unique_codes', [UniqueCodeController::class, 'index'])
        ->name('unique_codes');

    Route::post('request-code', [UniqueCodeController::class, 'create'])
        ->name('request-code');

});


/*
|--------------------------------------------------------------------------
| AXIOS
|--------------------------------------------------------------------------
*/

Route::get('degree-name/{id}', [DegreeNameController::class, 'DegreeName']);


/*
|--------------------------------------------------------------------------
| USER PROFILE
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});


/*
|--------------------------------------------------------------------------
| LANGUAGE
|--------------------------------------------------------------------------
*/

Route::get('lang/home', [LangController::class, 'index']);

Route::get('lang/change', [LangController::class, 'change'])
    ->name('changeLang');


/*
|--------------------------------------------------------------------------
| SOCIAL LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/auth/{provider}/redirect', [SocialiteLoginController::class, 'redirect'])
    ->name('redirect');

Route::get('/auth/{provider}/callback', [SocialiteLoginController::class, 'callback'])
    ->name('callback');


/*
|--------------------------------------------------------------------------
| CV
|--------------------------------------------------------------------------
*/

Route::get('job-seeker/cv-view/{id}', [JobSeekerController::class, 'cvView'])
    ->name('cv.view');


/*
|--------------------------------------------------------------------------
| COUNTRY JOB COUNTS
|--------------------------------------------------------------------------
*/

Route::get('/job-counts-by-country', [HomeController::class, 'getJobCountsByCountry']);

/*
|--------------------------------------------------------------------------
| NEW PRIVATE DOCUMENT WORKSPACE (independent of legacy public CV library)
|--------------------------------------------------------------------------
| These routes use web authentication, CSRF forms and security headers.
| Actual access is checked against account type, ownership and permission.
*/
Route::middleware(['auth', 'private.document.headers'])
    ->prefix('job_seeker/private-documents')
    ->group(function () {
        Route::get('/', [ApplicantDocumentWebController::class, 'index'])
            ->name('jobseeker.private-documents.index');
        Route::post('/', [ApplicantDocumentWebController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('jobseeker.private-documents.store');
        Route::get('/{id}', [ApplicantDocumentWebController::class, 'show'])
            ->whereNumber('id')->name('jobseeker.private-documents.show');
        Route::post('/{id}/revisions', [ApplicantDocumentWebController::class, 'revise'])
            ->whereNumber('id')->middleware('throttle:10,1')
            ->name('jobseeker.private-documents.revise');
        Route::get('/{id}/versions/{version}', [ApplicantDocumentWebController::class, 'download'])
            ->whereNumber('id')->whereNumber('version')
            ->name('jobseeker.private-documents.download');
    });

Route::middleware(['auth', 'private.document.headers'])
    ->prefix('admin/private-documents')
    ->group(function () {
        Route::get('/', [AdminDocumentWebController::class, 'index'])
            ->name('admin.private-documents.index');
        Route::get('/{id}', [AdminDocumentWebController::class, 'show'])
            ->whereNumber('id')->name('admin.private-documents.show');
        Route::post('/{id}/review', [AdminDocumentWebController::class, 'decide'])
            ->whereNumber('id')->middleware('throttle:30,1')
            ->name('admin.private-documents.decide');
        Route::get('/{id}/versions/{version}', [AdminDocumentWebController::class, 'download'])
            ->whereNumber('id')->whereNumber('version')
            ->name('admin.private-documents.download');
    });


/*
|----------------------------------------------------------------------
| ONLINE INTERVIEW SCHEDULING (no recording or native video)
|----------------------------------------------------------------------
| Meetings are secure HTTPS links issued by an approved organizer.
| Session-authenticated views use no third-party scripts.
*/
Route::middleware(['auth', 'private.document.headers'])->group(function () {
    Route::get('job_seeker/interviews', [InterviewController::class, 'applicantIndex'])
        ->name('interviews.applicant.index');
    Route::get('job_seeker/interviews/{application}', [InterviewController::class, 'applicantShow'])
        ->whereNumber('application')->name('interviews.applicant.show');
    Route::post('job_seeker/interviews/{application}/reply', [InterviewController::class, 'act'])
        ->whereNumber('application')->middleware('throttle:10,1')
        ->name('interviews.applicant.reply');

    Route::get('interviews/manage', [InterviewController::class, 'managerIndex'])
        ->name('interviews.manage.index');
    Route::get('interviews/manage/{application}', [InterviewController::class, 'managerShow'])
        ->whereNumber('application')->name('interviews.manage.show');
    Route::post('interviews/manage/{application}/schedule', [InterviewController::class, 'schedule'])
        ->whereNumber('application')->middleware('throttle:10,1')
        ->name('interviews.manage.schedule');
    Route::post('interviews/manage/{application}/action', [InterviewController::class, 'act'])
        ->whereNumber('application')->middleware('throttle:10,1')
        ->name('interviews.manage.action');
});


/*
|----------------------------------------------------------------------
| JWTC VISUAL SMTP EMAIL STUDIO (isolated from legacy Mass Mail)
|----------------------------------------------------------------------
| Feature flag + admin type + dedicated permission enforced in controller.
| No credentials or server secrets are written through these endpoints.
*/
Route::middleware(['auth', 'email.studio.headers'])
    ->prefix('admin/email-studio')
    ->group(function () {
        Route::get('/', [EmailStudioController::class, 'index'])->name('email-studio.index');
        Route::get('/new', [EmailStudioController::class, 'edit'])->name('email-studio.create');
        Route::post('/templates', [EmailStudioController::class, 'save'])
            ->middleware('throttle:10,1')->name('email-studio.store');
        Route::get('/templates/{id}', [EmailStudioController::class, 'edit'])
            ->whereNumber('id')->name('email-studio.edit');
        Route::patch('/templates/{id}', [EmailStudioController::class, 'save'])
            ->whereNumber('id')->middleware('throttle:10,1')
            ->name('email-studio.update');
        Route::get('/templates/{id}/images/{slot}', [EmailStudioController::class, 'image'])
            ->whereNumber('id')->where('slot', 'top|bottom_left|bottom_right')
            ->name('email-studio.image');
        Route::post('/templates/{id}/send', [EmailStudioController::class, 'send'])
            ->whereNumber('id')->middleware(['throttle:5,1', 'throttle:25,60'])
            ->name('email-studio.send');

        Route::post('/templates/{id}/duplicate', [EmailStudioController::class, 'duplicate'])
            ->whereNumber('id')->middleware('throttle:10,1')->name('email-studio.duplicate');
        Route::delete('/templates/{id}', [EmailStudioController::class, 'destroy'])
            ->whereNumber('id')->name('email-studio.destroy');
        Route::post('/templates/{id}/visual-pages', [EmailStudioController::class, 'importVisual'])
            ->whereNumber('id')->middleware('throttle:3,1')->name('email-studio.visual.import');
        Route::delete('/templates/{id}/visual-pages', [EmailStudioController::class, 'removeVisual'])
            ->whereNumber('id')->name('email-studio.visual.remove');
        Route::get('/templates/{id}/visual-pages/{page}', [EmailStudioController::class, 'visualPage'])
            ->whereNumber('id')->whereNumber('page')->name('email-studio.visual.page');

        Route::post('/smtp-profiles', [EmailStudioSmtpController::class, 'save'])
            ->middleware('throttle:8,1')->name('email-studio.smtp.store');
        Route::patch('/smtp-profiles/{id}', [EmailStudioSmtpController::class, 'save'])
            ->whereNumber('id')->middleware('throttle:8,1')->name('email-studio.smtp.update');
        Route::delete('/smtp-profiles/{id}', [EmailStudioSmtpController::class, 'remove'])
            ->whereNumber('id')->name('email-studio.smtp.destroy');
        Route::post('/smtp-profiles/{id}/default', [EmailStudioSmtpController::class, 'makeDefault'])
            ->whereNumber('id')->name('email-studio.smtp.default');
        Route::post('/smtp-test-environment', [EmailStudioSmtpController::class, 'testEnvironment'])
            ->middleware('throttle:3,1')->name('email-studio.smtp.test-environment');
        Route::post('/smtp-profiles/{id}/test', [EmailStudioSmtpController::class, 'test'])
            ->whereNumber('id')->middleware('throttle:3,1')->name('email-studio.smtp.test');
    });


/*
|--------------------------------------------------------------------------
| PRIVATE APPLICATION CHAT (no additional WebSocket service required)
|--------------------------------------------------------------------------
| Session/CSRF web routes, account scoping and private response headers.
*/
Route::middleware(['auth', 'application.chat.headers'])
    ->prefix('messages')->group(function () {
        Route::get('/', [ApplicationChatController::class, 'index'])
            ->name('application-chat.index');
        Route::post('/applications/{application}', [ApplicationChatController::class, 'open'])
            ->whereNumber('application')->middleware('throttle:8,1')
            ->name('application-chat.open');
        Route::get('/threads/{id}', [ApplicationChatController::class, 'thread'])
            ->whereNumber('id')->name('application-chat.thread');
        Route::get('/threads/{id}/poll', [ApplicationChatController::class, 'poll'])
            ->whereNumber('id')->middleware('throttle:30,1')
            ->name('application-chat.poll');
        Route::post('/threads/{id}/messages', [ApplicationChatController::class, 'send'])
            ->whereNumber('id')->middleware(['throttle:12,1', 'throttle:100,60'])
            ->name('application-chat.send');
        Route::post('/threads/{id}/read', [ApplicationChatController::class, 'read'])
            ->whereNumber('id')->middleware('throttle:30,1')
            ->name('application-chat.read');
        Route::post('/threads/{id}/status', [ApplicationChatController::class, 'status'])
            ->whereNumber('id')->middleware('throttle:12,1')
            ->name('application-chat.status');
    });
