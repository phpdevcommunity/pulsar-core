<?php

namespace Pulsar\Core\Http;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
class UnauthorizedException extends HttpException
{
    protected static $defaultMessage = 'Unauthorized';

    public function __construct(?string $message = null, ?int $code = null, \Throwable $previous = null)
    {
        parent::__construct(401, $message, $code, $previous);
    }
}
