<?php

namespace Pulsar\Core\Http;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
class HttpException extends \Exception implements HttpExceptionInterface
{
    /**
     * @var string|null
     */
    protected static $defaultMessage = null;

    /**
     * @var int
     */
    private $statusCode;

    public function __construct(int $statusCode, ?string $message = null, ?int $code = null, \Throwable $previous = null)
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
}
