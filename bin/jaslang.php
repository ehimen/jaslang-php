<?php

// TODO: de-rubbish this.

use Ehimen\Jaslang\Engine\Evaluator\Exception\RuntimeException;
use Ehimen\Jaslang\Engine\Parser\Exception\SyntaxErrorException;
use Ehimen\Jaslang\Core\JaslangFactory;

require_once 'vendor/autoload.php';

if (!isset($argv[1])) {
    echo 'Must specify file' . PHP_EOL;
    exit(1);
}

$fromPhar = false; // Overwritten by phar build script.
/* %FROM_PHAR% */

if ($fromPhar) {
    if ($argv[1][0] !== '/') {
        $argv[1] = getcwd() . '/' . $argv[1];
    }

    $fileName = 'file://' . $argv[1];
} else {
    $fileName = $argv[1];
}

if (false === ($fp = fopen($fileName, 'r'))) {
    echo "Cannot open " . $fileName . ' for reading' . PHP_EOL;
    exit(1);
}

$jaslang = stream_get_contents($fp);

if (strlen($jaslang) === 0) {
    echo '';
    exit(0);
}

$result = (new JaslangFactory())->create()->run($jaslang);

$error = $result->getError();

if ($error instanceof RuntimeException) {
    echo $error->getMessage();
    echo PHP_EOL;
    echo $error->getEvaluationTrace()->getAsString();
    echo PHP_EOL;
    exit(1);
} elseif ($error instanceof SyntaxErrorException) {
    echo $error->getMessage();
    echo PHP_EOL;
    exit(1);
} else {
    echo $result->getOut();
}

exit(0);
