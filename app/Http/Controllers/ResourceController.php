<?php

namespace Castle\Http\Controllers;
use Castle\Attachment;
use Castle\Client;
use Castle\Http\Requests;
use Castle\Resource;
use Castle\ResourceType;

use Illuminate\Http\Request;
use Validator;

class ResourceController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index($client)
	{
		return redirect()->route('clients.show', $client);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create($client)
	{
		$this->authorize('manage', Resource::class);

		$client = Client::findBySlug($client);

		return view('resources.create', ['client' => $client]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request, $client)
	{
		$this->authorize('manage', Resource::class);

		$client = Client::findBySlug($client);

		$this->validate($request, [
			'name' => ['required', 'max:255'],
			'slug' => [
				'required',
				'max:255',
				'alpha_dash',
				'unique:resources,slug,NULL,slug,client_id,'.$client->id,
				'not_in:create,destroy,edit,prune'
			],
			// 'metadata' => ['array'],
			// 'metadata.*' => ['distinct'],

			'attachments' => ['array'],
			'uploads' => ['array'],

			'type' => ['required'],

			'client' => ['required', 'exists:clients,id'],

			'tags' => ['array'],
			'tags.*' => ['exists:tags,id'],
		]);

		$resource = new Resource([
			'name' => $request->input('name'),
			'slug' => $request->input('slug'),
			'metadata' => $request->input('metadata', []),
		]);

		if ($request->hasFile('uploads')) {
			$resource->attachments = $request->file('uploads');
		}

		$type = ResourceType::firstOrNew([
			'slug' => str_slug($request->input('type'))
		]);

		if (!$type->exists) {
			$type->name = $request->input('type');
			$type->save();
		}

		$resource->type()->associate($type);
		$resource->client()->associate($client);

		$resource->save();

		$resource->tags()->sync($request->input('tags', []));

		return redirect()->route('clients.show', $client->url)
			->with('alert-success', 'Resource created!');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string  $resource
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, $client, $resource)
	{
		$resource = Resource::findBySlug($client, $resource);

		if (!$resource) {
			return response(view('resources.404'), 404);
		}

		$this->authorize('view', $resource);

		$resource->load('client', 'tags', 'type');

		return view('resources.show', ['resource' => $resource]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  string  $resource
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, $client, $resource)
	{
		$resource = Resource::findBySlug($client, $resource);

		if (!$resource) {
			return response(view('resources.404'), 404);
		}

		$this->authorize('manage', $resource);

		$resource->load('client', 'tags', 'type');

		return view('resources.edit', ['client' => $resource->client, 'resource' => $resource]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  string  $resource
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $client, $resource)
	{
		$resource = Resource::findBySlug($client, $resource);

		if (!$resource) {
			return response(view('resources.404'), 404);
		}

		$resource->load('client');
		$client = $resource->client;

		$this->authorize('manage', $resource);

		$this->validate($request, [
			'name' => ['required', 'max:255'],
			'slug' => [
				'required',
				'max:255',
				'alpha_dash',
				'unique:resources,slug,'.$request->input('slug', $resource->slug).',slug,client_id,'.$resource->client->id,
				'not_in:create,destroy,edit,prune'
			],
			// 'metadata' => ['array'],

			'attachments' => ['array'],
			'uploads' => ['array'],

			'type' => ['required'],

			'client' => ['required', 'exists:clients,id'],

			'tags' => ['array'],
			'tags.*' => ['exists:tags,id'],
		]);

		if (($newClient = $request->input('client', $resource->client->id)) != $resource->client->id) {
			$client = Client::find($newClient);
			$resource->client()->associate($client);
		}

		$resource->name = $request->input('name');
		$resource->slug = $request->input('slug');
		$resource->metadata = $request->input('metadata', []);

		$attachments = $request->input('attachments', []);

		if ($request->hasFile('uploads')) {
			$uploads = $request->file('uploads');
			$attachments = array_merge($attachments, $uploads);
		}

		$resource->attachments = $attachments;

		// set type
		if (!($type = ResourceType::findBySlug($request->input('type')))) {
			$type = ResourceType::create([
				'name' => $request->input('type'),
				'slug' => str_slug($request->input('type')),
			]);
		}

		$resource->type()->associate($type);

		$resource->save();

		$resource->tags()->sync($request->input('tags', []));

		return redirect()->route('clients.resources.show', ['client' => $resource->client->url, 'resource' => $resource->url])
			->with('alert-success', 'Resource updated!');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, $client, $resource)
	{
		$resource = Resource::findBySlug($client, $resource);

		if (!$resource) {
			return response(view('resources.404'), 404);
		}

		$this->authorize('delete', $resource);

		$resource->delete();

		return redirect()->route('clients.show', $client)
			->with('alert-success', 'Resource deleted!');
	}

}
