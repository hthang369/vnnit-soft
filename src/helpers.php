<?php

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Vnnit\Soft\Config\DotEnv;
use Vnnit\Soft\Pagination;
use Vnnit\Soft\Support\Arr;
use Vnnit\Soft\Support\Facade;
use Vnnit\Soft\Support\Uri;
use Vnnit\Soft\Support\VarDumper;

if (!function_exists('pagination_links')) {
    function pagination_links($current_page, $pages) {
        $pagination = new Vnnit\Soft\Pagination($current_page, $pages);

        return $pagination->getLinks();
    }
}

if ( ! function_exists('get_filenames'))
{
	/**
	 * Get Filenames
	 *
	 * Reads the specified directory and builds an array containing the filenames.
	 * Any sub-folders contained within the specified path are read as well.
	 *
	 * @param	string	path to source
	 * @param	bool	whether to include the path as part of the filename
	 * @param	bool	internal variable to determine recursion status - do not use in calls
	 * @return	array
	 */
	function get_filenames($source_dir, $include_path = FALSE, $_recursion = FALSE)
	{
		static $_filedata = array();

		if ($fp = @opendir($source_dir))
		{
			// reset the array and make sure $source_dir has a trailing slash on the initial call
			if ($_recursion === FALSE)
			{
				$_filedata = array();
				$source_dir = rtrim(realpath($source_dir), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			}

			while (FALSE !== ($file = readdir($fp)))
			{
				if (is_dir($source_dir.$file) && $file[0] !== '.')
				{
					get_filenames($source_dir.$file.DIRECTORY_SEPARATOR, $include_path, TRUE);
				}
				elseif ($file[0] !== '.')
				{

					
					$_filedata[] = ($include_path === TRUE) ? $source_dir.$file : $file;
				}
			}

			closedir($fp);
			return $_filedata;
		}

		return FALSE;
	}
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed|\Illuminate\Config\Repository
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (!function_exists('parse_url_query')) {
    function parse_url_query($url_query) {
		parse_str($url_query, $output);
		return $output;
    }
}

if (!function_exists('array_collapse')) {
    function array_collapse($array) {
        $results = [];

        foreach ($array as $values) {
            if (! is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        return array_merge([], ...$results);
    }
}

if (!function_exists('env')) {
	function env($key, $default = null) {
		$dotEnv = DotEnv::make(dirname(__DIR__.'../', 1));
		$dotEnv->load();
		return $dotEnv->getVariable($key) ?? $default;
	}
}

if (!function_exists('asset')) {
	function asset($path) {
		$uri = new Uri();
		return $uri->asset($path);
	}
}

// if (!function_exists('data_get')) {
// 	function data_get($target, $key, $default = null) {
// 		if (is_null($key)) {
//             return $target;
//         }

//         $key = is_array($key) ? $key : explode('.', $key);

//         foreach ($key as $i => $segment) {
//             unset($key[$i]);

//             if (is_null($segment)) {
//                 return $target;
//             }

//             if ($segment === '*') {
//                 if (! is_array($target)) {
//                     return value($default);
//                 }

//                 $result = [];

//                 foreach ($target as $item) {
//                     $result[] = data_get($item, $key);
//                 }

//                 return in_array('*', $key) ? Arr::collapse($result) : $result;
//             }

//             if (Arr::accessible($target) && Arr::exists($target, $segment)) {
//                 $target = $target[$segment];
//             } elseif (is_object($target) && isset($target->{$segment})) {
//                 $target = $target->{$segment};
//             } else {
//                 return value($default);
//             }
//         }

//         return $target;
// 	}
// }

// if (!function_exists('dd')) {
// 	function dd($data) {
// 		VarDumper::dump($data);
// 	}
// }

if (!function_exists('app')) {
    function app($make = '') {
        if ( ! is_null($make))
		{
			return app()->make($make);
		}

		return Facade::getFacadeApplication();
    }
}

if (!function_exists('vnn_config')) {
	function vnn_config($key, $default = null, $value = null) {
		$obj = app('config');
		if (empty($key)) {
			return $obj;
		}
        
		if (!is_null($value)) {
			$obj->set($key, $value);
		}

		return $obj->get($key, $default);
	}
}

if (!function_exists('includeFile')) {
    function includeFile($fileName, $isExist = true) {
        if ($isExist) {
            if (file_exists($fileName))
                require_once $fileName;
            else
                throw new FileNotFoundException("Không tìm thấy trang", 404);
        } else {
            require_once $fileName;
        }
    }
}

if (!function_exists('view_path')) {
    function view_path($fileName, $folder = 'template', $isExist = true) {
        // includeFile(VIEWPATH.$folder.DIRECTORY_SEPARATOR.$fileName, $isExist);
    }
}