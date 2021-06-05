<?php

declare(strict_types=1);

namespace Dhtech\Auth\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event providing a user model as context
 */
abstract class UserEvent
{
	use Dispatchable;
	use SerializesModels;

	/**
	 * User that this event refers to
	 *
	 * @var mixed
	 */
	public $user;

	/**
	 * Initialize new event
	 *
	 * @param mixed $user
	 */
	public function __construct($user)
	{
		$this->user = $user;
	}
}
