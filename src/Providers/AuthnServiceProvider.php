<?php

declare(strict_types=1);

namespace Dht\Auth\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Authentication provider to perform setup for authentication processes
 */
class AuthnServiceProvider extends ServiceProvider
{
	/**
	 * Register the additional service providers the authentication process depends on
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->app->register(\SocialiteProviders\Manager\ServiceProvider::class);
	}

	/**
	 * Initialize and register all authentication resources
	 *
	 * @return void
	 */
	public function boot(): void
	{
		// Register all authentication routes
		$this->bootRoutes();
		// Load keycloak configuration and set in Laravel config()
		$this->bootConfig();
	}

	/**
	 * Register all authentication routes
	 * If not already cached, generate and register the URL the SSO service
	 * should redirect back to.
	 *
	 * @return void
	 */
	protected function bootRoutes(): void
	{
		// Load routes
		\Route::group(config('larauthkit.authn.routing'), function() {
			$this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
		});

		// Figure out where the route is after booting
		if (!config()->has('services.keycloak.redirect'))
		{
			$this->app->booted(static function() {
				// TODO: For some reason route($name) isn't working here. We're
				// just looking through the routes manually for now...

				foreach (\Route::getRoutes() as $route)
				{
					if ($route->getName() === 'login.callback')
					{
						config(['services.keycloak.redirect' => url($route->uri())]);
						return;
					}
				}

				throw new \Exception('Route [login.callback] not found');
			});
		}
	}

	/**
	 * Generate any missing config values for keycloak by reading JSON
	 * auth config
	 *
	 * @return void
	 */
	protected function bootConfig(): void
	{
		// We check if the values are available because they may have
		// previously been generated and cached by Laravel in which case
		// we can save some work.
		if (!(config()->has('services.keycloak.client_id') &&
			config()->has('services.keycloak.client_secret') &&
			config()->has('services.keycloak.base_url') &&
			config()->has('services.keycloak.realm')))
		{
			// Figure out where to load the config from
			$disk = config('larauthkit.authn.config.disk');
			$path = config('larauthkit.authn.config.path');
			if (!\Storage::disk($disk)->exists($path))
			{
				// If it doesn't exist, skip the loading.
				// We do this so booting the provider doesn't cause
				// errors during install/etc.
				return;
			}

			$config_raw = \Storage::disk($disk)->get($path);
			$config_json = json_decode($config_raw, true);

			if (!isset($config_json))
			{
				throw new \Exception("Could not parse authentication configuration at $disk:$path");
			}

			config([
				'services.keycloak.client_id' => $config_json['resource'],
				'services.keycloak.client_secret' => $config_json['credentials']['secret'],
				'services.keycloak.base_url' => $config_json['auth-server-url'],
				'services.keycloak.realms' => $config_json['realm']
			]);
		}
	}
}
