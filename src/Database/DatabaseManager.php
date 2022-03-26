<?php
namespace VnnSoft\Database\Connection;

use InvalidArgumentException;
use Vnnit\Soft\Database\Connectors\ConnectionFactory;
use Vnnit\Soft\Support\Arr;
use Vnnit\Soft\Support\ConfigurationUrlParser;

class DatabaseManager
{
    /**
     * Make the database connection instance.
     *
     * @param  string  $name
     * @return \Illuminate\Database\Connection
     */
    public function makeConnection($name)
    {
        $config = $this->configuration($name);

        return app()->singleton(ConnectionFactory::class)->make($config, $name);
    }

    /**
     * Get the configuration for a connection.
     *
     * @param  string  $name
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function configuration($name)
    {
        $name = $name ?: $this->getDefaultConnection();

        $connections = config('database.connections');

        if (is_null($config = Arr::get($connections, $name))) {
            throw new InvalidArgumentException("Database connection [{$name}] not configured.");
        }

        return (new ConfigurationUrlParser)->parseConfiguration($config);
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return config('database.default');
    }
}