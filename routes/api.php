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
    Route::get("datasets/all", [DatasetsController::class, "all"]);
    Route::get("datasets/debtors", [DatasetsController::class, "debtors"]);
    Route::get("datasets/reminders", [DatasetsController::class, "reminders"]);
});

