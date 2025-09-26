<?php

use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReponseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\StatsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('logins', [UtilisateurController::class, 'login'])->name('login');
Route::post('register', [UtilisateurController::class, 'register']);


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
    
        return $request->user();
    });
    Route::apiResource('offre', OffreController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('utilisateur', UtilisateurController::class);
    Route::post('changer-mot-de-passe', [UtilisateurController::class, 'changerMotDePasse']);
    
    Route::apiResource('test', TestController::class);
    Route::apiResource('question', QuestionController::class);
    Route::apiResource('candidature', CandidatureController::class);
    Route::apiResource('reponse', ReponseController::class);

    Route::post('logout', [UtilisateurController::class, 'logout']);
    Route::get('stats', [StatsController::class, 'index']);
    
});
    