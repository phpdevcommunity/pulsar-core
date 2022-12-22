<?php

declare(strict_types=1);

namespace Pulsar\Core\Http\Exception;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
class HttpException extends \Exception implements HttpExceptionInterface
{
    /**
     * @var string|null
     */
    protected static ?string $defaultMessage = 'An error occurred';

    /**
     * @var int
     */
    private int $statusCode;

    public function __construct(int $statusCode, ?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        if ($message === null) {
            $message = static::$defaultMessage;
        }

        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getDefaultMessage(): string
    {
        return static::$defaultMessage;
    }
}
