<!doctype html>
<html lang="{{ config('app.locale') }}">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		@stack('meta')

		<title>@yield('title', 'Castle')</title>

		<link href="{{ asset('images/castle.png') }}" rel="icon" type="image/png">

		<link href="https://fonts.googleapis.com/css?family=Lato:400,400italic,700,700italic|Oxygen+Mono:400,700" rel="stylesheet" type="text/css">
		@stack('styles')
		<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	</head>
	<body>

		<div class="castle-layout">
			@include('layout.common.navigation')
			@include('layout.common.messages')

			@yield('content')

			@include('layout.common.footer')
		</div>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js" type="text/javascript"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>

		@if(Route::is('*.create') or Route::is('*.edit'))
		<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.1/js/standalone/selectize.min.js" type="text/javascript"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/markdown.js/0.5.0/markdown.min.js" type="text/javascript"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-markdown/2.10.0/js/bootstrap-markdown.min.js" type="text/javascript"></script>
		@endif

		@stack('scripts')

		@if(Auth::check())
		<script src="{{ asset('js/app.js') }}" type="text/javascript"></script>
		<script src="{{ route('home.castlejs') }}?via={{ urlencode(Route::currentRouteName()) }}" type="text/javascript"></script>
		@endif

	</body>
</html>
