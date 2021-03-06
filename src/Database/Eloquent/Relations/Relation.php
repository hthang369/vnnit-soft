<?php namespace Vnnit\Soft\Database\Eloquent\Relations;

use Closure;
use DateTime;
use Carbon\Carbon;
use Vnnit\Soft\Database\Eloquent\Model;
use Vnnit\Soft\Database\Eloquent\Builder;
use Vnnit\Soft\Database\Query\Expression;
use Vnnit\Soft\Database\Eloquent\Collection;

abstract class Relation {

	/**
	 * The Eloquent query builder instance.
	 *
	 * @var \Vnnit\Soft\Database\Eloquent\Builder
	 */
	protected $query;

	/**
	 * The parent model instance.
	 *
	 * @var \Vnnit\Soft\Database\Eloquent\Model
	 */
	protected $parent;

	/**
	 * The related model instance.
	 *
	 * @var \Vnnit\Soft\Database\Eloquent\Model
	 */
	protected $related;

	/**
	 * Indicates if the relation is adding constraints.
	 *
	 * @var bool
	 */
	protected static $constraints = true;

	/**
	 * Create a new relation instance.
	 *
	 * @param  \Vnnit\Soft\Database\Eloquent\Builder  $query
	 * @param  \Vnnit\Soft\Database\Eloquent\Model  $parent
	 * @return void
	 */
	public function __construct(Builder $query, Model $parent)
	{
		$this->query = $query;
		$this->parent = $parent;
		$this->related = $query->getModel();

		$this->addConstraints();
	}

	/**
	 * Set the base constraints on the relation query.
	 *
	 * @return void
	 */
	abstract public function addConstraints();

	/**
	 * Set the constraints for an eager load of the relation.
	 *
	 * @param  array  $models
	 * @return void
	 */
	abstract public function addEagerConstraints(array $models);

	/**
	 * Initialize the relation on a set of models.
	 *
	 * @param  array   $models
	 * @param  string  $relation
	 * @return void
	 */
	abstract public function initRelation(array $models, $relation);

	/**
	 * Match the eagerly loaded results to their parents.
	 *
	 * @param  array   $models
	 * @param  \Vnnit\Soft\Database\Eloquent\Collection  $results
	 * @param  string  $relation
	 * @return array
	 */
	abstract public function match(array $models, Collection $results, $relation);

	/**
	 * Get the results of the relationship.
	 *
	 * @return mixed
	 */
	abstract public function getResults();

	/**
	 * Touch all of the related models for the relationship.
	 *
	 * @return void
	 */
	public function touch()
	{
		$column = $this->getRelated()->getUpdatedAtColumn();

		$this->rawUpdate(array($column => $this->getRelated()->freshTimestampString()));
	}

	/**
	 * Restore all of the soft deleted related models.
	 *
	 * @return int
	 */
	public function restore()
	{
		return $this->query->withTrashed()->restore();
	}

	/**
	 * Run a raw update against the base query.
	 *
	 * @param  array  $attributes
	 * @return int
	 */
	public function rawUpdate(array $attributes = array())
	{
		return $this->query->update($attributes);
	}

	/**
	 * Add the constraints for a relationship count query.
	 *
	 * @param  \Vnnit\Soft\Database\Eloquent\Builder  $query
	 * @return \Vnnit\Soft\Database\Eloquent\Builder
	 */
	public function getRelationCountQuery(Builder $query)
	{
		$query->select(new Expression('count(*)'));

		$key = $this->wrap($this->parent->getQualifiedKeyName());

		return $query->where($this->getForeignKey(), '=', new Expression($key));
	}

	/**
	 * Run a callback with constrains disabled on the relation.
	 *
	 * @param  \Closure  $callback
	 * @return mixed
	 */
	public static function noConstraints(Closure $callback)
	{
		static::$constraints = false;

		// When resetting the relation where clause, we want to shift the first element
		// off of the bindings, leaving only the constraints that the developers put
		// as "extra" on the relationships, and not original relation constraints.
		$results = call_user_func($callback);

		static::$constraints = true;

		return $results;
	}

	/**
	 * Get all of the primary keys for an array of models.
	 *
	 * @param  array  $models
	 * @return array
	 */
	protected function getKeys(array $models)
	{
		return array_values(array_map(function($value)
		{
			return $value->getKey();

		}, $models));
	}

	/**
	 * Get the underlying query for the relation.
	 *
	 * @return \Vnnit\Soft\Database\Eloquent\Builder
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * Get the base query builder driving the Eloquent builder.
	 *
	 * @return \Vnnit\Soft\Database\Query\Builder
	 */
	public function getBaseQuery()
	{
		return $this->query->getQuery();
	}

	/**
	 * Get the parent model of the relation.
	 *
	 * @return \Vnnit\Soft\Database\Eloquent\Model
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Get the related model of the relation.
	 *
	 * @return \Vnnit\Soft\Database\Eloquent\Model
	 */
	public function getRelated()
	{
		return $this->related;
	}

	/**
	 * Get the name of the "created at" column.
	 *
	 * @return string
	 */
	public function createdAt()
	{
		return $this->parent->getCreatedAtColumn();
	}

	/**
	 * Get the name of the "updated at" column.
	 *
	 * @return string
	 */
	public function updatedAt()
	{
		return $this->parent->getUpdatedAtColumn();
	}

	/**
	 * Get the name of the related model's "updated at" column.
	 *
	 * @return string
	 */
	public function relatedUpdatedAt()
	{
		return $this->related->getUpdatedAtColumn();
	}

	/**
	 * Wrap the given value with the parent query's grammar.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public function wrap($value)
	{
		return $this->parent->getQuery()->getGrammar()->wrap($value);
	}

	/**
	 * Handle dynamic method calls to the relationship.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		$result = call_user_func_array(array($this->query, $method), $parameters);

		if ($result === $this->query) return $this;

		return $result;
	}

}