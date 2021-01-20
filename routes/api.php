<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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


$router->group(['middleware' => 'auth:api'], function ($router) {
    $router->group(['middleware' => 'localization'], function ($router) {
        $router->post('/logout', 'AuthController@logout');
    });
});
