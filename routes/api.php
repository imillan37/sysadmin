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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
/*primera ruta  de recurso  para permitir solo algunos metodos se pasa en el tercer metodo ['only' => ['index','show']] o podemos hacer except
['except'=>['create','edit']]
*/

/**
	Users
**/
Route::resource('users', 'User\UserController');
/**
	Solicitudes
**/
Route::resource('solicitudes','Solicitudes\SolicitudesController');
/**
	Capitulos
**/
Route::resource('capitulos','Capitulos\CapitulosController');
/**
	Miembros
**/
Route::resource('miembros','Miembros\MiembrosController');
/**
	Region
**/
Route::resource('region','Region\RegionController');