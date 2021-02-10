<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Models\Admin\Category;


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->post('/register', 'RegisterController@register');
$router->get('/confirm', 'RegisterController@confirm');
$router->post('/login', 'AuthController@login');
$router->post('/login/refresh', 'AuthController@refresh');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->post('/reset-password', 'AuthController@resetPassword');


$router->group(['middleware' => 'auth:api'], function ($router) {
    $router->group(['middleware' => 'localization'], function ($router) {

        $router->group(['prefix' => 'menu'], function ($router) {
            $router->get('/top', 'MenuController@topMenu');
            $router->get('/categories/{id}', 'MenuController@categories');
            $router->get('/category/{id}', 'MenuController@category');
        });

        $router->group(['middleware' => 'admin', 'namespace' => 'Admin', 'prefix' => 'admin'], function ($router) {

            $router->group(['prefix' => 'categories'], function ($router) {

                $router->put('/{id}/sort', 'CategoryController@updateOrder');

                $router->get('/colors', 'CategoryController@colors');
                $router->get('/array', 'CategoryController@arrayList');

                $router->get('/', 'CategoryController@index');
                $router->post('/', 'CategoryController@store');
                $router->get('/{id}', 'CategoryController@show');
                $router->post('/{id}', 'CategoryController@update');
                $router->delete('/{id}', 'CategoryController@destroy');
                $router->patch('/{id}', 'CategoryController@restore');
            });

            $router->group(['prefix' => 'redeem-codes'], function ($router) {
                $router->get('/', 'RedeemCodeController@index');
                $router->post('/', 'RedeemCodeController@store');
                $router->get('/{id}', 'RedeemCodeController@show');
                $router->put('/{id}', 'RedeemCodeController@update');
                $router->delete('/{id}', 'RedeemCodeController@destroy');
                $router->post('/import', 'RedeemCodeController@import');
            });

            $router->group(['prefix' => 'media'], function ($router) {
                $router->get('/types', 'MediaController@types');

                $router->get('/', 'MediaController@index');
                $router->post('/', 'MediaController@store');
                $router->get('/{id}', 'MediaController@show');
                $router->put('/{id}', 'MediaController@update');
                $router->delete('/{id}', 'MediaController@destroy');
                $router->patch('/{id}', 'MediaController@restore');
            });

            $router->group(['prefix' => 'users'], function ($router) {
                $router->get('/', 'UserController@index');
            });
        });

        $router->post('/logout', 'AuthController@logout');
        $router->get('/me', function () {
            $user = auth()->user();
            $user['category_home'] = Category::whereHas('categories')->first()->id ?? 1;
            return [
                'user' => $user()
            ];
        });
        $router->post('/profile/update', 'ProfileController@update');
        $router->post('/profile/update-password', 'ProfileController@updatePassword');
    });
});
