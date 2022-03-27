<?php 
namespace Vnnit\Soft\Database;

use Vnnit\Soft\Database\Query\Grammars\PostgresGrammar as QueryGrammar;

class PostgresConnection extends Connection {

	/**
	 * Get the default query grammar instance.
	 *
	 * @return \Vnnit\Soft\Database\Query\Grammars\PostgresGrammar
	 */
	protected function getDefaultQueryGrammar()
	{
		return $this->withTablePrefix(new QueryGrammar);
	}
}