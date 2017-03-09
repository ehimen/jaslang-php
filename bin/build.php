<?php

// Builds the jaslang phar file and runs all examples.

$pharScript = __DIR__ . '/build-phar.php';
$phar = __DIR__ . '/jaslang.phar';

echo 'Building PHAR...' . PHP_EOL;
$pharCmd = $_SERVER['_'] . ' ' . escapeshellarg($pharScript);
echo $pharCmd . PHP_EOL;

exec($pharCmd, $output, $result);

if (0 === $result) {
    echo 'Done.' . PHP_EOL;
} else {
    echo 'Failed:' . PHP_EOL;
    foreach ($output as $line) {
        echo $line . PHP_EOL;
    }
    exit(1);
}

echo 'Scanning examples...' . PHP_EOL;

$iterator = new RecursiveDirectoryIterator(__DIR__ . '/../examples/jaslang');
$iterator = new RegexIterator($iterator, '/\.jsl$/');

foreach ($iterator as $item) {
    $output = [];
    /** @var SplFileInfo $item */
    $path = $item->getRealPath();
    
    $cmd = $phar . ' ' . escapeshellarg($path);

    echo PHP_EOL;
    echo 'Running: ' . $path . PHP_EOL;
    
    exec($cmd, $output, $result);
    
    echo "Return $result" . PHP_EOL;
    
    foreach ($output as $line) {
        echo $line . PHP_EOL;
    }

    echo PHP_EOL;
}

echo "Done." . PHP_EOL;
