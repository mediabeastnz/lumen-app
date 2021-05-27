<?php

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

$router->group(['prefix' => 'api'], function () use ($router) {

    $router->get('products', [ 'uses' => 'ProductController@index']);
    $router->get('products/{id}', [ 'uses' => 'ProductController@show']);
    $router->post('products', [ 'uses' => 'ProductController@store']);
    $router->put('products/{id}', [ 'uses' => 'ProductController@update']);
    $router->delete('products/{id}', [ 'uses' => 'ProductController@destroy']);
    $router->post('products/{id}/addstock', [ 'uses' => 'ProductController@addStock']);

});
