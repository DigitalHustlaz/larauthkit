<?php

declare(strict_types=1);

use Dht\Auth\Http\Controllers\AuthenticationController;

\Route::get('/login', [AuthenticationController::class, 'login'])->name('login');
\Route::get('/logout', [AuthenticationController::class, 'logout'])->name('logout');
\Route::get('/callback', [AuthenticationController::class, 'callback'])->name('login.callback');
