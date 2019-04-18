<?php
/*
 * This file is part of the jojo1981/json-ast-builder package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */

\call_user_func(static function () {
    if (!\is_file($autoloadFile = __DIR__ . '/../vendor/autoload.php')) {
        throw new \RuntimeException('Did not find vendor/autoload.php. Did you run "composer install --dev"?');
    }

    /** @var \Composer\Autoload\ClassLoader $loader */
    $loader = require $autoloadFile;
    $loader->addPsr4('Jojo1981\\JsonAstBuilder\\TestSuite\\', __DIR__);

});
