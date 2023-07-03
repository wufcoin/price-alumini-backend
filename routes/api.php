<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CronController;

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

Route::get('/', function () {
    return response()->json("Welcome to Price Alumni");
});

Route::get('/configurations', [CommonController::class, 'getConfigurations']);
Route::get('/schools', [CommonController::class, 'getSchools']);
Route::get('/military_codes/{page}', [CommonController::class, 'getMilitaryCodes']);

Route::get('/getRanks/{id}', [UserController::class, 'getRanks']);
Route::post('feedback', [CommonController::class, 'saveFeedback']);
Route::post('/update_devicetoken', [UserController::class, 'update_devicetoken']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user/{id}', [UserController::class, 'userDetails']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::get('/getMatch/{id}', [UserController::class, 'getMatch']);
    Route::get('/usersByTraitsCount/{id}', [UserController::class, 'getUsersByTraitsCounts']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('update-profile', [AuthController::class, 'updateProfile']);
    Route::get('auth/me', [AuthController::class, 'getMyProfile']);
    Route::get('users', [UserController::class, 'getUsers']);
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('linkedinsignin', [AuthController::class, 'linkedinsignin']);
    Route::post('signin', [AuthController::class, 'signin']);
    Route::post('getuserbyemail', [AuthController::class, 'getuserbyemail']);
    Route::post('register', [AuthController::class, 'register']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//cron job
Route::get('/cron_send_noti', [CronController::class, 'cron_send_noti']);

Route::get('cities', [CommonController::class, 'getCities']);
Route::get('countries', [CommonController::class, 'getCountries']);
Route::get('degrees', [CommonController::class, 'getDegrees']);
Route::get('grad_levels', [CommonController::class, 'getGradLevels']);
Route::get('ibc_companies', [CommonController::class, 'getIbcCompanies']);
Route::get('student_orgs', [CommonController::class, 'getStudentOrgs']);
Route::get('associations', [CommonController::class, 'getAssociations']);
Route::get('industries', [CommonController::class, 'getIndustries']);
Route::get('hobbies', [CommonController::class, 'getHobbies']);
Route::get('job_titles', [CommonController::class, 'getJobTitles']);
Route::get('military_branches', [CommonController::class, 'getMilitaryBranches']);
Route::get('skills', [CommonController::class, 'getSkills']);

Route::group(['prefix' => 'admin', 'middleware' => ['admin']], function () {
    Route::post('city', [AdminController::class, 'createCity']);
    Route::put('city/{id}', [AdminController::class, 'updateCity']);

    Route::post('country', [AdminController::class, 'createCountry']);

    Route::post('school', [AdminController::class, 'createSchool']);
});
