<?php

namespace Castle;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Nicolaslopezj\Searchable\SearchableTrait as Searchable;

class User extends Authenticatable
{
	use SoftDeletes, Searchable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'email', 'phone', 'password',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];

	/**
	 * Searchable rules.
	 *
	 * @var array
	 */
	protected $searchable = [
		'columns' => [
			'users.name' => 10,
		]
	];

	// Attribute helper functions

	/**
	 * @return string
	 */
	public function getUrlAttribute()
	{
		return $this->id;
	}

	// Relationships

	/**
	 * @return Relationship
	 */
	public function permissions()
	{
		return $this->belongsToMany(Permission::class, 'users_permissions');
	}

	/**
	 * @return Relationship
	 */
	public function documents()
	{
		return $this->hasMany(Document::class, 'created_by');
	}

	/**
	 * @return Relationship
	 */
	public function documentEdits()
	{
		return $this->hasMany(Document::class, 'updated_by');
	}

	// Query scopes

	/**
	 * @return QueryBuilder
	 */
	public function scopeNamed($query, $name)
	{
		return $query->where('name', $name);
	}

}
