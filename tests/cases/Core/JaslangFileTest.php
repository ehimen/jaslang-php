<?php

namespace Ehimen\JaslangTests\Core;

use Ehimen\Jaslang\Core\JaslangFactory;
use Ehimen\Jaslang\Engine\Interpreter;

class JaslangFileTest extends \PHPUnit_Framework_TestCase
{
    const TESTDIR = __DIR__ . '/../../resources/jsltests';
    const TESTFILEREGEX = '#\.jslt$#';

    /**
     * @dataProvider provideFiles
     */
    public function testFile($expected, $code)
    {
        $this->assertSame($expected, $this->getInterpreter()->run($code));
    }

    public function provideFiles()
    {
        $files = $this->getTestFiles();
        $cases = [];
        
        foreach ($files as $file) {
            $contents = file_get_contents($file);
            
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

                $parts = preg_split('/>>>EXPECTED\n|\n>>>CODE\n/', $content);

                $expected = isset($parts[1]) ? $parts[1] : null;
                $code     = isset($parts[2]) ? $parts[2] : null;

                if (!is_string($expected) || !is_string($code)) {
                    throw new \Exception('Invalid jsl test file contents in file: ' . $file);
                }

                $cases[$file . '#' . $case] = [$expected, $code];
            }
        }
        
        return $cases;
    }

    /**
     * @return Interpreter
     */
    private function getInterpreter()
    {
        return (new JaslangFactory)->create();
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
