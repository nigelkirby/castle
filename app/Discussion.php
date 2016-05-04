<?php

namespace Castle;

use Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Markdown;
use Nicolaslopezj\Searchable\SearchableTrait as Searchable;

class Discussion extends Model
{
	use Attachable, SoftDeletes, Searchable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'slug', 'content',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	/**
	 * The attributes that should be casted to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'attachments' => 'collection',
	];

	/**
	 * Searchable rules.
	 *
	 * @var array
	 */
	protected $searchable = [
		'columns' => [
			'discussions.name' => 10,
			'discussions.slug' => 7,
			'discussions.content' => 7,
			'users.name' => 5,
			'discussion_statuses.status' => 3,
		],
		'joins' => [
			'users' => ['discussions.created_by', 'users.id'],
			'users' => ['discussions.updated_by', 'users.id'],
			'discussion_statuses' => ['discussion_statuses.id', 'discussions.status_id'],
		]
	];

	// Helper functions

	/**
	 * @return Resource
	 */
	public static function findBySlug($slug)
	{
		return self::where('slug', $slug)->first();
	}

	/**
	 * @return string
	 */
	public function toHtml()
	{
		if (Cache::has($this->cacheKey)) {
			$html = Cache::get($this->cacheKey);
		} else {
			$html = Markdown::convertToHtml($this->content);
			Cache::forever($this->cacheKey, $html);
		}

		return $html;
	}

	// Attribute helper functions

	/**
	 * @return string
	 */
	public function getUrlAttribute()
	{
		return $this->slug;
	}

	/**
	 * @return string
	 */
	public function getScoreAttribute()
	{
		return $this->votes->sum('value');
	}

	/**
	 * @return Collection
	 */
	public function getVotersAttribute($query)
	{
		return $this->votes->pluck('value', 'user_id');
	}

	/**
	 * @return string
	 */
	public function getAttachmentDirectoryAttribute()
	{
		return implode('/', [
			'discussions',
			$this->url
		]);
	}

	/**
	 * @return string
	 */
	public function getCacheKeyAttribute()
	{
		return 'discussions.'.$this->slug.'.content.html';
	}

	// Relationships

	/**
	 * @return Relationship
	 */
	public function createdBy()
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	/**
	 * @return Relationship
	 */
	public function updatedBy()
	{
		return $this->belongsTo(User::class, 'updated_by');
	}

	/**
	 * @return Relationship
	 */
	public function comments()
	{
		return $this->hasMany(Comment::class, 'discussion_id');
	}

	/**
	 * @return Relationship
	 */
	public function status()
	{
		return $this->belongsTo(DiscussionStatus::class, 'status_id');
	}

	/**
	 * @return Relationship
	 */
	public function tags()
	{
		return $this->belongsToMany(Tag::class, 'discussions_tags');
	}

	/**
	 * @return Relationship
	 */
	public function votes()
	{
		return $this->morphMany(Vote::class, 'owner');
	}

	// Query scopes

	/**
	 * @return QueryBuilder
	 */
	public function scopeNamed($query, $name)
	{
		return $query->where('name', 'like', '%'.$name.'%');
	}

	/**
	 * @return QueryBuilder
	 */
	public function scopeBy($query, $user)
	{
		$user = ($user instanceOf User) ? $user->id : $user;

		return $query->where('created_by', $user)
			->orWhere('updated_by', $user);
	}

	/**
	 * @return QueryBuilder
	 */
	public function scopeTagged($query, $tag)
	{
		return $query->with('tags')
			->where('tag.name', 'like', '%'.$tag.'%');
	}

}
