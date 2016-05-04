@if (isset($comments) and !$comments->isEmpty())
	<ul class="list-unstyled">
	@foreach ($comments as $comment)
		@include('comments.partials.single', ['item' => $comment, 'hideContext' => true])
	@endforeach
	</ul>
@endif
