<?php namespace Autoload;

spl_autoload_register(function ($class) {
    include_once $class . '.php';
});
