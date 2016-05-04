@extends('layout.master')

@section('breadcrumbs')
<ol class="breadcrumb">
	<li><a href="{{ route('home.index') }}">Home</a></li>
	<li><a href="{{ route('whiteboard.index') }}">Whiteboard</a></li>
	<li class="active">Create discussion</li>
</ol>
@endsection

@section('content')
<div class="container item-editor discussion-editor">

	<form class="form-horizontal" method="post" action="{{ route('whiteboard.store') }}" enctype="multipart/form-data">
		{!! csrf_field() !!}

		<fieldset>

			<div class="form-group{{ $errors->has('name') ? ' has-error has-feedback' : '' }}">
				<label class="col-md-2 control-label" for="name">Name</label>

				<div class="col-md-8">
					<input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}">
					@if ($errors->has('name'))
					<span class="help-block">
						<strong>{{ $errors->first('name') }}</strong>
					</span>
					@endif
				</div>
			</div>

			<div class="form-group{{ $errors->has('slug') ? ' has-error has-feedback' : '' }}">
				<label class="col-md-2 control-label" for="slug">Slug</label>

				<div class="col-md-8">
					<input type="text" class="form-control" name="slug" id="slug" value="{{ old('slug') }}" data-slug="name">
					@if ($errors->has('slug'))
					<span class="help-block">
						<strong>{{ $errors->first('slug') }}</strong>
					</span>
					@endif
				</div>
			</div>

			<div class="form-group{{ $errors->has('content') ? ' has-error has-feedback' : '' }}">
				<label class="col-md-2 control-label" for="content">
					Content
					<br class="hidden-xs hidden-sm">
					<img src="{{ asset('images/markdown.svg') }}" type="image/svg+xml" alt="Markdown" style="height: 18px; width: auto;" />
				</label>

				<div class="col-md-8">
					<textarea class="form-control mono-text" name="content" id="content" rows="16" data-provide="markdown">{{ old('content') }}</textarea>
					@if ($errors->has('content'))
					<span class="help-block">
						<strong>{{ $errors->first('content') }}</strong>
					</span>
					@endif
				</div>

			</div>

			<div class="form-group{{ $errors->has('tags') ? ' has-error has-feedback' : '' }}">
				<label class="col-md-2 control-label" for="tags">Tags</label>
				<div class="col-md-8">

					<?php $oldTags = collect(old('tags')) ?>
					<select multiple="multiple" class="form-control taggable" name="tags[]" id="tags" data-placeholder="No tags">
						@foreach (Castle\Tag::all() as $tag)
							<?php $selectedTag = $oldTags->contains($tag->id) ? ' selected="selected"' : '' ?>
							<option value="{{ $tag->id }}"{!! $selectedTag !!} data-color="{{ $tag->color }}">
								{{ $tag->name }}
							</option>
						@endforeach
					</select>

					@if ($errors->has('tags'))
					<span class="help-block">
						<strong>{{ $errors->first('tags') }}</strong>
					</span>
					@endif
				</div>
			</div>

			<div class="form-group{{ $errors->has('attachments') ? ' has-error has-feedback' : '' }}">
				<label class="col-md-2 control-label" for="attachments">Attachments</label>
				<div class="col-md-8">

					@include('attachments.partials.editor', ['attachments' => old('attachments')])

					@if ($errors->has('attachments'))
					<span class="help-block">
						<strong>{{ $errors->first('attachments') }}</strong>
					</span>
					@endif
				</div>
			</div>

			<div class="form-group">
				<div class="col-md-8 col-md-offset-2">
					<button type="submit" class="btn btn-primary">Create discussion</button>
					<a class="btn btn-default" href="{{ route('whiteboard.index') }}">Cancel</a>
				</div>
			</div>

		</fieldset>

	</form>

</div>
@endsection
