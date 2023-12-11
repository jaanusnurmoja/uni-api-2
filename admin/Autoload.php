<?php

spl_autoload_register(function ($class) {
    if (file_exists(__DIR__ . '/' . $class . '.php')) {
        include_once __DIR__ . '/' . $class . '.php';
    }

});