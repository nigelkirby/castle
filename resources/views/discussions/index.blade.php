@extends('layout.master')

@section('breadcrumbs')
<ol class="breadcrumb">
	<li><a href="{{ route('home.index') }}">Home</a></li>
	<li class="active">Whiteboard</li>
</ol>
@endsection

@section('content')
<div class="container item-index discussion-index">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">

			<header class="page-header">
				<h1>
					Whiteboard
					<small>
						&times;{{ $discussions->total() }}
					</small>
				</h1>
			</header>

			@can('participate', Castle\Discussion::class)
			<div class="action-bar">
				<a class="btn btn-success" href="{{ route('whiteboard.create') }}">
					Create discussion
				</a>
			</div>
			@endcan

			@if ($discussions->isEmpty())
			<div class="alert alert-info">
				@if (Request::input('filter', false))
				<span>No discussions match your filter.</span>
				@else
				<span>There are no discussions.</span>
				@endif
			</div>
			@else
			@include('discussions.partials.list', ['discussions' => $discussions->sortByDesc('score')])
			<nav class="text-center">
				{!! $discussions->links() !!}
			</nav>
			@endif

		</div>
	</div>
</div>
@endsection
