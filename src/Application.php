<?php
namespace Vnnit\Soft;

use Vnnit\Soft\Config\Repository;
use Vnnit\Soft\Support\Str;

class Application
{
    protected $config;

    protected $instance;

    public function __construct()
    {
        $this->config = [
            'config' => new Repository()
        ];

        foreach(get_filenames('config', true) as $file) {
            if (!Str::contains($file, 'constants')) {
                $data = require_once $file;
                config([basename($file, '.php') => $data]);
            }
        }
    }

    public function singleton($className)
    {
        $name = $this->getNameInstance($className);

        if (!isset($this->instance[$name])) {
            $this->instance[$name] = new $className();
        }

        return $this->instance[$name];
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