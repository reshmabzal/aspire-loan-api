<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('add_customer', 'App\Http\Controllers\CustomerController@store');
Route::post('get_key', 'App\Http\Controllers\CustomerController@show');
Route::middleware('validaterequest')->group(function() {
    Route::post('create_loan', 'App\Http\Controllers\LoanController@store');
    Route::post('view_loan', 'App\Http\Controllers\LoanController@show');
    Route::post('repayment', 'App\Http\Controllers\LoanController@repayment');
});
Route::middleware('validateadminrequest')->group(function() {
    Route::post('approve_loan', 'App\Http\Controllers\LoanController@approve_loan');
});

