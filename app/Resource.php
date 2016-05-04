<?php

namespace Castle;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nicolaslopezj\Searchable\SearchableTrait as Searchable;

class Resource extends Model
{
	use Attachable, SoftDeletes, Searchable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'slug', 'metadata', 'attachments'
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
		'metadata' => 'collection',
		'attachments' => 'collection',
	];

	/**
	 * Searchable rules.
	 *
	 * @var array
	 */
	protected $searchable = [
		'columns' => [
			'resources.name' => 10,
			'resources.slug' => 7,
			'resource_types.name' => 5,
			'resource_types.slug' => 5,
			'clients.name' => 7,
			'clients.slug' => 5,
		],
		'joins' => [
			'clients' => ['resources.client_id','clients.id'],
			'resource_types' => ['resources.resource_type_id','resource_types.id'],
		],
	];

	// Helper functions

	/**
	 * @return Resource
	 */
	public static function findBySlug($client, $slug)
	{
		$client = ($client instanceOf Client) ? $client : Client::findBySlug($client);

		if (!$client) {
			return null;
		}

		return self::where('client_id', $client->id)
			->where('slug', $slug)
			->first();
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
	public function getAttachmentDirectoryAttribute()
	{
		return implode('/', [
			'clients',
			isset($this->client) ? $this->client->url : $this->client,
			'resources',
			$this->url
		]);
	}

	// Relationships

	/**
	 * @return Relationship
	 */
	public function type()
	{
		return $this->belongsTo(ResourceType::class, 'resource_type_id');
	}

	/**
	 * @return Relationship
	 */
	public function tags()
	{
		return $this->belongsToMany(Tag::class, 'resources_tags');
	}

	/**
	 * @return Relationship
	 */
	public function client()
	{
		return $this->belongsTo(Client::class);
	}

	// Query scopes

	/**
	 * @return QueryBuilder
	 */
	public function scopeNamed($query, $name)
	{
		return $query->where('name', 'like', '%'.$name.'%');
	}

}
