<?php

namespace PKP\plugins {
    class GenericPlugin
    {
        public bool $enabled = false;
        public int $currentContextId = 0;
        public bool $parentRegisterResult = true;

        public function register($category, $path, $mainContextId = null)
        {
            return $this->parentRegisterResult;
        }

        public function getEnabled()
        {
            return $this->enabled;
        }

        public function getCurrentContextId()
        {
            return $this->currentContextId;
        }
    }
}

namespace PKP\config {
    class Config
    {
        public static array $vars = [];

        public static function reset(): void
        {
            self::$vars = [];
        }

        public static function setVar(string $section, string $key, $value): void
        {
            self::$vars[$section][$key] = $value;
        }

        public static function getVar(string $section, string $key, $default = null)
        {
            return self::$vars[$section][$key] ?? $default;
        }
    }
}

namespace Illuminate\Mail {
    class MailManager
    {
        public array $extensions = [];
        public ?string $defaultDriver = null;

        public function extend(string $name, callable $resolver): void
        {
            $this->extensions[$name] = $resolver;
        }

        public function setDefaultDriver(string $name): void
        {
            $this->defaultDriver = $name;
        }
    }
}

namespace Illuminate\Support\Facades {
    class Mail
    {
        public static $root;

        public static function swap($root): void
        {
            self::$root = $root;
        }

        public static function getFacadeRoot()
        {
            return self::$root;
        }
    }
}

namespace InnoGE\LaravelMsGraphMail\Services {
    class MicrosoftGraphApiService
    {
        public array $config;

        public function __construct(string $tenantId, string $clientId, string $clientSecret, int $accessTokenTtl)
        {
            $this->config = [
                'tenantId' => $tenantId,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'accessTokenTtl' => $accessTokenTtl,
            ];
        }
    }
}

namespace InnoGE\LaravelMsGraphMail {
    use InnoGE\LaravelMsGraphMail\Services\MicrosoftGraphApiService;

    class MicrosoftGraphTransport
    {
        public MicrosoftGraphApiService $service;

        public function __construct(MicrosoftGraphApiService $service)
        {
            $this->service = $service;
        }
    }
}

namespace PKP\core {
    class PKPContainer
    {
        private static $instance;

        public static function setInstance($instance): void
        {
            self::$instance = $instance;
        }

        public static function getInstance()
        {
            return self::$instance;
        }
    }
}

namespace Tests\Support {
    class ArrayConfigRepository
    {
        private array $items = [];

        public function get(string $key, $default = null)
        {
            $segments = explode('.', $key);
            $value = $this->items;

            foreach ($segments as $segment) {
                if (!is_array($value) || !array_key_exists($segment, $value)) {
                    return $default;
                }

                $value = $value[$segment];
            }

            return $value;
        }

        public function set(string $key, $value): void
        {
            $segments = explode('.', $key);
            $target = &$this->items;

            foreach ($segments as $segment) {
                if (!isset($target[$segment]) || !is_array($target[$segment])) {
                    $target[$segment] = [];
                }

                $target = &$target[$segment];
            }

            $target = $value;
        }
    }

    class TestContainer
    {
        private array $services;

        public function __construct(array $services)
        {
            $this->services = $services;
        }

        public function get(string $key)
        {
            return $this->services[$key];
        }
    }
}

namespace {
    require_once __DIR__ . '/../GraphMailerPlugin.php';

    function assertSame($expected, $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            throw new RuntimeException($message ?: sprintf('Expected %s, got %s', var_export($expected, true), var_export($actual, true)));
        }
    }

    function assertTrue($actual, string $message = ''): void
    {
        if ($actual !== true) {
            throw new RuntimeException($message ?: 'Value is not true');
        }
    }

    function assertFalse($actual, string $message = ''): void
    {
        if ($actual !== false) {
            throw new RuntimeException($message ?: 'Value is not false');
        }
    }

    function assertArrayHasKey($key, array $array, string $message = ''): void
    {
        if (!array_key_exists($key, $array)) {
            throw new RuntimeException($message ?: sprintf('Missing array key %s', $key));
        }
    }

    function assertInstanceOf(string $expectedClass, $value, string $message = ''): void
    {
        if (!$value instanceof $expectedClass) {
            throw new RuntimeException($message ?: sprintf('Expected instance of %s', $expectedClass));
        }
    }
}
