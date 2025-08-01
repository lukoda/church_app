<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Resources\AnnouncementController;
use App\Http\Controllers\Resources\CardPledgeController;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth', 'middleware' => 'guest'], function () {
    // dinominations
    Route::get('/dinominations', [RegisterController::class, 'index']);
    Route::get('/dinomination/churches/{id}', [RegisterController::class, 'church']);

    // address routes
    Route::get('/regions', [RegisterController::class, 'region']);
    Route::get('/districts/{id}', [RegisterController::class, 'district']);
    Route::get('/wards/{id}', [RegisterController::class, 'ward']);

    // search route
    Route::get('/churches/search/', [RegisterController::class, 'search']);

    // authentication route
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/register/membership', [RegisterController::class, 'registerChurchMembership']);
    Route::post('/login', [LoginController::class, 'login']);
});

// Resource protected
Route::group(['prefix' => 'resource', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/announcement', [AnnouncementController::class, 'announcement']);
    Route::post('/card_pledging', [CardPledgeController::class, 'card_pledge']);
    Route::get('/cards', [CardPledgeController::class, 'cards']);
    Route::get('/card_pledge/{card_id}/card_number/{card_no}', [CardPledgeController::class, 'get_card_pledge']);
    Route::get('/offerings/card/{card_no}/type/{card_type}', [CardPledgeController::class, 'get_card_offerings']);
});

// protected routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->churchMember;
});
