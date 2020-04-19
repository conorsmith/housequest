<?php

use Illuminate\Support\Facades\Route;

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

Route::get("/", function () {
    return view("welcome");
});

Route::get('/{gameId}', "GetGame");

Route::post("/new-game", "PostNewGame");

Route::post("/{gameId}/go/{locationId}", "PostGo");
Route::post("/{gameId}/look-at/{itemId}", "PostLookAt");
Route::post("/{gameId}/pick-up/{itemId}", "PostPickUp");
Route::post("/{gameId}/drop/{itemId}/{locationId}", "PostDrop");
Route::post("/{gameId}/use/{itemId}", "PostUse");
Route::post("/{gameId}/eat/{itemId}", "PostEat");
Route::post("/{gameId}/make", "PostMake");
Route::post("/{gameId}/transfer/{containerId}", "PostTransfer");
