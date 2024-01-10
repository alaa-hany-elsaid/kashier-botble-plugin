<?php

use Alaa\Kashier\Http\Controllers\KashierController;

Route::group(['controller' => KashierController::class, 'middleware' => ['web', 'core']], function () {
    Route::get('payment/kashier/status', 'getCallback')->name('payments.kashier.status');

});