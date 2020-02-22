<?php

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

Route::get('/', function () {
    return view('welcome');
    // \App\User::create([
    //     'username' => 'john.doe',
    //     'password' => bcrypt(12345),
    //     'first_name' => 'John',
    //     'last_name' => 'Doe'
    // ]);

    // \App\User::create([
    //     'username' => 'richard.roe',
    //     'password' => bcrypt(12345),
    //     'first_name' => 'Richard',
    //     'last_name' => 'Roe'
    // ]);

    // \App\User::create([
    //     'username' => 'jane.poe',
    //     'password' => bcrypt(12345),
    //     'first_name' => 'Jane',
    //     'last_name' => 'Poe'
    // ]);
});
