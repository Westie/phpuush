<?php

namespace App\Configuration;

class Configuration
{
    private $data;

    /**
     *  Constructor
     */
    public function __construct()
    {
        if (file_exists(APP_DIR . 'configuration.php')) {
            $this->data = $this->readLegacyConfigurationFile();
        }
    }

    /**
     *  Read a legacy config file
     */
    private function readLegacyConfigurationFile(): array
    {
        $data = (function() {
            $returnedValue = require APP_DIR . 'configuration.php';

            if (is_array($returnedValue)) {
                return $returnedValue;
            }

            if (isset($aGlobalConfiguration) && is_array($aGlobalConfiguration)) {
                return $aGlobalConfiguration;
            }

            return null;
        })();

        if (!empty($data['databases']['sql']) && file_exists($data['databases']['sql'])) {
            $data['databases']['sql'] = [
                'driver' => 'Pdo_Sqlite',
                'database' => $data['databases']['sql'],
            ];
        }

        return $data;
    }

    /**
     *  Get a value
     */
    public function get($path)
    {
        $paths = explode('.', $path);
        $root = $this->data;

        foreach ($paths as $path) {
            if (isset($root[$path])) {
                $root = $root[$path];
            } else {
                $root = null;
            }
        }

        return $root;
    }

    /**
     *  Return config
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
