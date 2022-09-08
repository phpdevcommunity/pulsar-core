<?php
/***
 * @var $e Exception
 * @var $env array
 * @var $server array
 */
$exception = $e;
$e = $exception->getPrevious() ?: $exception;

use Pulsar\Core\BaseKernel;
use Pulsar\Core\Http\Exception\HttpExceptionInterface;
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="robots" content="noindex,nofollow,noarchive,nosnippet,noodp,notranslate,noimageindex"/>
    <title><?php echo $e->getMessage(); ?></title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            color: #3b4351;
            background-color: #f3f3f3;
            font-size: 15px;
            margin: 0;
            height: 100vh;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-family: Source Sans Pro,Helvetica Neue,Arial,sans-serif;
        }

        .small {
            font-size: 0.9em;
        }

        .exception-message {
            word-break: break-word;
            font-weight: 500;
            line-height: 1.125;
            font-size: 1.3em;
            margin: 2rem 0;
            padding: 2rem;
            background-color: #f40343;
            color: #ffffff;
            width: 100%;
            text-align: center;
        }

        a {
            color: #3b4351;
            cursor: pointer;
            text-decoration: underline;
        }

        header {
            position: sticky;
            top: 0;
            width: 100%;
            height: auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding: 0.5rem;
            background-color: #3b4351;
        }

        main {
            flex: 1;
        }

        .text-center {
            text-align: center;
        }

        .container {
            width: 100%;
            max-width: 1400px;
            max-height: 100%;
            margin: auto;
            padding: 1rem;
        }

        .badge {
            align-items: center;
            background-color: #077baa;
            color: #fff;
            display: inline-flex;
            font-size: 0.9em;
            height: 1.5em;
            justify-content: center;
            line-height: 1.5;
            padding-left: .45em;
            padding-right: .45em;
            white-space: nowrap;
            margin: 0.2rem;
        }

        p {
            line-height: 1.5rem;
            font-size: 1em;
            margin: 0.2em;
        }

        ul.exception-traces {
            list-style: none;
            padding: 1rem;
            margin: 1rem 0.2rem;
            border: 1px dashed #3b4351;
            background-color: #ffffff;
        }

        ul.exception-traces li {
            border-bottom: 1px dashed #3b4351;
            padding: 1rem;
            display: flex;
            align-items: center;
            word-break: break-word;
        }

        ul.exception-traces li:last-child {
            border-bottom: none;
        }

        ul.exception-traces li .order {
            margin-right: 1rem;
        }

        ul.exception-traces li .class-name {
            color: #3b4351;
            font-weight: 500;
        }

        ul.exception-traces li .file-name {
            color: #3b4351;
            font-weight: 400;
        }
    </style>
</head>
<body>
<header>
    <div class="badge">Pulsar <?php echo BaseKernel::VERSION ?></div>
    <div class="badge">PHP <?php echo phpversion() ?></div>
    <div class="badge">ENVIRONMENT : <?php echo $env['APP_ENV'] ?></div>
    <div class="badge">METHOD : <?php echo $server['REQUEST_METHOD'] ?></div>
    <div class="badge">IP : <?php echo $server['REMOTE_ADDR'] ?></div>
    <?php if ($exception instanceof HttpExceptionInterface): ?>
        <div class="badge">HTTP status : <?php echo $exception->getStatusCode() ?></div>
    <?php endif; ?>
</header>
<main>
    <div class="container">
        <div class="exception-message">
            <p><?php echo $e->getMessage(); ?> - <small><?php echo get_class($e) ?></small></p>
            <i class="small"><?php echo $e->getFile(); ?> line <?php echo $e->getLine(); ?></i>
        </div>
        <ul class="exception-traces">
            <?php $traces = array_reverse($e->getTrace()); ?>
            <?php foreach (array_reverse($traces, true) as $key => $item): ?>
                <?php if (!isset($item['file'])) {
                    continue;
                } ?>
                <li>
                    <div class="order">
                            <span class="badge">
                                <?php echo $key + 1; ?>
                            </span>
                    </div>
                    <div>
                        <div class="class-name"><?php echo $item['class'] ?? $item["function"]; ?></div>
                        <i class="file-name"><?php echo $item['file'] ?? null; ?>
                            line <?php echo $item['line'] ?? null; ?></i>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>
<footer>
    <p class="text-center">
        Made with <span style="color: #e25555;">❤</span> in Paris by
        <a class="mr-4" href="https://www.devcoder.xyz" target="_blank" rel="noopener">
            Devcoder.xyz (Fady M.R)
        </a>
        <a class="ml-4" href="mailto:fadymichel@devcoder.xyz">
            Contact us
        </a>
    </p>
</footer>
</body>
</html>

