@if (isset($discussions) and !$discussions->isEmpty())
	<div class="list-group discussions-list-group">
	@foreach ($discussions as $discussion)
		@include('discussions.partials.single', ['item' => $discussion])
	@endforeach
	</div>
@endif
