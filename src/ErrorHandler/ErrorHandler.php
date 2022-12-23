<?php

namespace Pulsar\Core\ErrorHandler;

use ErrorException;
use function in_array;
use function set_error_handler;
use const E_DEPRECATED;
use const E_USER_DEPRECATED;

final class ErrorHandler
{
    private array $deprecations = [];

    public static function register(): self
    {
        \error_reporting(E_ALL);
        \ini_set('display_errors', '1');
        
        $handler = new self();
        set_error_handler($handler);
        return $handler;
    }

    public function __invoke(int $level, string $message, ?string $file = null, ?int $line = null): void
    {
        if (!error_reporting()) {
            return;
        }
        if (in_array($level, [E_USER_DEPRECATED, E_DEPRECATED])) {
            $this->deprecations[] = ['level' => $level, 'file' => $file, ' line' => $line, 'message' => $message];
            return;
        }

        if (in_array($level, [E_ERROR, E_RECOVERABLE_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_USER_ERROR], true)) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    public function clean(): void
    {
        restore_error_handler();
    }

    /**
     * @return array
     */
    public function getDeprecations(): array
    {
        return $this->deprecations;
    }
}
