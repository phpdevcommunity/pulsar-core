<?php

declare(strict_types=1);

namespace Pulsar\Core\Http\Exception;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
class UnauthorizedException extends HttpException
{
    protected static $defaultMessage = 'Unauthorized';

    public function __construct(?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(401, $message, $code, $previous);
    }
}
