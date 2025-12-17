<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$config = Yaml::parseFile(__DIR__ . '/../../config.yml');

// View will use $config variable
