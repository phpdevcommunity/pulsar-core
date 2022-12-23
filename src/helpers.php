<?php

use Psr\Http\Message\ResponseInterface;
use Pulsar\Core\App;

if (!function_exists('send')) {
    function send(ResponseInterface $response)
    {
        $httpLine = sprintf('HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        if (!headers_sent()) {
            header($httpLine, true, $response->getStatusCode());

            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header("$name: $value", false);
                }
            }
        }

        echo $response->getBody();
    }
}

if (!function_exists('response')) {

    function response(string $content = '', int $status = 200): ResponseInterface
    {
        $response = App::getResponseFactory()->createResponse($status);
        $response->getBody()->write($content);
        return $response;
    }
}

if (!function_exists('json_response')) {

    function json_response(array $data, int $status = 200, int $flags = JSON_HEX_TAG
    | JSON_HEX_APOS
    | JSON_HEX_AMP
    | JSON_HEX_QUOT
    | JSON_UNESCAPED_SLASHES): ResponseInterface
    {
        $response = App::getResponseFactory()->createResponse($status);
        $response->getBody()->write(json_encode($data, $flags));
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                sprintf('Unable to encode data to JSON : %s', json_last_error_msg())
            );
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}

if (!function_exists('redirect')) {

    function redirect(string $url, int $status = 302): ResponseInterface
    {
        $response = App::getResponseFactory()->createResponse($status);
        return $response->withHeader('Location', $url);
    }
}

if (!function_exists('render_view')) {

    function render_view(string $view, array $context = []): string
    {
        $view = App::getTemplateDir() . DIRECTORY_SEPARATOR . $view;
        if (!file_exists($view)) {
            throw new Exception(sprintf('The file %s could not be found.', $view));
        }

        extract($context);
        ob_start();
        include($view);
        return trim(ob_get_clean());
    }
}

if (!function_exists('render')) {

    function render(string $view, array $context = [], int $status = 200): ResponseInterface
    {
        return response(render_view($view, $context), $status);
    }
}

if (!function_exists('__e')) {

    function __e(string $str): string
    {
        return htmlentities($str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('asset')) {

    function asset(string $path): string
    {
        return App::getPublicDir() . DIRECTORY_SEPARATOR . $path;
    }
}

if (!function_exists('dd')) {

    function dd(...$data)
    {
        dump(...$data);
        exit;
    }
}

if (!function_exists('dump')) {

    function dump(...$data)
    {
        echo '<pre style="color: #3b4351;
            background-color: #f1f1f1;
            border: 1px dashed #03a9f4;
            margin:0.3rem;
            padding:0.3rem;
            overflow: auto;
            line-height: 1rem;
            white-space: pre-wrap;
            white-space: -moz-pre-wrap;
            white-space: -o-pre-wrap;
            word-wrap: break-word;">';
        var_dump(...$data);
        echo "</pre>";
    }
}
