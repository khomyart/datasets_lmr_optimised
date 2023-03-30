<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DatasetsController;

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

Route::post("register", [UserController::class, "create"]);
Route::post("login", [UserController::class, "authenticate"]);
Route::post("logout", [UserController::class, "logout"]);

Route::middleware('api.authentication')->group(function () {
    Route::get("datasets/receive", [DatasetsController::class, "receive"]);
    Route::get("datasets/read", [DatasetsController::class, "read"]);
    Route::get("datasets/sendMail", [DatasetsController::class, "sendMail"]);
});

