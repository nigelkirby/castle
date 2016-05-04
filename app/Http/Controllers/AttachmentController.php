<?php

namespace Castle\Http\Controllers;
use Castle\Attachable;
use Castle\Http\Requests;

use Carbon;
use File;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Storage;

class AttachmentController extends Controller
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
		$this->authorize('view', Attachable::class);

		$attachments = collect(Storage::disk('attachments')->allFiles('/'));

		$paginator = new LengthAwarePaginator(
			$attachments->forPage(LengthAwarePaginator::resolveCurrentPage(), 50),
			$attachments->count(),
			50
		);

		$paginator->setPath(route('attachments.index'))
			->appends($request->all());

		return view('attachments.index', ['attachments' => $paginator]);
	}

	/**
	 * Download the specified resource.
	 *
	 * @param  string  $attachment
	 * @return \Illuminate\Http\Response
	 */
	public function download($attachment)
	{
		$this->authorize('view', Attachable::class);

		$storage = Storage::disk('attachments');

		if (!$storage->has($attachment)) {
			return response(view('attachments.404'), 404);
		}

		$age = Carbon\Carbon::createFromTimestamp(
			$storage->lastModified($attachment)
		);

		return response()->stream(function() use ($attachment, $storage) {
			$output = fopen('php://output', 'w');
			fwrite($output, $storage->get($attachment));
			fclose($output);
		}, 200, [
			'Last-Modified' => $age->toRfc2822String(),
			'Expires' => $age->addMonth(),
			'Cache-Control' => 'must-revalidate',
			'Content-Disposition' => 'attachment; filename="'.utf8_encode(basename($attachment)).'"',
			'Content-Length' => $storage->size($attachment),
			'Content-Type' => $storage->mimeType($attachment),
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  string  $attachment
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($attachment)
	{
		$this->authorize('delete', Attachable::class);

		Storage::disk('attachments')->delete($attachment);

		return redirect()->route('attachments.index')
			->with('alert-success', 'Attachment deleted!');
	}
}
