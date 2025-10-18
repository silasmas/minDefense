<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\VeteranController;
use App\Http\Controllers\StateAssetController;
use App\Http\Controllers\VeteranAssetController;
use App\Http\Controllers\StateAssetApiController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\VeteranSmsVerifyController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\AssetSearchController;

Route::get('/', [StateAssetController::class, 'index'])->name('home');
Route::get('/about', [StateAssetController::class, 'about'])->name('about');
Route::get('/actualites', [StateAssetController::class, 'actualites'])->name('actualites');
Route::get('/ministre', [StateAssetController::class, 'ministre'])->name('ministre');
Route::get('/gouvernance', [StateAssetController::class, 'gouvernance'])->name('gouvernance');
Route::get('/events', [StateAssetController::class, 'events'])->name('events');
Route::get('/contact', [StateAssetController::class, 'contact'])->name('contact');


Route::prefix('otp')->group(function () {
    Route::post('send/reset',  [OtpController::class, 'sendReset'])->name('otp.send.reset');
    Route::post('verify/reset',[OtpController::class, 'verifyReset'])->name('otp.verify.reset');

    Route::post('send/login',  [OtpController::class, 'sendLogin'])->name('otp.send.login');
    Route::post('verify/login',[OtpController::class, 'verifyLogin'])->name('otp.verify.login');
});

Route::get('/admin/veterans/{veteran}/card', [VeteranController::class, 'single'])
    ->middleware(['auth'])  // ajuste tes middlewares
    ->name('veterans.card');
// Route::get('/verify/veterans/{veteran}', [VeteranController::class, 'verify'])
//     ->name('veterans.verify'); // <-- la route manquante
Route::get('/verify/veterans/{veteran}', [VeteranController::class, 'verify'])
    ->middleware(['signed','throttle:20,1']) // signature requise, 20 req/min
    ->name('veterans.verify');
// / Page de vérification par SMS (pas besoin d'être connecté)
Route::get('/verify/sms/{token}',  [VeteranSmsVerifyController::class, 'show'])->name('veterans.sms.verify');
Route::post('/verify/sms/{token}', [VeteranSmsVerifyController::class, 'confirm'])->name('veterans.sms.verify.submit');


Route::post('/verify/sms/{token}/decline', [VeteranSmsVerifyController::class, 'decline'])->name('veterans.sms.verify.decline'); // <—
Route::middleware('guest')->group(function () {
    // Demande de lien de réinitialisation
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Formulaire pour définir le nouveau mot de passe
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});
Route::get('/admin/veterans/{veteran}/card/preview', [VeteranController::class,'preview'])
    ->name('veterans.card.preview');
Route::get('/admin/veterans/{veteran}/card/duplex', [VeteranController::class,'duplex'])
    ->name('veterans.card.duplex');
Route::get('/symlink', function () {
    return view('symlink');
})->name('generate_symlink');

// routes/web.php
Route::middleware(['web','auth'])
    ->prefix('admin/api')
    ->group(function () {
        Route::get('/state-assets', [\App\Http\Controllers\Admin\StateAssetApiController::class,'index'])
            ->name('admin.api.state-assets.index');
    });
Route::middleware(['auth']) // + 'verified' + Gate si besoin
    ->prefix('admin/api')
    ->name('admin.api.')
    ->group(function () {
        Route::get('veteran-assets', [VeteranAssetController::class, 'index'])
            ->name('veteran-assets.index');
    });
Route::middleware(['web','filament.admin'])
    ->prefix(filament()->getCurrentPanel()?->getPath() ?? 'admin')   // <= préfixe réel
    ->name('admin.')
    ->group(function () {
        Route::get('api/assets', [AssetSearchController::class, 'index'])
            ->name('api.assets.index'); // nom clair
    });
