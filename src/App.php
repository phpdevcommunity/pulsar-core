<?php

declare(strict_types=1);

namespace Pulsar\Core;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @package	Pulsar
 * @author	Devcoder.xyz <dev@devcoder.xyz>
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://www.devcoder.xyz
 */
final class App
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @var self|null
     */
    private static $instance;

    private function __construct(array $options)
    {
        if (array_key_exists('server_request', $options) === false) {
            throw new \LogicException('server_request is missing');
        }
        if (array_key_exists('response_factory', $options) === false) {
            throw new \LogicException('response_factory is missing');
        }
        $this->options = $options;
    }

    public static function init(string $path): void
    {
        if (! file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('%s does not exist', $path));
        }
        self::$instance = new self(require $path);
    }

    public static function createServerRequest(): ServerRequestInterface
    {
        $serverRequest = self::getApp()->options['server_request'];
        return $serverRequest();
    }

    public static function getResponseFactory(): ResponseFactoryInterface
    {
        $responseFactory = self::getApp()->options['response_factory'];
        return $responseFactory();
    }

    private static function getApp(): self
    {
        if (self::$instance === null) {
            throw new \LogicException('Please call ::init() method before get ' . self::class);
        }
        return self::$instance;
    }
}
