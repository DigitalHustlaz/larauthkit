<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class larauthkit_update_users_table extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', static function(Blueprint $table) {
			$table->dropColumn('id');
		});
		Schema::table('users', static function(Blueprint $table) {
			$table->string('id')->first()->unique();
			$table->primary('id');
		});
		Schema::table('users', static function(Blueprint $table) {
			$table->dropColumn('email_verified_at');
		});
		Schema::table('users', static function(Blueprint $table) {
			$table->dropColumn('password');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Up causes irreversible data loss. We cannot 'down' it.
	}
}
