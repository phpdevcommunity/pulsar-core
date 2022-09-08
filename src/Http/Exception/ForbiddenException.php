<?php

declare(strict_types=1);

namespace Pulsar\Core\Http\Exception;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
class ForbiddenException extends HttpException
{
    protected static $defaultMessage = 'Access Denied';

    public function __construct(?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(403, $message, $code, $previous);
    }
}
