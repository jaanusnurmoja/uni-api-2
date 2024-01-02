<?php

spl_autoload_register(function ($class) {
    if (file_exists($class . '.php')) {
        include_once $class . '.php';
    }

});