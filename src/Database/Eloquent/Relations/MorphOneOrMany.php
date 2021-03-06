<?php namespace Vnnit\Soft\Database\Eloquent\Relations;

use Vnnit\Soft\Database\Eloquent\Model;
use Vnnit\Soft\Database\Eloquent\Builder;

abstract class MorphOneOrMany extends HasOneOrMany {

	/**
	 * The foreign key type for the relationship.
	 *
	 * @var string
	 */
	protected $morphType;

	/**
	 * The class name of the parent model.
	 *
	 * @var string
	 */
	protected $morphClass;

	/**
	 * Create a new has many relationship instance.
	 *
	 * @param  \Vnnit\Soft\Database\Eloquent\Builder  $query
	 * @param  \Vnnit\Soft\Database\Eloquent\Model  $parent
	 * @param  string  $type
	 * @param  string  $id
	 * @return void
	 */
	public function __construct(Builder $query, Model $parent, $type, $id)
	{
		$this->morphType = $type;

		$this->morphClass = get_class($parent);

		parent::__construct($query, $parent, $id);
	}

	/**
	 * Set the base constraints on the relation query.
	 *
	 * @return void
	 */
	public function addConstraints()
	{
		if (static::$constraints)
		{
			parent::addConstraints();

			$this->query->where($this->morphType, $this->morphClass);
		}
	}

	/**
	 * Add the constraints for a relationship count query.
	 *
	 * @param  \Vnnit\Soft\Database\Eloquent\Builder  $query
	 * @return \Vnnit\Soft\Database\Eloquent\Builder
	 */
	public function getRelationCountQuery(Builder $query)
	{
		$query = parent::getRelationCountQuery($query);

		return $query->where($this->morphType, $this->morphClass);
	}

	/**
	 * Set the constraints for an eager load of the relation.
	 *
	 * @param  array  $models
	 * @return void
	 */
	public function addEagerConstraints(array $models)
	{
		parent::addEagerConstraints($models);

		$this->query->where($this->morphType, $this->morphClass);
	}	

	/**
	 * Attach a model instance to the parent model.
	 *
	 * @param  \Vnnit\Soft\Database\Eloquent\Model  $model
	 * @return \Vnnit\Soft\Database\Eloquent\Model
	 */
	public function save(Model $model)
	{
		$model->setAttribute($this->getPlainMorphType(), $this->morphClass);

		return parent::save($model);
	}

	/**
	 * Create a new instance of the related model.
	 *
	 * @param  array  $attributes
	 * @return \Vnnit\Soft\Database\Eloquent\Model
	 */
	public function create(array $attributes)
	{
		$foreign = $this->getForeignAttributesForCreate();

		// When saving a polymorphic relationship, we need to set not only the foreign
		// key, but also the foreign key type, which is typically the class name of
		// the parent model. This makes the polymorphic item unique in the table.
		$attributes = array_merge($attributes, $foreign);

		$instance = $this->related->newInstance($attributes);

		$instance->save();

		return $instance;
	}

	/**
	 * Get the foreign ID and type for creating a related model.
	 *
	 * @return array
	 */
	protected function getForeignAttributesForCreate()
	{
		$foreign = array($this->getPlainForeignKey() => $this->parent->getKey());

		$foreign[last(explode('.', $this->morphType))] = $this->morphClass;

		return $foreign;
	}

	/**
	 * Get the foreign key "type" name.
	 *
	 * @return string
	 */
	public function getMorphType()
	{
		return $this->morphType;
	}

	/**
	 * Get the plain morph type name without the table.
	 *
	 * @return string
	 */
	public function getPlainMorphType()
	{
		return last(explode('.', $this->morphType));
	}

	/**
	 * Get the class name of the parent model.
	 *
	 * @return string
	 */
	public function getMorphClass()
	{
		return $this->morphClass;
	}

}