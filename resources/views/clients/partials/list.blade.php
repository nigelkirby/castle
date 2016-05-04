@if (isset($clients) and !$clients->isEmpty())
	<div class="list-group clients-list-group">
	@foreach ($clients as $client)
		@include('clients.partials.single', ['item' => $client])
	@endforeach
	</div>
@endif
