<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->string('email')->unique();
			$table->string('phone')->nullable();
			$table->string('password');
			$table->rememberToken();
			$table->timestamps();
			$table->softDeletes();
		});

		Schema::create('oauth_users', function (Blueprint $table) {
			$table->integer('user_id')->unsigned();
			$table->string('provider_name');
			$table->string('provider_id')->nullable();
			$table->string('provider_token')->nullable();
			$table->json('provider_metadata')->nullable();

			$table->primary(['user_id', 'provider_name']);
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}
}
