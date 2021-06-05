<?php

declare(strict_types=1);

namespace Dht\Auth\Http\Controllers;

/**
 * Methods for handling user authentication operations
 */
class AuthenticationController extends Controller
{

	/**
	 * Start the login flow for a user
	 *
	 * Redirects the user to the SSO service
	 *
	 * @return mixed
	 */
	public function login()
	{
		// TODO: Pass in 'previous' URL from session so we can redirect back
		// if we were redirected to login from a guard?
		return \Socialite::driver('keycloak')
			->scopes(config('larauthkit.authn.scopes'))
			->redirect()
		;
	}

	/**
	 * Handle the response from the SSO service
	 *
	 * Uses Socialite to exchange the code for a token and fetches basic
	 * user information. Attempts to log the user into this app, and creates
	 * them if they don't exist. Then redirects the user to the configured
	 * post_login url.
	 *
	 * @return mixed
	 */
	public function callback()
	{
		// Get the user class from the Laravel auth config
		$user_class = config('auth.providers.users.model');

		// Try and complete the SSO login
		$sso_user = \Socialite::driver('keycloak')->user();
		// TODO: If login failed?
		// Attempt to log the SSO user in locally
		$user_crn = 'crn:user:'.$sso_user->getId();
		$user = \Auth::loginUsingId($user_crn);

		if ($user === false)
		{
			// User doesn't exist, create them.
			$user = new $user_class();
			$id_field = $user->getAuthIdentifierName();
			$user->{$id_field} = $user_crn;
			$user->name = $sso_user->getName();
			$user->email = $sso_user->getEmail();
			$register_event_result = event(new \Dht\Auth\Events\UserRegistration($user));
			if (sizeof($register_event_result))
			{
				foreach ($register_event_result as $result)
				{
					if ($result instanceof $user_class)
					{
						$user = $result;
					}
				}
			}
			if (!$user->exists)
			{
				$user->save();
			}
			\Auth::login($user);
		}
		else
		{
			// Existing user, update their information
			$user->name = $sso_user->getName();
			$user->email = $sso_user->getEmail();
			$user->save();
		}

		event(new \Dht\Auth\Events\UserLogin($user));

		return redirect(url(config('larauthkit.authn.urls.post_login')));
	}

	/**
	 * Explicitly log out of this application and the SSO service
	 *
	 * @return mixed
	 */
	public function logout()
	{
		event(new \Dht\Auth\Events\UserLogout(\Auth::user()));
		// Log out locally
		\Auth::logout();
		// Redirect to log out remotely as well
		return redirect(
			rtrim(config('services.keycloak.base_url'), '/').
			'/realms/'.config('services.keycloak.realms', 'master').
			'/protocol/openid-connect/logout?'.
			http_build_query([
				'redirect_uri' => url(config('larauthkit.authn.urls.post_logout'))
			])
		);
	}
}
