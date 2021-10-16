<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ForgotPasswordController;

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

Route::post('create-new-account', [AuthController::class, 'register']);
Route::post('verify-token', [AuthController::class, 'verify']);
Route::post('login-account', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('change_user_password', [HomeController::class, 'userChangePassword']);
    Route::post('update_user_email', [HomeController::class, 'userUpdateEmail']);
    Route::post('update_username', [HomeController::class, 'userUpdateUsername']);
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('password/reset', [ResetPasswordController::class, 'reset']);

});
