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
Route::post("/{gameId}/pick-up", "PostPickUp");
Route::post("/{gameId}/drop/{locationId}", "PostDrop");
Route::post("/{gameId}/use/{itemId}", "PostUse");
Route::post("/{gameId}/eat/{itemId}", "PostEat");
Route::post("/{gameId}/make", "PostMake");
Route::post("/{gameId}/transfer/{containerId}", "PostTransfer");
Route::post("/{gameId}/place", "PostPlace");
Route::post("/{gameId}/open", "PostOpen");
Route::post("/{gameId}/close", "PostClose");
Route::post("/{gameId}/put-in", "PostPutIn");
Route::post("/{gameId}/use-with", "PostUseWith");
