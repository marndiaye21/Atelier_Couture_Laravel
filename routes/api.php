<?php

use App\Http\Controllers\ArticleConfectionController;
use App\Http\Controllers\ArticleVenteController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProviderController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource("/categories", CategoryController::class);
Route::get("/categories/search/{searchValue}", [CategoryController::class, "search"]);
Route::delete("/categories/delete", [CategoryController::class, "destroy"]);

Route::apiResource("/providers", ProviderController::class);
Route::get("/providers/search/{searchValue}", [ProviderController::class, "search"]);

Route::apiResource("/articles", ArticleConfectionController::class);
Route::get("/articles/search/{searchValue}", [ArticleConfectionController::class, 'searchArticle']);

Route::apiResource("/articles_vente", ArticleVenteController::class);


