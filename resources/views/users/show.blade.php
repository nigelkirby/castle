@extends('layout.master')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li><a href="{{ route('home.index') }}">Home</a></li>
    <li><a href="{{ route('users.index') }}">Users</a></li>
    <li class="active">{{ $user->name }}</li>
</ol>
@endsection

@section('content')
<div class="container item-viewer user-viewer">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">

			<header class="well">
				<h1>{{ $user->name }}</h1>
				<ul class="list-unstyled">
					<li>
						<strong>Email address:</strong>
						<a class="user-email" href="mailto:{{ $user->email }}">{{ $user->email }}</a>
					</li>
					@if ($user->phone)
					<li>
						<strong>Phone number:</strong>
						<a href="tel:{{ $user->phone }}">{{ $user->phone }}</a>
					</li>
					@endif
				</ul>
				@include('layout.common.action-bar', [
					'editPermission' => ['manage', $user],
					'editRoute' => route('users.edit', $user->url),
					'deletePermission' => ['delete', $user],
					'deleteRoute' => route('users.destroy', $user->url),
				])
			</header>

		</div>
	</div>
</div>
@endsection
