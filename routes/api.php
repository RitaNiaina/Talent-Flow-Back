<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OffreController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('roles', RoleController::class);
Route::apiResource('utilisateur', UtilisateurController::class);
Route::post('changer-mot-de-passe', [UtilisateurController::class, 'changerMotDePasse']);
Route::post('login', [LoginController::class, 'login']);
Route::apiResource('offre', OffreController::class);