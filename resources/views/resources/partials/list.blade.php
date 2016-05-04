@if (isset($resources) and !$resources->isEmpty())
	<div class="list-group resources-list-group">
	@foreach ($resources as $resource)
		@include('resources.partials.single', ['item' => $resource])
	@endforeach
	</div>
@endif
