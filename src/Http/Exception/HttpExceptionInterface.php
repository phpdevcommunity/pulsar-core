<?php

declare(strict_types=1);

namespace Pulsar\Core\Http\Exception;

/**
 * @author Devcoder.xyz <dev@devcoder.xyz>
 */
interface HttpExceptionInterface extends \Throwable
{
    /**
     * Returns the status code.
     *
     * @return int An HTTP response status code
     */
    public function getStatusCode(): int;

    /**
     * Returns the default message status.
     *
     * @return string
     */
    public function getDefaultMessage(): string;
}
