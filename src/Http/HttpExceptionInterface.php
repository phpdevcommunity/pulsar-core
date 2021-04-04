<?php

namespace Pulsar\Core\Http;

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

}
