<?php

namespace Vnnit\Soft\Foundation;

use Closure;
use Vnnit\Soft\Config\Repository;
use Vnnit\Soft\Filesystem\Filesystem;
use Vnnit\Soft\Foundation\Exception\BindingResolutionException;
use Vnnit\Soft\Support\Str;

class Application
{
    protected $config;

    /**
     * The container's shared instances.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * The registered type aliases.
     *
     * @var array
     */
    protected $aliases = [];

    public function __construct()
    {
        // $this->config = [
        //     'config' => new Repository()
        // ];

        // foreach(get_filenames('config', true) as $file) {
        //     if (!Str::contains($file, 'constants')) {
        //         $data = require_once $file;
        //         config([basename($file, '.php') => $data]);
        //     }
        // }
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param  string  $abstract
     * @param  mixed   $instance
     * @return void
     */
    public function instance($abstract, $instance)
    {
        if (is_array($abstract)) {
            list($abstract, $alias) = $this->extractAlias($abstract);

            $this->alias($abstract, $alias);
        }

        $this->instances[$abstract] = $instance;
    }

    /**
     * Alias a type to a shorter name.
     *
     * @param  string  $abstract
     * @param  string  $alias
     * @return void
     */
    public function alias($abstract, $alias)
    {
        $this->aliases[$alias] = $abstract;
    }

    /**
     * Extract the type and alias from a given definition.
     *
     * @param  array  $definition
     * @return array
     */
    protected function extractAlias(array $definition)
    {
        return array(key($definition), current($definition));
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param  string  $concrete
     * @param  array   $parameters
     * @return mixed
     */
    protected function build($concrete, $parameters = [])
    {
        if ($concrete instanceof Closure)
		{
			return $concrete($parameters);
		}

        $reflector = new \ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            $message = "Target [$concrete] is not instantiable.";

            throw new BindingResolutionException($message);
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor))
		{
			return new $concrete;
		}

        $parameters = $constructor->getParameters();

        $dependencies = $this->getDependencies($parameters);

		return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @param  array   $parameters
     * @return mixed
     */
    public function make($abstract, $parameters = [])
    {
        return $this->singleton($abstract, $parameters);
    }

    public function bind($abstract, $parameters = [])
    {
        $instance = $this->build($abstract, $parameters);

        $this->instance($abstract, $instance);

        return $instance;
    }

    public function singleton($abstract, $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->instances[$abstract]))
		{
			return $this->instances[$abstract];
		}

        $instance = $this->build($abstract, $parameters);

        $this->instance($abstract, $instance);

        return $instance;
    }

    /**
	 * Resolve all of the dependencies from the ReflectionParameters.
	 *
	 * @param  array  $parameters
	 * @return array
	 */
	protected function getDependencies($parameters)
    {
        $dependencies = [];

		foreach ($parameters as $parameter)
		{
			$dependency = $parameter->getClass();

			// If the class is null, it means the dependency is a string or some other
			// primitive type which we can not resolve since it is not a class and
			// we'll just bomb out with an error since we have no-where to go.
			if (is_null($dependency))
			{
				$dependencies[] = $this->resolveNonClass($parameter);
			}
			else
			{
				$dependencies[] = $this->resolveClass($parameter);
			}
		}

		return (array) $dependencies;
    }

    /**
	 * Resolve a non-class hinted dependency.
	 *
	 * @param  ReflectionParameter  $parameter
	 * @return mixed
	 */
	protected function resolveNonClass(\ReflectionParameter $parameter)
	{
		if ($parameter->isDefaultValueAvailable())
		{
			return $parameter->getDefaultValue();
		}
		else
		{
			$message = "Unresolvable dependency resolving [$parameter].";

			throw new BindingResolutionException($message);
		}
	}

	/**
	 * Resolve a class based dependency from the container.
	 *
	 * @param  \ReflectionParameter  $parameter
	 * @return mixed
	 */
	protected function resolveClass(\ReflectionParameter $parameter)
	{
		try
		{
			return $this->make($parameter->getClass()->name);
		}

		// If we can not resolve the class instance, we will check to see if the value
		// is optional, and if it is we will return the optional parameter value as
		// the value of the dependency, similarly to how we do this with scalars.
		catch (BindingResolutionException $e)
		{
			if ($parameter->isOptional())
			{
				return $parameter->getDefaultValue();
			}
			else
			{
				throw $e;
			}
		}
	}

    /**
	 * Get the alias for an abstract if available.
	 *
	 * @param  string  $abstract
	 * @return string
	 */
	protected function getAlias($abstract)
	{
		return isset($this->aliases[$abstract]) ? $this->aliases[$abstract] : $abstract;
	}

    /**
	 * Get the configuration loader instance.
	 *
	 * @return \Illuminate\Config\LoaderInterface
	 */
	public function getConfigLoader()
	{
		return new FileLoader(new Filesystem, $this->config['path'].'/config');

        $fileSystem = new Filesystem;
	}

    public function setAppPath($path)
    {
        $this->config['path'] = $path;
    }

    private function getNameInstance($className)
    {
        $arr = explode('\\', $className);

        return strtolower(end($arr));
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        if (!isset($this->instance[$name])) {
            $name = $this->getNameInstance($name);
        }

        return isset($this->instance[$name]) ? $this->instance[$name] : null;
    }
}
