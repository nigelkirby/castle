@if (isset($users) and !$users->isEmpty())
	<div class="list-group users-list-group">
	@foreach ($users as $user)
		@include('users.partials.single', ['item' => $user])
	@endforeach
	</div>
@endif
