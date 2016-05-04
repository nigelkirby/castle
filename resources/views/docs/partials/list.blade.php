@if (isset($docs) and !$docs->isEmpty())
	<div class="list-group docs-list-group">
	@foreach ($docs as $doc)
		@include('docs.partials.single', ['item' => $doc])
	@endforeach
	</div>
@endif
