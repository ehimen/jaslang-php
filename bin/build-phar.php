<?php

$pharFile = __DIR__ . '/jaslang.phar';

if (!Phar::canWrite()) {
    echo 'Cannot write phar. Please check phar.readonly in ini settings' . PHP_EOL;
    exit(1);
}

if (is_writable($pharFile)) {
    echo "Removing existing file at $pharFile" . PHP_EOL;
    @unlink($pharFile);
}

$phar = new Phar($pharFile, 0, 'jaslang.phar');

$phar->startBuffering();

$phar->convertToExecutable(Phar::PHAR);

// TODO: not include everything!
$files = $phar->buildFromDirectory(__DIR__ . '/../');

$phar['index.php'] = file_get_contents(__DIR__ . '/jaslang.php');

$phar->setMetadata(['bootstrap' => 'index.php']);

echo 'Writing ' . $pharFile . PHP_EOL;
$phar->stopBuffering();