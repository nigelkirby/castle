@extends('layout.master')

@section('breadcrumbs')
<ol class="breadcrumb">
	<li><a href="{{ route('home.index') }}">Home</a></li>
	<li class="active">Attachments</li>
</ol>
@endsection

@section('content')
<div class="container item-index attachments-index">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">

			<header class="page-header">
				<h1>
					Attachments
					<small>
						&times;{{ $attachments->total() }}
					</small>
				</h1>
			</header>

			<section>
				@include('attachments.partials.list', [
					'attachments' => $attachments,
					'metadata' => true,
					'fullPath' => true,
					'edit' => true,
					'destroy' => true,
				])
			</section>

		</div>
	</div>
</div>
@endsection
