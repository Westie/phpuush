<?php

namespace App\Configuration;

use Dotenv\Dotenv;

class Configuration
{
    private $data = [];

    /**
     *  Constructor
     */
    public function __construct()
    {
        // read legacy config (if it exists)
        if (empty($this->data) && empty($_ENV['PHPUUSH_IGNORE_CONFIG_FILE'])) {
            if (file_exists(APP_DIR . '/configuration.php')) {
                $this->data = $this->readLegacyConfigurationFile(APP_DIR . '/configuration.php');
            }
        }

        // read environment variables (as a fall back...)
        if (empty($this->data) && empty($_ENV['PHPUUSH_IGNORE_ENV'])) {
            $this->data = $this->readEnvironmentVariables();
        }

        // fix DSN
        if (!empty($this->data['databases']['sql'])) {
            if (file_exists($this->data['databases']['sql'])) {
                $this->data['databases']['sql'] = [
                    'driver' => 'Pdo_Sqlite',
                    'database' => $this->data['databases']['sql'],
                ];
            }
        }

        return;
    }

    /**
     *  Read a legacy config file
     */
    private function readLegacyConfigurationFile(string $fileName): array
    {
        return (static function() use ($fileName) {
            $returnedValue = require $fileName;

            if (is_array($returnedValue)) {
                return $returnedValue;
            }
            if (isset($aGlobalConfiguration) && is_array($aGlobalConfiguration)) {
                return $aGlobalConfiguration;
            }

            return [];
        })();
    }

    /**
     *  Read environment variables
     */
    private function readEnvironmentVariables()
    {
        $data = [
            'databases' => [],
            'files' => [],
        ];

        if (empty($_ENV['PHPUUSH_SKIP_ENV_FILE'])) {
            $fileName = '.env';

            if (file_exists(APP_DIR . '/' . $fileName)) {
                $dotenv = Dotenv::createImmutable(APP_DIR . '/', $fileName);
                $dotenv->load();
            } elseif (file_exists(APP_DIR . '/../' . $fileName)) {
                $dotenv = Dotenv::createImmutable(APP_DIR . '/../', $fileName);
                $dotenv->load();
            }
        }

        if (!empty($_ENV['PHPUUSH_DATABASE'])) {
            $data['databases']['sql'] = $_ENV['PHPUUSH_DATABASE'];
        }
        if (!empty($_ENV['PHPUUSH_FILES_DOMAIN'])) {
            $data['files']['domain'] = $_ENV['PHPUUSH_FILES_DOMAIN'];
        }
        if (!empty($_ENV['PHPUUSH_FILES_TTL'])) {
            $data['files']['ttl'] = $_ENV['PHPUUSH_FILES_TTL'];
        }
        if (!empty($_ENV['PHPUUSH_FILES_UPLOAD'])) {
            $data['files']['upload'] = $_ENV['PHPUUSH_FILES_UPLOAD'];
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
