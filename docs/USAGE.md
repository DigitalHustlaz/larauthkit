
### Package Name: SSO Laravel with Keycloak 
### Author: Pasha Zahari
### Website: https://digitalhustlaz.com
### Contact: support@digitalhustlaz.com
### License: MIT License
### DATE: 06052021

# Usage

What the larauthkit package does and how to customize it.


## Endpoints

After install, the larauthkit package registers a few new routes in your
application.

### /auth/login

When called, this immediately redirects the user to the SSO service to perform
authentication.

This route is registered with the name `login` if you need to reference it from
within your templates.

### /auth/callback

After authentication has been completed, the SSO service redirects the user back
to this URL (it must be registered with the SSO services as a valid redirect URL).

This method will:

* Exchanging the returned code for a token and fetch basic user information
* Attempt to log in a user in your configured user driver whose auth identifier
  matches the ID of the user returned by the SSO service.
* If that user does not exist, it will be created.
* Updates the existing/newly created user with the name and email returned by
  the SSO service for the logged in user.
* Redirect the user to the configured `post_login` URL (default `/`).

### /auth/logout

This method will log the user out locally, then redirect them to the SSO service
to log them out of that as well. After logging out, they will be redirected to
the configured `post_logout` URL (default `/`). This URL must be registered as
a valid redirect URL with the SSO service.

This route is registered with the name `logout` if you need to reference it from
within your templates.


## Events

The larauthkit package provides a few events for hooking into the authentication
process.

All events are passed a User model as the `user` property of the event.

### UserRegistration

On the first login by a SSO user to this service, a new record will be created
for them. This event is fired _after_ the new user's model has been initialized
with the SSO user's data but _before_ it is saved.

This is primarily intended to give you the opportunity to initialize any other
related records your application requires or calculate and configure values for
any columns you have added to the user model.

If your event handler returns an instance of your user model, it will be used
instead of the one that was generated. This gives you the opportunity to match
unrecognized users to existing users if you're migrating to larauthkit. See
[MIGRATING.md](MIGRATING.md) for details and an example.

### UserLogin

This is fired on every login of a user into the service, including first time
logins that fired a `UserRegistration` event. 

This event fires _after_ all login steps have been completed, but _before_ the
user is redirected back to the configured post_login page.

### UserLogout

This is fired on every manual logout from your application (i.e., this will not
fire on logouts caused by session timeouts or other means).

This is fired _after_ the user has been logged out of your application, but
_before_ the user is redirected to the SSO service to be logged out of that.


## User Model Class

A pre-configured user model is provided to extend from, but it's not necessary
to use. Nor is it necessary to use the default Laravel one.

### Changing User Model Class

Authkit respects the model specified on the default 'users' provider in Laravel's
authentication configuration. If you wish to use an entirely different model, it
can be specified by updating `config/auth.php`, replacing the class in the section:

```php
	'providers' => [
		'users' => [
			'driver' => 'eloquent',
			'model' => App\Models\User::class
		]
	]
```

### User Model Requirements

By default, Laravel treats the `id` column as an integer while the returned id
is closer to a uri. It also includes some columns which we don't use by default.

Given the default:

```php
	protected $fillable = [
		'name',
		'email',
		'password',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];
```

You can remove:

* fillable: password
* hidden: password
* casts: the entire block

### Using a column other than `id`

Authkit respects the Authenticatable contract along with the rest of the built-in
authentication system. The name of the column to use as the 'id' for the purposes
of logins and other user lookups can be specified by overriding the
`getAuthIdentifierName()` method. For example:

```php
	/**
	 * Get the name of the unique identifier for the user.
	 *
	 * @return string
	 */
	public function getAuthIdentifierName()
	{
		return 'sso_id';
	}
```

The default migrations are not aware of this. You will need to either not use
the built-in migration or add subsequent migrations to make the necessary changes
for your desired column.


## Customizing the Configuration

As far as possible you should attempt to work around the existing configuration.
The more standardized every application is, the easier it is for everyone to
understand and work on.

If it's absolutely required to change the behaviour, you can generate the default
configuration by running:

```
$ php artisan vendor:publish --provider="Dhtech\Auth\Providers\AuthkitServiceProvider" --tag="config"
```

You can then adjust the configuration by editing the `config/larauthkit.php` file
inside your project. All values are documented within this file.

