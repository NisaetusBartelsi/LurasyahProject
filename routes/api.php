<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DomisiliController;
use App\Http\Controllers\ForgotPasswordAPIController;
use App\Http\Controllers\CreateController;
use App\Http\Controllers\DeleteController;
use App\Http\Controllers\ReadController;
use App\Http\Controllers\UpdateController;

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

Route::post('/auth-verification', [AuthController::class, 'AuthVerification']);
Route::post('/auth-logout', [AuthController::class, 'AuthLogout']);
Route::post('/auth-login', [AuthController::class, 'AuthLogin']);
Route::post('/auth-registration', [AuthController::class, 'AuthRegistration']);
Route::get('/auth/google', [AuthController::class, 'LinkGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'BackHome']);
Route::post('/auth-resend-otp', [AuthController::class, 'ResendOTP']);
Route::post('/email-forgot-pass', [ForgotPasswordAPIController::class, 'SendEmailForgotPass']);
Route::post('/form-reset-password', [ForgotPasswordAPIController::class, 'ChangePassword']);
Route::post('/create-store', [CreateController::class, 'CreateStore']);
Route::post('/create-like/{username}/{id}', [CreateController::class, 'CreateLike']);
Route::post('/create-bookmark/{username}/{id}', [CreateController::class, 'CreateBookmark']);
Route::post('/create-comment/{username}/{id}', [CreateController::class, 'CreateComment']);
Route::post('/read-search', [ReadController::class, 'ReadSearch']);
Route::post('/read-detail/{username}/{id}', [ReadController::class, 'ReadDetail']);
Route::post('/read-profile/{username}', [ReadController::class, 'ReadProfile']);
Route::post('/read-all', [ReadController::class, 'ReadAll']);
Route::post('/update-role', [UpdateController::class, 'UpdateRole']);
Route::post('/update-bio-profile', [UpdateController::class, 'UpdateBioProfile']);
Route::post('/delete-profile/{username}/{id}', [DeleteController::class, 'DeleteProfile']);
Route::post('/delete-company/{username}/{id}', [DeleteController::class, 'DeleteCompany']);
Route::get('/province', [DomisiliController::class, 'province']);
Route::get('/regency', [DomisiliController::class, 'regency']);
Route::get('/district', [DomisiliController::class, 'district']);
Route::get('/village', [DomisiliController::class, 'village']);
