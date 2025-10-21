<?php

use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReponseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\CompetenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('logins', [UtilisateurController::class, 'login'])->name('login');
Route::post('register', [UtilisateurController::class, 'register']);


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
    
        return $request->user();
    });
    
    
});
Route::apiResource('offre', OffreController::class);
Route::apiResource('roles', RoleController::class);
Route::apiResource('utilisateur', UtilisateurController::class);
Route::post('changer-mot-de-passe', [UtilisateurController::class, 'changerMotDePasse']);

Route::apiResource('test', TestController::class);
Route::apiResource('question', QuestionController::class);
Route::apiResource('candidature', CandidatureController::class);
Route::apiResource('reponse', ReponseController::class);
Route::apiResource('competences', CompetenceController::class);
Route::post('logout', [UtilisateurController::class, 'logout']);
Route::get('stats', [StatsController::class, 'index']);

Route::get('offre/{id}/details-complet', [OffreController::class, 'getDetailsComplet']);
Route::get('offres/{offre_id}/test', [TestController::class, 'getByOffre']);
Route::post('/test-complet', [TestController::class, 'storeWithQuestions']);
Route::put('/test-complet/{id}', [TestController::class, 'updateWithQuestions']);
Route::get('/test-complet/{id}', [TestController::class, 'showWithQuestions']);
Route::post('/submitTest', [ReponseController::class, 'submitTest']);
Route::put('/candidatures/{id}/refuser', [CandidatureController::class, 'refuserCandidature']);
