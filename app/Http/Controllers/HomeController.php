<?php
namespace Castle\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Nicolaslopezj\Searchable\SearchableTrait;

class HomeController extends Controller
{
	/**
	 * Classes to search through.
	 *
	 * @var array
	 */
	protected $searchable = [
		\Castle\Client::class,
		\Castle\Comment::class,
		\Castle\Discussion::class,
		\Castle\Document::class,
		\Castle\Resource::class,
		\Castle\Tag::class,
		\Castle\User::class,
	];

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
	 * Show the home page.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function home()
	{
		return view('home');
	}

	/**
	 * Performs a search.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function search(Request $request, $term = null)
	{
		$term = $term ?: $request->input('term');

		if (!$term) {
			return view('search', ['term' => null, 'results' => null]);
		}

		$results = collect();
		foreach ($this->searchable as $class) {
			if (property_exists($class, 'searchable')) {
				$search = $class::search($term, 20, true)->get();
				$results->push($search);
			}
		}

		$results = $results->collapse()->sortByDesc('relevance');

		$paginator = new LengthAwarePaginator(
			$results->forPage(LengthAwarePaginator::resolveCurrentPage(), 20),
			$results->count(),
			20
		);

		$paginator->setPath(route('home.search'))
			->appends($request->all());

		return $request->wantsJson() ?
			$paginator :
			view('search', ['term' => $term, 'results' => $paginator])
				->withInput($request->only('term'));
	}

	/**
	 * Initializes Javascript, optionally for a specific page.
	 *
	 * @return Response JSONP callback to CastleJS.init
	 */
	public function castlejs(Request $request)
	{
		$data = [
			'debug' => config('app.debug'),
			'route' => $request->input('via'),
			'config' => config('castle')
		];

		return response()->json($data)
			->setCallback('CastleJS.init')
			->header('Cache-Control', 'private, max-age=604800');
	}

}
