<?php

declare(strict_types=1);

namespace Dht\Auth\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Event service provider to create listeners for socialite plugins
 */
class AuthkitEventServiceProvider extends ServiceProvider
{
	/** @var array<string,string[]> */
	protected $listen = [
		\SocialiteProviders\Manager\SocialiteWasCalled::class => [
			'SocialiteProviders\\Keycloak\\KeycloakExtendSocialite@handle'
		]
	];
}
