<?php

namespace Castle\Http\Controllers;
use Castle\Discussion;
use Castle\DiscussionStatus;
use Castle\Http\Requests;
use Castle\Vote;

use Cache;
use Illuminate\Http\Request;

class DiscussionController extends Controller
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
	public function index(Request $request)
	{
		$this->authorize('view', Discussion::class);

		$discussions = Discussion::with('comments', 'tags', 'votes')
			->orderBy('updated_at', 'desc')
			->paginate(20)
			->appends($request->all());

		return view('discussions.index', ['discussions' => $discussions]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$this->authorize('participate', Discussion::class);

		return view('discussions.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$this->authorize('participate', Discussion::class);

		$this->validate($request, [
			'name' => ['required', 'max:255'],
			'slug' => [
				'required',
				'max:255',
				'alpha_dash',
				'unique:discussions,slug',
				'not_in:create,destroy,edit,prune'
			],
			'content' => ['required'],

			'attachments' => ['array'],
			'uploads' => ['array'],

			'tags' => ['array'],
			'tags.*' => ['exists:tags,id'],
		]);

		$discussion = new Discussion([
			'name' => $request->input('name'),
			'slug' => $request->input('slug'),
			'content' => $request->input('content'),
		]);

		if ($request->hasFile('uploads')) {
			$discussion->attachments = $request->file('uploads');
		}

		$discussion->createdBy()->associate($request->user());

		$discussion->save();

		$discussion->tags()->sync($request->input('tags', []));

		return redirect()->route('whiteboard.show', $discussion->url)
			->with('alert-success', 'Discussion created!');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string  $discussion
	 * @return \Illuminate\Http\Response
	 */
	public function show($discussion)
	{
		$discussion = Discussion::findBySlug($discussion);

		if (!$discussion) {
			return response(view('discussions.404'), 404);
		}

		$this->authorize('view', $discussion);

		$discussion->load('createdBy', 'updatedBy', 'comments', 'comments.author', 'comments.discussion', 'comments.votes', 'tags', 'votes');

		Cache::forever($discussion->cacheKey, $discussion->toHtml());
		foreach ($discussion->comments as $comment) {
			Cache::forever($comment->cacheKey, $comment->toHtml());
		}

		return view('discussions.show', ['discussion' => $discussion]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  string  $discussion
	 * @return \Illuminate\Http\Response
	 */
	public function edit($discussion)
	{
		$discussion = Discussion::findBySlug($discussion);

		if (!$discussion) {
			return response(view('discussions.404'), 404);
		}

		$this->authorize('manage', $discussion);

		$discussion->load('tags');

		return view('discussions.edit', ['discussion' => $discussion]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  string  $discussion
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $discussion)
	{
		$discussion = Discussion::findBySlug($discussion);

		if (!$discussion) {
			return response(view('docs.404'), 404);
		}

		$this->authorize('manage', $discussion);

		$this->validate($request, [
			'name' => ['required', 'max:255'],
			'slug' => [
				'required',
				'max:255',
				'alpha_dash',
				'unique:discussions,slug,'.$discussion->slug.',slug',
				'not_in:create,destroy,edit,prune'
			],
			'content' => ['required'],

			'attachments' => ['array'],
			'uploads' => ['array'],

			'tags' => ['array'],
			'tags.*' => ['exists:tags,id'],
		]);

		$discussion->name = $request->input('name');
		$discussion->slug = $request->input('slug');
		$discussion->content = $request->input('content');

		$attachments = $request->input('attachments', []);

		if ($request->hasFile('uploads')) {
			$uploads = $request->file('uploads');
			$attachments = array_merge($attachments, $uploads);
		}

		$discussion->attachments = $attachments;

		$discussion->updatedBy()->associate($request->user());

		$discussion->save();

		$discussion->tags()->sync($request->input('tags', []));

		Cache::forget($discussion->cacheKey);

		return redirect()->route('whiteboard.show', $discussion->url)
			->with('alert-success', 'Discussion updated!');
	}

	/**
	 * Increases or decreases the discussion score.
	 *
	 * @param  string  $discussion
	 * @return \Illuminate\Http\Response if JSON, \Illuminate\Http\Redirect otherwise
	 */
	public function vote(Request $request, $discussion)
	{
		$discussion = Discussion::findBySlug($discussion);

		if (!$discussion) {
			return response(view('discussions.404'), 404);
		}

		$this->authorize('participate', $discussion);

		$vote = Vote::voted($request->user(), $discussion);

		if ($vote->exists()) {
			$vote = $vote->first();
		} else {
			$vote = new Vote;
			$vote->user()->associate($request->user());
			$vote->owner()->associate($discussion);
		}

		$voteValues = [
			'up' => 1,
			'down' => -1,
			'none' => 0,
		];

		$value = strtr($request->input('vote'), $voteValues);
		$vote->value = $value;

		$vote->save();

		return $request->wantsJson() ?
			response()->json([
				'discussion' => $discussion->url,
				'vote' => $vote,
				'score' => $discussion->score
			]) :
			redirect()->route('whiteboard.show', $discussion->url)
				->with('alert-success', $value ? 'Voted '.array_flip($voteValues)[$value].'!' : 'Vote rescinded!');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  string  $discussion
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($discussion)
	{
		$discussion = Discussion::findBySlug($discussion);

		if (!$discussion) {
			return response(view('discussions.404'), 404);
		}

		$this->authorize('manage', $discussion);

		Cache::forget($discussion->cacheKey);

		$discussion->comments->each(function($comment) {
			Cache::forget($comment->cacheKey);
			$comment->delete();
		});

		$discussion->delete();

		return redirect()->route('whiteboard.index')
			->with('alert-success', 'Discussion deleted!');
	}
}
