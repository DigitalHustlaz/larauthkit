<!--
Package Name: SSO Laravel with Keycloak 
Author: Pasha Zahari
Website: https://digitalhustlaz.com
Contact: support@digitalhustlaz.com
License: MIT License
-->

<?php

declare(strict_types=1);

return [

	'authn' => [
		/*
		 * Enable/disable the authentication component
		 *
		 * When disabled, the authentication service provider, socialite plugin,
		 * routes, and all other components of the authentication system are not
		 * registered with Laravel.
		 */
		'enable' => true,

		// Scopes to request from the OIDC service
		'scopes' => ['email'],

		/*
		  * Path to the OIDC config
		  *
		  * This uses the Laravel storage driver, so this needs to be configured to
		  * use one of the filesystems set up in config/filesystems.php.
		  */
		'config' => [
			'disk' => 'local',
			'path' => 'auth.json'
		],

		/*
		 * Customize registered routes
		 *
		 * All routes are registered with \Route::group(this_array, function() {})
		 * At minimum, you should specify 'middleware' and 'prefix' though you
		 * can specify any options that Laravel's router will accept.
		 */
		'routing' => [
			'middleware' => 'web',
			'prefix' => '/auth'
		],

		/*
		  * URL to redirect the user to at various points in the process
		  *
		  * These are run through Laravel's url() helper, so can be relative (in which
		  * case they will respect the app's current URL/APP_URL/etc) or absolute.
		  *
		  * post_login: After a successful login
		  * post_logout: After a successful logout. Must be configured in SSO service.
		  */
		'urls' => [
			'post_login' => '/',
			'post_logout' => '/'
		]
	],

	'authz' => [
		/*
		 * Enable/disable the authorization component
		 *
		 * Currently authorization is not implemented.
		 */
		'enable' => true,
	]
];
