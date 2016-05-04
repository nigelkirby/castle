<a class="list-group-item" href="{{ route('whiteboard.show', $item->url) }}">
	<div class="media">
		<div class="media-left media-middle">
			<strong class="discussion-item-score">
				{{ $item->score > 0 ? '+'.$item->score : $item->score }}
			</strong>
		</div>
		<div class="media-body media-middle">
			<h4 class="list-group-item-heading">
				{{ $item->name }}
				@if (isset($item->attachments) and !$item->attachments->isEmpty())
					<small class="text-muted pull-right">
						<span class="glyphicon glyphicon-paperclip"></span>
						<span class="sr-only">Has attachments</span>
					</small>
				@endif
			</h4>
			<div class="tag-bar">
				<span class="label label-primary">
					<span class="glyphicon glyphicon-comment"></span>
					<span class="sr-only">Comments</span>
					{{ $item->comments->count() }}
				</span>
				@include('tags.partials.bar', [
					'noContainer' => true,
					'clients' => (isset($hideClient) and $hideClient) ?
						null :
						$item->clients,
					'tags' => (isset($hideTags) and $hideTags) ?
						null :
						$item->tags
				])
			</div>
		</div>
	</div>
</a>
