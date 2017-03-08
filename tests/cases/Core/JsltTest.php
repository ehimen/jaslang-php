<?php

namespace Ehimen\JaslangTests\Core;

use PHPUnit\Framework\TestCase;

/**
 * High-level tests for Jaslang evaluation.
 *
 * Runs test files (.jslt) in tests/resources/jsltests
 */
class JsltTest extends TestCase
{
    use JaslangTestCase;

    const TESTDIR = __DIR__ . '/../../resources/jsltests';
    const TESTFILEREGEX = '#\.jslt$#';

    /**
     * @dataProvider provideFiles
     */
    public function testFile($expected, $code, $error, $input)
    {
        $result = $this->getInterpreter($input)->run($code);

        $actualError = $result->getError();

        if ($error) {
            $this->assertSame(trim($error), trim((string)$actualError));
        } else {
            $this->assertNull($actualError, 'Encountered unexpected error: ' . PHP_EOL . (string)$actualError);
        }

        $this->assertSame($expected, $result->getOut());
    }

    public function provideFiles()
    {
        $files = $this->getTestFiles();
        $cases = [];

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            
            $isOnlyTestFile = (strpos(strrev($file), 'tlsj.!') === 0);
            
            if ($isOnlyTestFile) {
                $cases = [];
            }

            $tests = preg_split('/!>(\S+)\n/', $contents, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

            if (1 === count($tests)) {
                $tests = ['', reset($tests)];
            }

            for ($i = 0; $i < count($tests); $i += 2) {
                $case    = isset($tests[$i]) ? $tests[$i] : null;
                $content = isset($tests[$i + 1]) ? $tests[$i + 1] : null;

                if (!is_string($content) || !is_string($case)) {
                    throw new \Exception('Invalid jsl test file contents in file: ' . $file);
                }

                $lines = explode(PHP_EOL, $content);

                $buffer      = '';
                $parts       = [];
                $currentPart = null;
                $lineCount  = count($lines);

                foreach ($lines as $index => $line) {

                    $isLastLine = ($index === ($lineCount -1));

                    if (preg_match('/^>>>([A-Z]+)$/', $line, $matches) || $isLastLine) {

                        if (isset($currentPart)) {
                            $parts[$currentPart] = (strlen($buffer) > 0) ? substr($buffer, 0, -1) : $buffer;
                        }

                        $buffer = '';
                        $currentPart = $matches[1];

                    } else {
                        $buffer .= $line.PHP_EOL;
                    }
                }

                $expected = isset($parts['EXPECTED']) ? $parts['EXPECTED'] : null;
                $code     = isset($parts['CODE']) ? $parts['CODE'] : null;
                $error    = isset($parts['ERROR']) ? $parts['ERROR'] : null;
                $input    = isset($parts['INPUT']) ? $parts['INPUT'] : null;

                if (!is_string($expected) || !is_string($code)) {
                    throw new \Exception('Invalid jsl test file contents in file: ' . $file);
                }

                $testArgs = [$expected, $code, $error, $input];

                if (is_string($case) && strrev($case)[0] === '!') {
                    // If the case ends in !, only run this test.
                    $cases = [$file . '#' . $case => $testArgs];
                    break 2;
                } else {
                    $cases[$file . '#' . $case] = $testArgs;
                }
            }
            
            if ($isOnlyTestFile) {
                break;
            }
        }

        return $cases;
    }

    private function getTestFiles()
    {
        $directory    = new \RecursiveDirectoryIterator(static::TESTDIR);
        $iterator     = new \RecursiveIteratorIterator($directory);
        $fileIterator = new \RegexIterator($iterator, static::TESTFILEREGEX);

        return array_map(
            function (\SplFileInfo $fileInfo) {
                return $fileInfo->getRealPath();
            },
            iterator_to_array($fileIterator)
        );
    }
}
