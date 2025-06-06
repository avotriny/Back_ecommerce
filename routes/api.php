<?php

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
use App\Http\Controllers\StripeWebhookController;

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

Route::post('categorie', [CategorieController::class, 'store']);
Route::post('/subcategorie', [SubCategorieController::class, 'store']);
Route::get('categorie', [CategorieController::class, 'index']);
Route::get('/subcategorie', [SubCategorieController::class, 'index']);

Route::post('/produit', [ProduitController::class, 'store']);
Route::get('/produit', [ProduitController::class, 'index']);

Route::post('/commande', [CommandeController::class, 'commande']);
Route::get('/commande', [CommandeController::class, 'index']);
Route::post('/stripe/webhook',    [StripeWebhookController::class, 'handleWebhook']);

