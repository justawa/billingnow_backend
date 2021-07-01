<?php

use Illuminate\Http\Request;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('search-list', 'ItemController@search');
Route::post('search-single-item', 'ItemController@search_single_item');
Route::get('stock-less-than-ten', 'ItemController@stock_less_than_ten');
Route::get('most-sold-items', 'ItemController@most_sold_items');

Route::get('customer-list', 'CustomerController@search_list');
Route::post('search-customer', 'CustomerController@search_by_phone');
Route::put('update-customer/{id}', 'CustomerController@update');
Route::get('customers', 'CustomerController@index');
Route::get('customers/{id}', 'CustomerController@show');

Route::post('search-party', 'PartyController@search_by_phone');
Route::put('update-party/{id}', 'PartyController@update');

Route::post('create-bill', 'PurchaseController@store');
Route::get('bill/{id}', 'PurchaseController@show');
Route::put('cancel-bill/{id}', 'PurchaseController@cancel_bill');

Route::post('create-invoice', 'SaleController@store');
Route::get('invoice/{id}', 'SaleController@show');
Route::put('cancel-invoice/{id}', 'SaleController@cancel_invoice');
Route::post('update-sale-payment-mode', 'SaleController@update_payment_mode');

Route::put('cancel-expense/{id}', 'ExpenseController@cancel_expense');

Route::get('daily-purchase', 'ReportController@daily_purchase');
Route::get('daily-sale', 'ReportController@daily_sale');
Route::get('sale-profit', 'ReportController@sale_profit');
Route::get('stock-summary', 'ReportController@stock_summary');
Route::get('cancelled-invoice', 'ReportController@cancelled_invoice');
Route::get('item-gst', 'ReportController@item_gst');

Route::get('last-customers', 'CustomerController@last_ten_customers');

Route::get('expenses', 'ExpenseController@index');
Route::post('create-expense', 'ExpenseController@store');
Route::post('edit-expense/{id}', 'ExpenseController@update');

Route::get('fix-item', 'ItemController@fixed_items');
Route::post('add-fix-item', 'ItemController@add_fixed_item');
Route::get('get-fix-item', 'ItemController@get_fixed_item');
Route::get('get-fix-item/{id}', 'ItemController@get_fixed_item_by_id');
Route::post('update-fix-item/{id}', 'ItemController@update_fixed_item');
