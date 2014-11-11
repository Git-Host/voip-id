<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the Closure to execute when that URI is requested.
  |
 */


// WARNING: you must comment this init route on production
Route::controller('init', 'InitController');


// Start of public routes

Route::get('/', 'HomeController@showHome');

Route::controller('login', 'LoginController');
Route::get('logout', 'LoginController@getLogout');

Route::controller('register', 'RegisterController');

Route::controller('password', 'PasswordController');


// Start of private routes protected with auth

Route::controller('dashboard', 'DashboardController');

Route::resource('profile', 'ProfileController', array('only' => array('index', 'update')));

Route::resource('user', 'UserController', array('only' => array('index', 'update')));

Route::resource('users', 'UserManagementController',  array('only' => array('index', 'update','delete')));
Route::get('users/add/{hash?}', 'UserManagementController@create');
Route::post('users/save/{hash?}', 'UserManagementController@store');
Route::get('users/edit/{id}/{hash?}', 'UserManagementController@edit');
Route::any('users/update/{id}/{hash?}', 'UserManagementController@update');
Route::get('users/delete/{id}/{hash?}', 'UserManagementController@destroy');
Route::get('users/ban/{id}/{hash?}', 'UserManagementController@ban');
Route::get('users/unban/{id}/{hash?}', 'UserManagementController@unban');

Route::get('managers','UserManagementController@manager');

Route::any('users/search', 'UserManagementController@index');
Route::any('managers/search', 'UserManagementController@manager');

Route::any('domain/update/{id}','DomainController@update');
Route::get('domain/users/add/{id}','DomainController@addUser');
Route::controller('domain','DomainController');

Route::get(Config::get('settings.panel_path').'/{hash}','PanelController@register');
Route::any(Config::get('settings.panel_path').'/{hash}/save','PanelController@store');

Route::any('phone_number/update/{id}','PhoneNumberController@update');
Route::controller('phone_number','PhoneNumberController');

Route::controller('main_config','SettingController');

Route::controller('contact','ContactController');