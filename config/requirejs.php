<?php

return [

	'baseUrl' => '/js/lib/',

	'paths' => [
		'jquery' => [
			'jquery-2.1.4.min',
			'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min',
		],
		'bootstrap' => [
			'bootstrap-3.3.6.min',
			'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min',
		],
		'selectize' => [
			'selectize-0.12.1.min',
			'https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.1/js/standalone/selectize.min',
		]
	],

	'shim' => [
		'bootstrap' => [
			'deps' => ['jquery']
		],
		'selectize' => [
			'deps' => ['jquery']
		]
	]

];
