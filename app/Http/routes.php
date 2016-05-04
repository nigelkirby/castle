<?php

// Authentication routes
Route::group(['middleware' => 'web'], function () {

	// Login and logout
	Route::get('login', [
		'as' => 'auth.login',
		'uses' => Auth\AuthController::class.'@showLoginForm'
	]);
	Route::post('login', [
		'as' => 'auth.login.do',
		'uses' => Auth\AuthController::class.'@login'
	]);
	Route::get('logout', [
		'as' => 'auth.logout',
		'uses' => Auth\AuthController::class.'@logout'
	]);

	// Password resets
	Route::get('password/reset/{token?}', [
		'as' => 'auth.reset',
		'uses' => Auth\PasswordController::class.'@showResetForm'
	]);
	Route::post('password/email', [
		'as' => 'auth.reset.create',
		'uses' => Auth\PasswordController::class.'@sendResetLinkEmail'
	]);
	Route::post('password/reset', [
		'as' => 'auth.reset.do',
		'uses' => Auth\PasswordController::class.'@reset'
	]);

	// OAuth
	Route::get('login/{provider}', [
		'as' => 'auth.oauth',
		'uses' => Auth\AuthController::class.'@redirectToProvider'
	]);
	Route::get('login/{provider}/callback', [
		'as' => 'auth.oauth.callback',
		'uses' => Auth\AuthController::class.'@handleProviderCallback'
	]);

});

// Resource routes
Route::group(['middleware' => 'web'], function () {

	Route::singularResourceParameters();

	// ClientController and ResourceController
	Route::resource('clients', ClientController::class);
	Route::resource('clients.resources', ResourceController::class);

	// AttachmentController
	Route::get('attachments', [
		'as' => 'attachments.index',
		'uses' => AttachmentController::class.'@index'
	]);
	Route::get('attachments/{attachment}', [
		'as' => 'attachments.show',
		'uses' => AttachmentController::class.'@download',
		'where' => ['attachment' => '(.*)']
	]);
	Route::delete('attachments/{attachment}', [
		'as' => 'attachments.destroy',
		'uses' => AttachmentController::class.'@destroy',
		'where' => ['attachment' => '(.*)']
	]);

	// DocsController
	Route::resource('docs', DocsController::class);

	// TagController
	Route::delete('tags/prune', [
		'as' => 'tags.prune',
		'uses' => TagController::class.'@prune'
	]);
	Route::resource('tags', TagController::class);

	// DiscussionController and CommentController
	Route::post('whiteboard/{discussion}/vote', [
		'as' => 'whiteboard.vote',
		'uses' => DiscussionController::class.'@vote'
	]);
	Route::resource('whiteboard', DiscussionController::class);

	Route::post('whiteboard/{discussion}/comments/{comment}/vote', [
		'as' => 'whiteboard.comments.vote',
		'uses' => CommentController::class.'@vote'
	]);
	Route::resource('whiteboard.comments', CommentController::class);

	// UserController
	Route::get('users/{user}/edit/password', [
		'as' => 'users.edit.password',
		'uses' => UserController::class.'@password'
	]);
	Route::post('users/{user}/edit/password', [
		'as' => 'users.update.password',
		'uses' => UserController::class.'@editPassword'
	]);
	Route::resource('users', UserController::class);

	// HomeController
	Route::get('castle.js/{via?}', [
		'as' => 'home.castlejs',
		'uses' => HomeController::class.'@castlejs'
	]);
	Route::get('search/{term?}', [
		'as' => 'home.search',
		'uses' => HomeController::class.'@search'
	]);
	Route::get('/', [
		'as' => 'home.index',
		'uses' => HomeController::class.'@home'
	]);

});
