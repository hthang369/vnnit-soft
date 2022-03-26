<?php 
namespace Vnnit\Soft\Database;

use Vnnit\Soft\Database\Query\Grammars\SQLiteGrammar as QueryGrammar;

class SQLiteConnection extends Connection {

	/**
	 * Get the default query grammar instance.
	 *
	 * @return \Vnnit\Soft\Database\Query\Grammars\SQLiteGrammar
	 */
	protected function getDefaultQueryGrammar()
	{
		return $this->withTablePrefix(new QueryGrammar);
	}
}