<?php namespace Milkyway\SS\Env;

/**
 * Milkyway Multimedia
 * Env.php
 *
 * Allows you to access SS config using dot notation,
 * and completely replacing them using $_ENV variables
 *
 * @package milkyway-multimedia/ss-mwm-env
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use Config;
use ViewableData;

class Env {
    protected $_cache = [];

    protected $_defaults = [
        'objects' => [],
        'parseEnvVarFn' => null,
        'beforeConfigNamespaceCheckCallbacks' => [],
        'mapping' => [],
        'fromCache' => true,
        'doCache' => true,
        'on' => Config::INHERITED,
    ];

    /**
     * Get a config value from DataObject/YAML/environment vars using dot notation
     *
     * @param string $key
     * @param mixed|null $default
     * @param array $params
     * @return mixed|null
     */
    public function get($key, $default = null, $params = [])
    {
        $params = array_merge($this->_defaults, $params);

        foreach ($params['objects'] as $object) {
            if ($object && ($object instanceof ViewableData) && $object->$key) {
                return $object->$key;
            }
        }

        // Grab mapping from object
        $mapping = (array)Config::inst()->get('environment', 'mapping');
        $objects = array_reverse($params['objects'], true);

        foreach ($objects as $object) {
            if ($object && method_exists($object, 'config')) {
                $mapping = array_merge($mapping, (array)$object->config()->db_to_environment_mapping);
            }
        }

        $mapping = array_merge($mapping, $params['mapping']);

        if (isset($mapping[$key])) {
            $key = $mapping[$key];
        }

        // 1. Check cache for valid key and return if found
        if ($params['fromCache'] && array_key_exists($key, $this->_cache)) {
            return $this->_cache[$key] === null ? $default : $this->_cache[$key];
        }

        $value = $default;

        // The function to check the $_ENV vars
        $findInEnvironment = function ($key) use ($params) {
            $value = null;

            if (isset($_ENV[$key])) {
                $value = $_ENV[$key];
            }

            if (getenv($key)) {
                $value = getenv($key);
            }

            if (is_callable($params['parseEnvVarFn'])) {
                $value = call_user_func_array($params['parseEnvVarFn'], [$value, $key]);
            }

            return $value;
        };

        // If key has dots, check recursively
        if (strpos($key, '.')) {
            $keyParts = explode('.', $key);

            // First part of key can denote multiple classes separated by a pipe (or)
            $namespaces = explode('|', array_shift($keyParts));

            // 2. Check \Config class for original value
            foreach ($namespaces as $namespace) {
                // Do a callback to get a value from a function sent in (this is for checking SiteConfig)
                if (isset($params['beforeConfigNamespaceCheckCallbacks'][$namespace]) && is_callable($params['beforeConfigNamespaceCheckCallbacks'][$namespace])) {
                    $value = call_user_func_array($params['beforeConfigNamespaceCheckCallbacks'][$namespace],
                        [$keyParts, $key]);

                    if ($value !== null) {
                        break;
                    }
                }

                $config = Config::inst()->forClass($namespace);

                $value = $config->get(implode('.', $keyParts), $params['on']);

                // 3. If value not found explicitly, recursively get if array
                if (!$value && count($keyParts) > 1) {
                    $configKey = array_shift($keyParts);
                    $configValue = $config->get($configKey, $params['on']);

                    if (is_array($configValue)) {
                        $value = array_get($configValue, implode('.', $keyParts));
                    } else {
                        $value = $configValue;
                    }
                }

                if ($value !== null) {
                    break;
                }
            }

            // 4. Check environment for key explicitly
            if ($value === null) {
                $value = $findInEnvironment($key);
            }

            // 5. Otherwise check for key by namespace in environment
            if ($value === null && count($namespaces) > 1) {
                foreach ($namespaces as $namespace) {
                    $value = $findInEnvironment($namespace . '.' . implode('.', $keyParts));

                    if ($value !== null) {
                        break;
                    }
                }
            }

            // 6. Otherwise check for key recursively in environment
            if ($value === null && !empty($keyParts)) {
                foreach ($namespaces as $namespace) {
                    if (($first = $findInEnvironment($namespace)) && is_array($first)) {
                        $value = array_get($first, implode('.', $keyParts));

                        if ($value !== null) {
                            break;
                        }
                    }
                }

                // 7. Otherwise, check for key as is (without namespaces, a global override to assume)
                if ($value === null) {
                    $value = $findInEnvironment(implode('.', $keyParts));
                }
            }
        } else {
            // Or else check in $_ENV vars
            $value = $findInEnvironment($key);
        }

        if ($params['doCache']) {
            $this->_cache[$key] = $value;
        }

        return $value === null ? $default : $value;
    }

    /**
     * Set a value manually in the config cache
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value = null)
    {
        $this->_cache[$key] = $value;
    }

    /**
     * Remove a value from the config cache
     *
     * @param string $key
     */
    public function remove($key)
    {
        if (isset($this->_cache[$key])) {
            unset($this->_cache[$key]);
        }
    }
} 