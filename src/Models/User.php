<?php

declare(strict_types=1);

namespace Dhtech\Auth\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Sample user model compatible with larauthkit
 */
class User extends Authenticatable
{
	/** @var bool */
	public $incrementing = false;

	/** @var string */
	protected $keyType = 'string';

	/** @var string[] */
	protected $fillable = [
		'name',
		'email'
	];

	/** @var string[] */
	protected $hidden = [
		'remember_token'
	];
}
