<?php
declare(strict_types = 1);

spl_autoload_register(function ($class) {
    include __DIR__ . '/../src/' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
});
