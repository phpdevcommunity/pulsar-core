<?php

declare(strict_types=1);

namespace Pulsar\Core\Http;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
class NotFoundException extends HttpException
{
    protected static $defaultMessage = 'Not Found';

    public function __construct(?string $message = null, ?int $code = null, \Throwable $previous = null)
    {
        parent::__construct(404, $message, $code, $previous);
    }
}
