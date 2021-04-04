<?php

namespace Pulsar\Core\Http;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
class BadRequestException extends HttpException
{
    protected static $defaultMessage = 'Bad Request';

    public function __construct(?string $message = null, ?int $code = null, \Throwable $previous = null)
    {
        parent::__construct(400, $message, $code, $previous);
    }
}
