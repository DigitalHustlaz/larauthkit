<?php

declare(strict_types=1);

namespace Dhtech\Auth\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Authkit core service provider
 */
class AuthkitServiceProvider extends ServiceProvider
{

	/**
	 * Register all providers and other components for any enabled features
	 * of Authkit
	 * 
	 * @return void
	 */
	public function register(): void
	{
		$this->mergeConfigFrom(__DIR__.'/../../config/larauthkit.php', 'larauthkit');
		if (config('larauthkit.authn.enable'))
		{
			$this->app->register(AuthkitEventServiceProvider::class);
			$this->app->register(AuthnServiceProvider::class);
		}

		if (config('larauthkit.authz.enable'))
		{
			$this->app->register(AuthzServiceProvider::class);
		}
	}

	/**
	 * Register publishable larauthkit resources
	 *
	 * @return void
	 */
	public function boot(): void
	{
		if ($this->app->runningInConsole())
		{
			$this->publishes([
				__DIR__.'/../../config/larauthkit.php' => config_path('larauthkit.php')
			], 'config');
			$this->publishes([
				__DIR__.'/../../database/migrations/larauthkit_update_users_table.php' => database_path('migrations/'.date('Y_m_d_His').'_larauthkit_update_users_table.php')
			], 'migrations');
		}
	}
}
