<?php

declare(strict_types=1);

namespace Pulsar\Core;

use DevCoder\Resolver\Option;
use DevCoder\Resolver\OptionsResolver;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Pulsar\Core\Util\ExceptionHandler;

/**
 * @package    Pulsar
 * @author    Devcoder.xyz <dev@devcoder.xyz>
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link    https://www.devcoder.xyz
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
        $resolver = new OptionsResolver([
            (new Option('server_request'))->validator(static function ($value) {
                return $value instanceof \Closure;
            }),
            (new Option('response_factory'))->validator(static function ($value) {
                return $value instanceof \Closure;
            }),
            (new Option('container_builder'))->validator(static function ($value) {
                return $value instanceof \Closure;
            }),
            (new Option('custom_environments'))->validator(static function ($value) {
                if (is_array($value) === false) {
                    return false;
                }
                $environmentsFiltered = array_filter($value, function ($value) {
                    return is_string($value) === false;
                });
                if ($environmentsFiltered !== []) {
                  throw new \InvalidArgumentException('custom_environments array values must be string only');
                }
                return true;

            })->setDefaultValue([])
        ]);
        $this->options = $resolver->resolve($options);
    }

    public static function init(string $path): void
    {
        if (!file_exists($path)) {
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

    public static function createContainerBuilder(): \Closure
    {
        return self::getApp()->options['container_builder'];
    }

    public static function getCustomEnvironments(): array
    {
        return self::getApp()->options['custom_environments'];
    }

    private static function getApp(): self
    {
        if (self::$instance === null) {
            throw new \LogicException('Please call ::init() method before get ' . self::class);
        }
        return self::$instance;
    }
}
