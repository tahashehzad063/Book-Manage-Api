<?php

use App\Http\Controllers\Api\AutherController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\API\LibraryController;
use App\Http\Controllers\API\UserController;
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


// Route::get('users', [UsersController::class, 'index']);
// Route::post('store', [UsersController::class, 'store']);
// Route::get('users/{user}', [UsersController::class, 'show']);
// Route::put('users/{user}', [UsersController::class, 'update']);
// Route::delete('users/{user}', [UsersController::class, 'destroy']);

Route::post('create', [BookController::class, 'create']);
Route::post('create/{create}', [BookController::class, 'update']);
Route::get('create/{create}', [BookController::class, 'show']);
Route::delete('create/{create}', [BookController::class, 'destroy']);


Route::post('authors', [AutherController::class, 'create']);
Route::post('authors/{author}', [AutherController::class, 'update']); 
Route::get('authors/{author}', [AutherController::class, 'show']); 
Route::delete('authors/{author}', [AutherController::class, 'destroy']); 


Route::post('login',[UserController::class,'loginUser']);
Route::controller(UserController::class)->group(function(){
Route::get('login','getUserDetail');
})->middleware('auth:api');
Route::controller(UserController::class)->group(function(){

    Route::get('logout','userLogout');
})->middleware('auth:api');
Route::post('register', [UserController::class, 'register']);


Route::get('search', [BookController::class, 'search']);

Route::post('borrow',[LibraryController::class,'create']);
Route::post('borrow/{borrow}', [LibraryController::class, 'update']);
Route::get('borrow/{borrow}', [LibraryController::class, 'show']);
Route::delete('borrow/{borrow}', [LibraryController::class, 'destroy']);

Route::post('/borrows', [LibraryController::class, 'borrowBook']);
Route::post('/return', [LibraryController::class, 'return']);
