@extends('layout.master')

@section('breadcrumbs')
<ol class="breadcrumb">
	<li><a href="{{ route('home.index') }}">Home</a></li>
	<li><a href="{{ route('clients.index') }}">Clients</a></li>
	<li><a href="{{ route('clients.show', $resource->client->url) }}">{{ $resource->client->name }}</a></li>
	<li class="active">{{ $resource->name }}</li>
</ol>
@endsection

@section('content')
<div class="container item-viewer resource-viewer">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">

			<header class="well">
				<h1>{{ $resource->name }}</h1>
				@include('tags.partials.bar', [
					'clients' => $resource->client,
					'tags' => $resource->tags,
					'linkify' => true
				])
				<span class="text-muted">
					{{ $resource->type->name }}
				</span>
				@include('layout.common.action-bar', [
					'editPermission' => ['manage', $resource],
					'editRoute' => route('clients.resources.edit', ['client' => $resource->client->url, 'resource' => $resource->url]),
					'deletePermission' => ['delete', $resource],
					'deleteRoute' => route('clients.resources.destroy', ['client' => $resource->client->url, 'resource' => $resource->url]),
					'deleteWarning' => $resource->attachments->count() ?
						'This resource\'s attachments will also be deleted.' :
						null,
				])
			</header>

			<section>
				@if (isset($resource->metadata) and !$resource->metadata->isEmpty())
				<article id="metadata">
					@foreach ($resource->metadata as $key => $value)
					<div class="row" style="margin-bottom: 4px;">
						<div class="col-sm-4 text-right">
							<input type="text" class="form-control mono-text" value="{{ $key }}" readonly="readonly">
						</div>
						<div class="col-sm-8 text-left">
							<textarea class="form-control mono-text" readonly="readonly">{{ $value }}</textarea>
						</div>
					</div>
					@endforeach
				</article>
				@endif
				@if (isset($resource->attachments) and !$resource->attachments->isEmpty())
				<article id="attachments">
					<h3>
						Attachments
						<small>&times;{{ $resource->attachments->count() }}</small>
					</h3>
					@include('attachments.partials.list', ['attachments' => $resource->attachments])
				</article>
				@endif
			</section>

		</div>
	</div>
</div>
@endsection
