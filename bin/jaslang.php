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

try {
    echo (new Ehimen\Jaslang\JaslangFactory())->create()->evaluate($jaslang);
} catch (\Ehimen\Jaslang\Evaluator\Exception\RuntimeException $e) {
    echo $e->getMessage();
    echo PHP_EOL;
    echo $e->getEvaluationTrace()->getAsString();
} catch (\Ehimen\Jaslang\Parser\Exception\SyntaxErrorException $e) {
    echo $e->getMessage();
}

echo PHP_EOL;

exit(0);