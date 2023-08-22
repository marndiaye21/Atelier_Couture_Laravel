<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProviderController;
use App\Models\Category;
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
Route::get("/categories/search/{label}", [CategoryController::class, "search"]);
Route::delete("/categories/delete", [CategoryController::class, "destroy"]);

Route::apiResource("/providers", ProviderController::class);
Route::get("/providers/search/{searchValue}", [ProviderController::class, "search"]);

Route::apiResource("/articles", ArticleController::class);
Route::get("/articles/search/{search_value}", [ArticleController::class, 'searchArticle']);

