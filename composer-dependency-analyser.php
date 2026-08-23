<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;

return (new Configuration())
    ->addPathToScan(__DIR__ . '/src', false)
    ->addPathToScan(__DIR__ . '/tests', true)
    ->addPathToScan(__DIR__ . '/scripts', true);
