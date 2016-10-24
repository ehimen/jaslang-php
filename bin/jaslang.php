<?php

// TODO: de-rubbish this.

require_once 'vendor/autoload.php';

if (!isset($argv[1])) {
    echo 'Must specify file' . PHP_EOL;
    exit(1);
}

if ($argv[1][0] !== '/') {
    $argv[1] = getcwd() . '/' . $argv[1];
}

$fileName = 'file://' . $argv[1];

if (false === ($fp = fopen($fileName, 'r'))) {
    echo "Cannot open " . $fileName . ' for reading' . PHP_EOL;
    exit(1);
}

$jaslang = stream_get_contents($fp);

if (strlen($jaslang) === 0) {
    echo '';
    exit(0);
}

echo (new Ehimen\Jaslang\JaslangFactory())->create()->evaluate($jaslang);

echo PHP_EOL;

exit(0);