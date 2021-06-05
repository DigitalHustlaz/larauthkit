<?php

declare(strict_types=1);

namespace Dhtech\Auth\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Authorization provider to register and configure all
 * assets involved in permission checking
 */
class AuthzServiceProvider extends ServiceProvider
{
	public function register()
	{
	}

	public function boot()
	{
	}
}
