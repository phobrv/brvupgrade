<?php
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'auth:sanctum', 'lang', 'verified'])->namespace('Phobrv\BrvUpgrade\Controllers')->group(function () {
	Route::middleware(['can:superuser'])->prefix('admin')->group(function () {
		Route::get('/upgrade', 'UpgradeController@index')->name('upgrade.index');
		Route::get('/upgrade/replace', 'UpgradeController@replace')->name('upgrade.replace');
		Route::post('/upgrade/run', 'UpgradeController@run')->name('upgrade.run');
	});
});
