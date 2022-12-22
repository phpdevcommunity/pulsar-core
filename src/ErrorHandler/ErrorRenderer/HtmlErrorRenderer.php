<?php

namespace Pulsar\Core\ErrorHandler\ErrorRenderer;

use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Pulsar\Core\Http\Exception\HttpExceptionInterface;
use function dirname;
use function extract;
use function file_exists;
use function ob_get_clean;
use function ob_start;
use function sprintf;

final class HtmlErrorRenderer
{
    private ResponseFactoryInterface $responseFactory;
    private bool $debug;
    private ?string $templateDir;

    public function __construct(ResponseFactoryInterface $responseFactory, bool $debug = false, ?string $templateDir = null)
    {
        $this->responseFactory = $responseFactory;
        $this->debug = $debug;

        if (is_string($templateDir)) {
            if (!file_exists($templateDir) || !is_dir($templateDir)) {
                throw new InvalidArgumentException(sprintf('%s does not exist', $templateDir));
            }
            $this->templateDir = rtrim($templateDir, '/');
        }
    }

    public function __invoke(HttpExceptionInterface $exception): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($exception->getStatusCode());
        if ($this->isDebug() === false) {
            $template = $this->findTemplate($exception->getStatusCode());
            if ($template !== null) {
                $response->getBody()->write($this->include($template, [
                    'exception' => $exception,
                ]));
            }
            return $response;
        }

        $template = dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources/views/error.html.php';
        $response->getBody()->write($this->include($template, [
            'e' => $exception,
            "server" => $_SERVER,
            "env" => $_ENV

        ]));
        return $response;
    }

    private function include(string $template, array $context = []): string
    {
        if (!file_exists($template)) {
            throw new InvalidArgumentException(sprintf('%s does not exist', $template));
        }
        
        extract($context);
        ob_start();
        include($template);

        return trim(ob_get_clean());
    }
    
    public function isDebug(): bool
    {
        return $this->debug;
    }

    private function findTemplate(int $statusCode): ?string
    {
        $template = $this->templateDir . DIRECTORY_SEPARATOR . sprintf('error%s.html.php', $statusCode);
        if (file_exists($template)) {
            return $template;
        }

        $template = $this->templateDir . DIRECTORY_SEPARATOR . 'error.html.php';

        return file_exists($template) ? $template : null;
    }
}
