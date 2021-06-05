
### Package Name: SSO Laravel with Keycloak 
## Author: Pasha Zahari
## Website: https://digitalhustlaz.com
### Contact: support@digitalhustlaz.com
## License: MIT License


# Migrating to Authkit

When you already have an existing project with an existing set of users.

## Install Authkit

### Fix Dependencies

Unfortunately, one of the transitive dependencies relies on an older version of
GuzzleHttp than Laravel defaults to. The most straightforward resolution to this
at the moment is to update your project's `composer.json`. Find the line that
looks similar to:

```js
	"require": {
		[...]
		"guzzlehttp/guzzle": "^7.0.1",
		[...]
	}
```

And replace it with:

```js
	"require": {
		[...]
		"guzzlehttp/guzzle": "^6.2.0",
		[...]
	}
```

Then run:

```
$ composer update
```

This will hopefully be resolved soon with an update upstream.

### Install

In your project's folder:

```
$ composer require dht/larauthkit
```

Laravel will automatically discover the required configuration and service
providers.


## Configure Authkit

### Add SSO Configuration

You can request/obtain a SSO configuration file from ops. It contains all the
necessary information for authenticating against the SSO service in a JSON file.

By default, larauthkit will look for this file in your app at `storage/app/auth.json`.
Place the file there.

#### Generating auth.json

If you're running this against your own instance of Keycloak, you can
generate the appropriate configuration by select the correct realm, then
navigating to Clients (left menu) -> Your client -> Installation (top tabs).
Select "Keycloak OIDC JSON" from the dropdown. Put the JSON generated into the
auth.json file.


## Modify Database

Your `users` table will require, at minimum, a column to store the SSO user id.

Create a migration to add the column, for the sake of example we will use `sso_id`
here.

You may also choose to remove the columns that are no longer required such as
`password` or mark them nullable to simplify the migration.


## Modify User Model Authentication Identifier

Configure your user model to use the new `sso_id` column for logging in users.

You can do this by overriding the `getAuthIdentifierName()` method on the user
model. For example:

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

## User Migration Listener

When users log in from the SSO service for the first time, larauthkit will attempt
to look them up by `sso_id`. If no match is found, a new user model will be
created and a UserRegistration event fired _before_ the model is saved.

During this event, you can return a different instance of your user model
instead. You can implement your own logic to lookup an existing user and merge
the SSO user and return that model instead.

### Sample Listener

This sample listener will attempt to match to existing user records by email.
If a match is found, the `sso_id` will be set, and the `name` updated to the
one passed back from the SSO service.

```php
<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use \Dht\Auth\Events\UserRegistration;

class UserMigrationListener
{
	/**
	 * Handle the event.
	 *
	 * @param  UserRegistration  $event
	 * @return void
	 */
	public function handle(UserRegistration $event)
	{
		$user = $event->user;

		$existing = \App\Models\User::where('email', $user->email)->first();
		if (isset($existing))
		{
			$existing->sso_id = $user->sso_id;
			$existing->name = $user->name;
			$existing->save();
			return $existing;
		}
		// Only required if we didn't drop or make the password column
		// nullable. When new users login that _don't_ already exist in
		// our database larauthkit will try and create a new record for them.
		// If password exists and isn't nullable, a value will be required
		// on the model to save it.
		else
		{
			$user->password = '***sso***';
		}
	}
}
```

### Register Listener

In your `app/Providers/EventServiceProvider.php`, register your listener for
the UserRegistration event. For example:

```php
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
		\Dht\Auth\Events\UserRegistration::class => [
			\App\Listeners\UserMigrationListener::class
		]
    ];
}
```

## Done

At this point you should be able to log in through the SSO service and have
users automatically matched to and updating your existing user records.

See [USAGE.md](USAGE.md) for details.
