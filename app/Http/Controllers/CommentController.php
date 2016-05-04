<?php

namespace Castle\Http\Controllers;
use Castle\Comment;
use Castle\Discussion;
use Castle\Http\Requests;
use Castle\Vote;

use Cache;
use Illuminate\Http\Request;

class CommentController extends Controller
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
	 * @param  string  $discussion
	 * @return \Illuminate\Http\Response
	 */
	public function index($discussion)
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param  string  $discussion
	 * @return \Illuminate\Http\Response
	 */
	public function create($discussion)
	{
		$discussion = Discussion::findBySlug($discussion);

		if (!$discussion) {
			return response(view('discussions.404'), 404);
		}

		$this->authorize('participate', Comment::class);

		return view('comments.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  string  $discussion
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request, $discussion)
	{
		$discussion = Discussion::findBySlug($discussion);

		if (!$discussion) {
			return response(view('discussions.404'), 404);
		}

		$this->authorize('participate', Comment::class);

		$this->validate($request, [
			'content' => ['required'],
		]);

		$comment = new Comment([
			'content' => $request->input('content')
		]);

		$comment->author()->associate($request->user());
		$comment->discussion()->associate($discussion);

		$comment->save();

		return redirect()->route('whiteboard.show', $discussion->url)
			->with('alert-success', 'Comment created!');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string  $discussion
	 * @param  int  $comment
	 * @return \Illuminate\Http\Response
	 */
	public function show($discussion, $comment)
	{
		$comment = Comment::with('discussion')
			->find($comment);

		if (!$comment) {
			return response(view('comments.404'), 404);
		}

		$this->authorize('view', $comment);

		if (!Cache::has($comment->cacheKey)) {
			Cache::forever($comment->cacheKey, $comment->toHtml());
		}

		return view('comments.show', ['comment' => $comment]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  string  $discussion
	 * @param  int  $comment
	 * @return \Illuminate\Http\Response
	 */
	public function edit($discussion, $comment)
	{
		$comment = Comment::with('discussion')
			->find($comment);

		if (!$comment) {
			return response(view('comments.404'), 404);
		}

		$this->authorize('manage', $comment);

		return view('comments.edit', ['comment' => $comment]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $comment
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $discussion, $comment)
	{
		$comment = Comment::with('author', 'discussion')->find($comment);

		if (!$comment) {
			return response(view('comments.404'), 404);
		}

		$this->authorize('manage', $comment);

		$this->validate($request, [
			'content' => ['required']
		]);

		$comment->content = $request->input('content');

		$comment->save();

		Cache::forget($comment->cacheKey);

		return redirect()->route('whiteboard.show', $comment->discussion->url)
			->with('alert-success', 'Comment updated!');
	}

	/**
	 * Increases or decreases the comment score.
	 *
	 * @param  string  $discussion
	 * @param  int  $comment
	 * @return \Illuminate\Http\Response if JSON, \Illuminate\Http\Redirect otherwise
	 */
	public function vote(Request $request, $discussion, $comment)
	{
		$comment = Comment::with('author', 'discussion', 'votes')
			->find($comment);

		if (!$comment) {
			return response(view('comments.404'), 404);
		}

		$this->authorize('participate', $comment->discussion);

		$vote = Vote::voted($request->user(), $comment);

		if ($vote->exists()) {
			$vote = $vote->first();
		} else {
			$vote = new Vote;
			$vote->user()->associate($request->user());
			$vote->owner()->associate($comment);
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
				'comment' => $comment->url,
				'vote' => $vote,
				'score' => $comment->score
			]) :
			redirect()->route('whiteboard.show', $comment->discussion->url)
				->with('alert-success', $value ? 'Voted '.array_flip($voteValues)[$value].'!' : 'Vote rescinded!');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  string  $discussion
	 * @param  int  $comment
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($discussion, $comment)
	{
		$comment = Comment::with('discussion')->find($comment);

		if (!$comment) {
			return response(view('comments.404'), 404);
		}

		$this->authorize('delete', $comment);

		Cache::forget($comment->cacheKey);

		$discussion = $comment->discussion;

		$comment->delete();

		return redirect()->route('whiteboard.show', $discussion->url)
			->with('alert-success', 'Comment deleted!');
	}
}
