

# Larauthkit Setup

How to set up a brand new Laravel project to use Authkit.

## Create Laravel Project

There are several [installation methods available](https://laravel.com/docs/8.x/installation#installation-via-composer).
The most straightforward:

```
$ composer create-project laravel/laravel my-new-project
```

## Fix Dependencies

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

## Install Authkit

In your project's folder:

```
$ composer require dht/larauthkit
```

Laravel will automatically discover the required configuration and service
providers.

## Finish Laravel Setup

Configure your database and perform any other initial configuration/setup required.

## Run Database Migrations

```
$ php artisan vendor:publish --provider="Dht\Auth\Providers\AuthkitServiceProvider" --tag="migrations"
$ php artisan migrate
```

This modifies the default Laravel users table to:

* Change the `id` column to text (to store our SSO user ids)
* Remove the `email_verified_at` column (app no longer performs email verification directly)
* Remove the `password` column (app no longer stores or uses passwords)

## Add SSO Configuration

You can request/obtain a SSO configuration file from ops. It contains all the
necessary information for authenticating against the SSO service in a JSON file.

By default, larauthkit will look for this file in your app at `storage/app/auth.json`.
Place the file there.

### Generating auth.json

If you're running this against your own instance of Keycloak, you can
generate the appropriate configuration by select the correct realm, then
navigating to Clients (left menu) -> Your client -> Installation (top tabs).
Select "Keycloak OIDC JSON" from the dropdown. Put the JSON generated into the
auth.json file.

## Update Your User Model

Laravel requires some extra configuration on the Eloquent model you use for users.
The easiest way to implement these changes is to extend your user model from the
Authkit user model.

Update the default Laravel User model in `app/Models/User.php` replacing:

```php
class User extends Authenticatable
{
```

With:

```php
class User extends \Dht\Auth\Models\User
{
```

and remove all code from within the class.

## Done

You're done!

See [USAGE.md](USAGE.md).
