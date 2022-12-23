<?php

namespace Pulsar\Core\Package;

interface PackageInterface
{
    public function getDefinitions(): array;

    public function getParameters(): array;

    public function getListeners(): array;

    public function getRoutes(): array;

    public function getCommands(): array;
}