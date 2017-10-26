<?php

if ('cli-server' === PHP_SAPI && is_file(__DIR__ . $_SERVER['REQUEST_URI'])) {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    return false;
}

require '../src/bootstrap.php';
