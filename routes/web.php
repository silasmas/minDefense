<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\VeteranController;
use App\Http\Controllers\VeteranSmsVerifyController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
Route::get('/', function () {
    return view('welcome');
});


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
