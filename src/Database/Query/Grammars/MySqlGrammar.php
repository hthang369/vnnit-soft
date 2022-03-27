<?php 
namespace Vnnit\Soft\Database\Query\Grammars;

use Vnnit\Soft\Database\Query\Builder;

class MySqlGrammar extends Grammar {

	/**
	 * The keyword identifier wrapper format.
	 *
	 * @var string
	 */
	protected $wrapper = '`%s`';

	/**
	 * Compile an update statement into SQL.
	 *
	 * @param  \Vnnit\Soft\Database\Query\Builder  $query
	 * @param  array  $values
	 * @return string
	 */
	public function compileUpdate(Builder $query, $values)
	{
		$sql = parent::compileUpdate($query, $values);

		if (isset($query->orders))
		{
			$sql .= ' '.$this->compileOrders($query, $query->orders);
		}

		if (isset($query->limit))
		{
			$sql .= ' '.$this->compileLimit($query, $query->limit);
		}

		return rtrim($sql);
	}

}