@extends('layout.master')

@section('breadcrumbs')
<ol class="breadcrumb">
    <li><a href="{{ route('home.index') }}">Home</a></li>
    <li><a href="{{ route('whiteboard.index') }}">Whiteboard</a></li>
    <li class="active">{{ $discussion->name }}</li>
</ol>
@endsection

@section('content')
<div class="container item-viewer document-viewer">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">

			<header class="well">
				<h1>{{ $discussion->name }}</h1>

				@include('tags.partials.bar', [
					'tags' => $discussion->tags,
					'linkify' => true
				])

				<div class="row">
					<div class="col-sm-8 updated text-muted">
						{{ $discussion->updated_at > $discussion->created_at ? 'Updated' : 'Created' }}
						<time datetime="{{ $discussion->updated_at->format('c') }}" title="{{ Carbon\Carbon::parse($discussion->updated_at)->toDateTimeString() }}">
							{{ Carbon\Carbon::parse($discussion->updated_at)->diffForHumans() }}
						</time>
						by
						@if ($discussion->updatedBy)
						<a href="{{ route('users.show', $discussion->updatedBy->url) }}">
							{{ $discussion->updatedBy->name }}
						</a>
						@elseif ($discussion->createdBy)
						<a href="{{ route('users.show', $discussion->createdBy->url) }}">
							{{ $discussion->createdBy->name }}
						</a>
						@else
						<span class="text-muted">
							(deleted)
						</span>
						@endif
					</div>
					<div class="col-sm-4">
						{{-- $discussion->status or 'unspecified' --}}
					</div>
				</div>

				<nav class="row action-bar">
					<div class="col-sm-6 action-bar-left">
						<form class="form-inline" method="post" action="{{ route('whiteboard.vote', $discussion->url) }}">
							{!! csrf_field() !!}
							<?php $vote = $discussion->voters->has(auth()->user()->id) ? $discussion->voters->get(auth()->user()->id) : false ?>
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
							<span class="discussion-score">
								<strong style="padding-left: 9px; display: inline-block;" data-discussion-score="{{ $discussion->score or 0 }}">
									{{ $discussion->score or 0 }}
								</strong>
							</span>
						</form>
					</div>
					<div class="col-sm-6 action-bar-right">
						@include('layout.common.action-bar', [
							'noContainer' => true,
							'editPermission' => ['manage', $discussion],
							'editRoute' => route('whiteboard.edit', $discussion->url),
							'deletePermission' => ['delete', $discussion],
							'deleteRoute' => route('whiteboard.destroy', $discussion->url),
							'deleteWarning' => (isset($discussion->attachments) and !$discussion->attachments->isEmpty()) ?
								'This discussion\'s attachments will also be deleted.' :
								null,
						])
					</div>
				</nav>
			</header>

			<section class="discussion-content">
				{!! $discussion->toHtml() !!}
			</section>

			<section class="discussion-extras">
				<nav class="action-bar border-top">
					<ul class="nav nav-pills">
						@if (isset($discussion->attachments) and !$discussion->attachments->isEmpty())
						<li>
							<a href="#attachments" data-toggle="tab">
								Attachments
								<small class="text-muted">
									&times;{{ $discussion->attachments->count() }}
								</small>
							</a>
						</li>
						@endif
					</ul>
				</nav>
				<div class="tab-content">
					@if (isset($discussion->attachments) and !$discussion->attachments->isEmpty())
					<article id="attachments" class="tab-pane">
						@include('attachments.partials.list', ['attachments' => $discussion->attachments])
					</article>
					@endif
				</div>
			</section>

			@can ('participate', $discussion)
			@if (!$discussion->comments->isEmpty())
			<section class="discussion-comments">
				<h2 class="page-header">
					Comments
					<small>
						&times;{{ $discussion->comments->count() }}
					</small>
				</h2>
				@include ('comments.partials.list', ['comments' => $discussion->comments->sortBy('created_at')])
			</section>
			@endif
			@endcan

			@can ('participate', $discussion)
			<section class="discussion-create-comment item-editor">
				<form class="form-horizontal" method="post" action="{{ route('whiteboard.comments.store', $discussion->url) }}">
					{!! csrf_field() !!}

					<fieldset>

						<div class="form-group{{ $errors->has('name') ? ' has-error has-feedback' : '' }}">
							<label class="sr-only" for="content">
								Comment
								<br class="hidden-xs">
								<img src="{{ asset('images/markdown.svg') }}" type="image/svg+xml" alt="Markdown" style="height: 18px; width: auto;" />
							</label>
							<div class="col-sm-10">

								<textarea class="form-control mono-text" name="content" id="content" rows="2" placeholder="Add a comment&hellip;">{{ old('content') }}</textarea>

								@if ($errors->has('content'))
								<span class="help-block">
									<strong>{{ $errors->first('content') }}</strong>
								</span>
								@endif
							</div>
							<div class="col-sm-2">

								<button type="submit" class="btn btn-success">
									Send
								</button>

							</div>
						</div>

					</fieldset>
				</form>
			</section>
			@endcan

		</div>
	</div>
</div>
@endsection
