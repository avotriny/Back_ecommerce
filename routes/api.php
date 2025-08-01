<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\RegistrationVerificationController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\SubCategorieController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\LivraisonController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login', [AuthenticatedSessionController::class, 'login']);
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::get('/registration/verify', [RegistrationVerificationController::class, 'verify']);
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->name('verification.send');
Route::middleware('auth:sanctum')->post('/profile', [ProfileController::class, 'updateProfile']);

Route::middleware('auth:sanctum')->post('/produit', [ProduitController::class, 'store']);
Route::get('/produit', [ProduitController::class, 'index']);
Route::middleware('auth:sanctum')->post('categorie', [CategorieController::class, 'store']);
Route::middleware('auth:sanctum')->post('/subcategorie', [SubCategorieController::class, 'store']);
Route::get('categorie', [CategorieController::class, 'index']);
Route::get('/subcategorie', [SubCategorieController::class, 'index']);
Route::middleware('auth:sanctum')->post('produit/{produit}/like', [ProduitController::class, 'toggleLike']);
Route::get('/produit/liste-par-nom', [ProduitController::class, 'listeParNom']);
Route::get('/produit/{nom}', [ProduitController::class, 'showByNom']);
Route::post('/commande', [CommandeController::class, 'commande']);
Route::get('/like', [ProduitController::class, 'listLike']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/commande', [CommandeController::class, 'index']);
    Route::get('/livraison', [CommandeController::class, 'livraison']);
});
Route::middleware('auth:sanctum')->post('livraisonFaite', [LivraisonController::class, 'livraisonFaite']);
Route::middleware('auth:sanctum')->get('livraisonFaite', [LivraisonController::class, 'livraisonFaites']);
Route::middleware(['auth:sanctum','admin'])->group(function() {

Route::get('/users', [AuthenticatedSessionController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::patch('/users/{user}/role',   [AdminController::class, 'updateRole']);
    Route::patch('/users/{user}/active', [AdminController::class, 'updateActive']);
});
});

