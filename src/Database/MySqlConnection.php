<?php
namespace Vnnit\Soft\Database;

use Vnnit\Soft\Database\Query\Grammars\MySqlGrammar as QueryGrammar;

class MySqlConnection extends Connection
{
    /**
	 * Get the default query grammar instance.
	 *
	 * @return \Vnnit\Soft\Database\Query\Grammars\MySqlGrammar
	 */
	protected function getDefaultQueryGrammar()
	{
		return $this->withTablePrefix(new QueryGrammar);
	}
}