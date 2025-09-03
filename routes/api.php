<?php

use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReponseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UtilisateurController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('roles', RoleController::class);
Route::apiResource('utilisateur', UtilisateurController::class);
Route::post('changer-mot-de-passe', [UtilisateurController::class, 'changerMotDePasse']);
// Route::post('login', [LoginController::class, 'login']);
Route::apiResource('offre', OffreController::class);
Route::apiResource('test', TestController::class);
Route::apiResource('question', QuestionController::class);
Route::apiResource('candidature', CandidatureController::class);
Route::apiResource('reponse', ReponseController::class);

Route::post('login', [UtilisateurController::class, 'login']);
Route::post('register', [UtilisateurController::class, 'register']);
