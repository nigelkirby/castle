<a class="list-group-item" href="{{ route('tags.show', $item->url) }}">
	<div class="media">
		<div class="media-body media-middle">
			<h4 class="media-heading list-group-item-heading">
				<span class="label label-primary" data-color="{{ $item->color }}">
					{{ $item->name }}
				</span>
			</h4>
		</div>
	</div>
</a>
