<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DroitController;
use App\Http\Controllers\DocumentUserController;
use App\Http\Controllers\DocumentContactController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\RappelController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ThemeGeneralController;
use App\Http\Controllers\JourFerieController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PrestationController;
use App\Http\Controllers\PointageUserController;
use App\Http\Controllers\ServiceController;

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
   // Role Routes
Route::get('roles', [RoleController::class, 'index']);
Route::post('roles', [RoleController::class, 'store']);
Route::get('roles/{id}', [RoleController::class, 'show']);
Route::put('roles/{id}', [RoleController::class, 'update']);
Route::delete('roles/{id}', [RoleController::class, 'destroy']);

// Public Routes (No Authentication Required)
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::get('users/{id}/photo', [UserController::class, 'showPhoto']);


// Protected Routes (Authentication Required)
Route::group(['middleware' => ['auth.jwt']], function () {
    
    // User Routes
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/me', [UserController::class, 'me']);
    Route::post('users/logout', [UserController::class, 'logout']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::post('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);

    // Services Routes
    Route::get('services', [ServiceController::class, 'index']);
    Route::post('services', [ServiceController::class, 'logout']);
    // Route::get('users/me', [UserController::class, 'me']);
    // Route::post('users/logout', [UserController::class, 'logout']);
    // Route::get('users/{id}', [UserController::class, 'show']);
    // Route::post('users/{id}', [UserController::class, 'update']);
    // Route::delete('users/{id}', [UserController::class, 'destroy']);
    

    // Droit Routes
    Route::get('droits', [DroitController::class, 'index']);
    Route::post('droits', [DroitController::class, 'store']);
    Route::get('droits/{id}', [DroitController::class, 'show']);
    Route::put('droits/{id}', [DroitController::class, 'update']);
    Route::delete('droits/{id}', [DroitController::class, 'destroy']);

    // DocumentUser Routes
    Route::get('document_users', [DocumentUserController::class, 'index']);
    Route::post('document_users', [DocumentUserController::class, 'store']);
    Route::get('document_users/{id}', [DocumentUserController::class, 'show']);
    Route::post('document_users/{id}', [DocumentUserController::class, 'update']);
    Route::delete('document_users/{id}', [DocumentUserController::class, 'destroy']);
    Route::get('document_users/{id}/document', [DocumentUserController::class, 'showDocument']);

    // Paiement Routes
    Route::get('paiements', [PaiementController::class, 'index']);
    Route::post('paiements', [PaiementController::class, 'store']);
    Route::get('paiements/{id}', [PaiementController::class, 'show']);
    Route::put('paiements/{id}', [PaiementController::class, 'update']);
    Route::delete('paiements/{id}', [PaiementController::class, 'destroy']);

    // Rappel Routes
    Route::get('rappels', [RappelController::class, 'index']);
    Route::post('rappels', [RappelController::class, 'store']);
    Route::get('rappels/{id}', [RappelController::class, 'show']);
    Route::put('rappels/{id}', [RappelController::class, 'update']);
    Route::delete('rappels/{id}', [RappelController::class, 'destroy']);

    // Conge Routes
    Route::get('conges', [CongeController::class, 'index']);
    Route::post('conges', [CongeController::class, 'store']);
    Route::get('conges/{id}', [CongeController::class, 'show']);
    Route::put('conges/{id}', [CongeController::class, 'update']);
    Route::delete('conges/{id}', [CongeController::class, 'destroy']);

    // Pointage Routes
    Route::get('pointages', [PointageController::class, 'index']);
    Route::post('pointages', [PointageController::class, 'store']);
    Route::get('pointages/{id}', [PointageController::class, 'show']);
    Route::put('pointages/{id}', [PointageController::class, 'update']);
    Route::delete('pointages/{id}', [PointageController::class, 'destroy']);

    // Contact Routes
    Route::get('contacts', [ContactController::class, 'index']);
    Route::post('contacts', [ContactController::class, 'store']);
    Route::get('contacts/{id}', [ContactController::class, 'show']);
    Route::put('contacts/{id}', [ContactController::class, 'update']);
    Route::delete('contacts/{id}', [ContactController::class, 'destroy']);

    // DocumentContact Routes
    Route::get('document_contacts', [DocumentContactController::class, 'index']);
    Route::post('document_contacts', [DocumentContactController::class, 'store']);
    Route::get('document_contacts/{id}', [DocumentContactController::class, 'show']);
    Route::post('document_contacts/{id}', [DocumentContactController::class, 'update']);
    Route::delete('document_contacts/{id}', [DocumentContactController::class, 'destroy']);
    Route::get('document_contacts/{id}/document', [DocumentContactController::class, 'showDocument']);

    // Category Routes
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    // Prestation Routes
    Route::get('prestations', [PrestationController::class, 'index']);
    Route::post('prestations', [PrestationController::class, 'store']);
    Route::get('prestations/{id}', [PrestationController::class, 'show']);
    Route::put('prestations/{id}', [PrestationController::class, 'update']);
    Route::delete('prestations/{id}', [PrestationController::class, 'destroy']);

    // RendezVous Routes
    Route::get('rendez_vous', [RendezVousController::class, 'index']);
    Route::post('rendez_vous', [RendezVousController::class, 'store']);
    Route::get('rendez_vous/{id}', [RendezVousController::class, 'show']);
    Route::put('rendez_vous/{id}', [RendezVousController::class, 'update']);
    Route::delete('rendez_vous/{id}', [RendezVousController::class, 'destroy']);

    // Notification Routes
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications', [NotificationController::class, 'store']);
    Route::get('notifications/{id}', [NotificationController::class, 'show']);
    Route::put('notifications/{id}', [NotificationController::class, 'update']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);

    // ThemeGeneral Routes
    Route::get('theme_generals', [ThemeGeneralController::class, 'index']);
    Route::post('theme_generals', [ThemeGeneralController::class, 'store']);
    Route::get('theme_generals/{id}', [ThemeGeneralController::class, 'show']);
    Route::put('theme_generals/{id}', [ThemeGeneralController::class, 'update']);
    Route::delete('theme_generals/{id}', [ThemeGeneralController::class, 'destroy']);

    // JourFerie Routes
    Route::get('jours_feries', [JourFerieController::class, 'index']);
    Route::post('jours_feries', [JourFerieController::class, 'store']);
    Route::get('jours_feries/{id}', [JourFerieController::class, 'show']);
    Route::put('jours_feries/{id}', [JourFerieController::class, 'update']);
    Route::delete('jours_feries/{id}', [JourFerieController::class, 'destroy']);

    // PointageUser Routes
    Route::post('pointage_users/{pointageId}/{userId}', [PointageUserController::class, 'store']);
    Route::delete('pointage_users/{pointageId}/{userId}', [PointageUserController::class, 'destroy']);
});
