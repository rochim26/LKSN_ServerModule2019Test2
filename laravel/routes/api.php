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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1'], function ()
{
    Route::group(['prefix' => 'auth'], function ()
    {
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');
        Route::get('logout', 'AuthController@logout')->middleware('member');
    });

    Route::group(['middleware' => 'member'], function ()
    {
        Route::post('board/{board}/member', 'BoardController@addTeamMember');
        Route::delete('board/{board}/member/{member}', 'BoardController@removeTeamMember');
        Route::post('board/{board}/list', 'BoardController@addList');
        Route::put('board/{board}/list/{list}', 'BoardController@updateList');
        Route::delete('board/{board}/list/{list}', 'BoardController@deleteList');
        Route::post('board/{board}/list/{list}/right', 'BoardController@moveToRightList');
        Route::post('board/{board}/list/{list}/left', 'BoardController@moveToLeftList');
        Route::post('board/{board}/list/{list}/card', 'BoardController@addCard');
        Route::put('board/{board}/list/{list}/card/{card}', 'BoardController@updateCard');
        Route::delete('board/{board}/list/{list}/card/{card}', 'BoardController@deleteCard');
        Route::post('card/{card}/up', 'BoardController@moveUpCard');
        Route::post('card/{card}/down', 'BoardController@moveDownCard');
        Route::post('card/{card}/move/{list}', 'BoardController@moveCardToAnotherList');
        Route::resource('board', 'BoardController');
    });
    Route::post('image', 'BoardController@image');
    Route::get('image', 'BoardController@imageShow');
});
