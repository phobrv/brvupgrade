<?php

namespace Phobrv\BrvUpgrade;

use Illuminate\Support\ServiceProvider;

class BrvUpgradeServiceProvider extends ServiceProvider {
	/**
	 * Perform post-registration booting of services.
	 *
	 * @return void
	 */
	public function boot(): void{
		// $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'phobrv');
		$this->loadViewsFrom(__DIR__ . '/../resources/views', 'phobrv');
		// $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
		$this->loadRoutesFrom(__DIR__ . '/routes.php');

		// Publishing is only necessary when using the CLI.
		if ($this->app->runningInConsole()) {
			$this->bootForConsole();
		}
	}

	/**
	 * Register any package services.
	 *
	 * @return void
	 */
	public function register(): void{
		$this->mergeConfigFrom(__DIR__ . '/../config/brvupgrade.php', 'brvupgrade');

		// Register the service the package provides.
		$this->app->singleton('brvupgrade', function ($app) {
			return new BrvUpgrade;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides() {
		return ['brvupgrade'];
	}

	/**
	 * Console-specific booting.
	 *
	 * @return void
	 */
	protected function bootForConsole(): void {
		// Publishing the configuration file.
		// $this->publishes([
		// 	__DIR__ . '/../config/brvupgrade.php' => config_path('brvupgrade.php'),
		// ], 'brvupgrade.config');

		// Publishing the views.
		/*$this->publishes([
	            __DIR__.'/../resources/views' => base_path('resources/views/vendor/phobrv'),
*/

		// Publishing assets.
		/*$this->publishes([
	            __DIR__.'/../resources/assets' => public_path('vendor/phobrv'),
*/

		// Publishing the translation files.
		/*$this->publishes([
	            __DIR__.'/../resources/lang' => resource_path('lang/vendor/phobrv'),
*/

		// Registering package commands.
		// $this->commands([]);
	}
}
