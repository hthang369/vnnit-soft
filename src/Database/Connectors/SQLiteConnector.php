<?php

namespace Vnnit\Soft\Database\Connectors;

use InvalidArgumentException;

class SQLiteConnector extends Connector
{
    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return \PDO
     *
     * @throws \InvalidArgumentException
     */
    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);

        $options = $this->getOptions($config);

        // SQLite supports "in-memory" databases that only last as long as the owning
        // connection does. These are useful for tests or for short lifetime store
        // querying. In-memory databases may only have a single open connection.
        if (is_null($dsn)) {
            throw new InvalidArgumentException("Database ({$config['database']}) does not exist.");
        }

        return $this->createConnection($dsn, $config, $options);
    }

    /**
     * Create a DSN string from a configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        if ($config['database'] === ':memory:')
            return 'sqlite::memory:';
        else {
            $path = realpath($config['database']);

            if ($path === false) {
                return null;
            }

            return "sqlite:{$path}";
        }
    }
}
