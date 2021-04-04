<?php

namespace Pulsar\Core\Http;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
class ForbiddenException extends HttpException
{
    protected static $defaultMessage = 'Access Denied';

    public function __construct(?string $message = null, ?int $code = null, \Throwable $previous = null)
    {
        parent::__construct(403, $message, $code, $previous);
    }
}
