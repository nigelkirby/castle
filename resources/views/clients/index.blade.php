@extends('layout.master')

@section('breadcrumbs')
<ol class="breadcrumb">
	<li><a href="{{ route('home.index') }}">Home</a></li>
	<li class="active">Clients</li>
</ol>
@endsection

@section('content')
<div class="container item-index client-index">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">

			<header class="page-header">
				<h1>
					Clients
					<small>
						&times;{{ $clients->total() }}
					</small>
				</h1>
			</header>

			<div class="action-bar row">
				<div class="col-xs-6 action-bar-left">
					@can('manage', Castle\Client::class)
					<a class="btn btn-success" href="{{ route('clients.create') }}">
						Create client
					</a>
					@endcan
				</div>
				<div class="col-xs-6 action-bar-right">
					<form class="form-inline" action="{{ route('clients.index') }}" method="get">
						<label for="filter" class="sr-only">Filter</label>
						<div class="btn-group">
							<button type="submit" name="order" value="asc" class="btn {{ (Request::get('order', 'asc') == 'asc') ? 'btn-info' : 'btn-default' }}">
								<span class="glyphicon glyphicon-sort-by-alphabet"></span>
								<span class="sr-only">Sort A to Z</span>
							</button>
							<button type="submit" name="order" value="desc" class="btn {{ (Request::get('order', '') == 'desc') ? 'btn-info' : 'btn-default' }}">
								<span class="glyphicon glyphicon-sort-by-alphabet-alt"></span>
								<span class="sr-only">Sort Z to A</span>
							</button>
						</div>
					</form>
				</div>
			</div>

			@if ($clients->isEmpty())
			<div class="alert alert-info">
				@if (Request::input('filter', false))
				<span>No clients match your filter.</span>
				@else
				<span>There are no clients.</span>
				@endif
			</div>
			@else
			@include('clients.partials.list', ['clients' => $clients])
			<nav class="text-center">
				{!! $clients->links() !!}
			</nav>
			@endif

		</div>
	</div>
</div>
@endsection
