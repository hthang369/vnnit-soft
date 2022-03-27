<?php 
namespace Vnnit\Soft\Filesystem;

interface LoaderInterface 
{
	/**
	 * Load the messages for the given locale.
	 *
	 * @param  string  $name
	 * @param  string  $group
	 * @param  string  $namespace
	 * @return array
	 */
	public function load($name, $group, $namespace = null);

	/**
	 * Add a new namespace to the loader.
	 *
	 * @param  string  $namespace
	 * @param  string  $hint
	 * @return void
	 */
	public function addNamespace($namespace, $hint);

}