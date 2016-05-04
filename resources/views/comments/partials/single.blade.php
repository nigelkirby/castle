<li class="list-group-item">
	<header style="font-size: 87.5%">
		<div class="row action-bar">
			<div class="col-xs-6 text-left">
				<form class="form-inline" method="post" action="{{ route('whiteboard.comments.vote', ['discussion' => $item->discussion->url, 'comment' => $item->url]) }}">
					{!! csrf_field() !!}
					<?php $vote = $item->voters->has(auth()->user()->id) ? $item->voters->get(auth()->user()->id) : false ?>
					@if ($vote == 1)
					<button type="submit" class="btn btn-sm btn-info active" value="none" name="vote" title="Rescind vote">
						<span class="glyphicon glyphicon-chevron-up"></span>
						<span class="sr-only">Rescind vote</span>
					@else
					<button type="submit" class="btn btn-sm btn-info" value="up" name="vote" title="Vote up">
						<span class="glyphicon glyphicon-chevron-up"></span>
						<span class="sr-only">Vote up</span>
					@endif
					</button>
					@if ($vote == -1)
					<button type="submit" class="btn btn-sm btn-info active" value="none" name="vote" title="Rescind vote">
						<span class="glyphicon glyphicon-chevron-down"></span>
						<span class="sr-only">Rescind vote</span>
					@else
					<button type="submit" class="btn btn-sm btn-info" value="down" name="vote" title="Vote down">
						<span class="glyphicon glyphicon-chevron-down"></span>
						<span class="sr-only">Vote down</span>
					@endif
					</button>
					<span class="item-score">
						<strong style="padding-left: 9px; display: inline-block;" data-comment-score="{{ $item->score or 0 }}">
							{{ $item->score or 0 }}
						</strong>
					</span>
				</form>
			</div>
			<div class="col-xs-6 text-right">
				@include('layout.common.action-bar', [
				'noContainer' => true,
				'editPermission' => ['manage', $item],
				'editRoute' => route('whiteboard.comments.edit', ['discussion' => $item->discussion->url, 'comment' => $item->url]),
				'deletePermission' => ['delete', $item],
				'deleteRoute' => route('whiteboard.comments.destroy', ['discussion' => $item->discussion->url, 'comment' => $item->url])
			])
			</div>
		</div>
		<div>
			<ul class="list-inline">
				<li>
					<a href="{{ route('users.show', $item->author->url) }}">
						<strong>
							{{ $item->author->name }}
						</strong>
					</a>
				</li>
				<li class="text-muted">
					<time datetime="{{ $item->created_at->format('c') }}" title="{{ $item->created_at }}">
						{{ Carbon\Carbon::parse($item->created_at)->diffForHumans() }}
					</time>
				</li>
				@if (!(isset($hideContext) and $hideContext))
				<li class="text-muted">
					on
					<a href="{{ route('whiteboard.show', $item->discussion->url) }}">
						{{ $item->discussion->name }}
					</a>
				</li>
				@endif
			</ul>
		</div>
	</header>
	<section id="comment-{{ $item->url }}">
		{!! $item->toHtml() !!}
	</section>
</li>
