<?php

namespace ReportMaker\Builders;

use Symfony\Component\Process\Process;

class CsFixerBuilder extends Builder
{
    protected const BIN_DIR = self::VENDOR_BIN_DIR . "php-cs-fixer";

    protected const CONFIG_FILE = "./.php-cs-fixer.dist.php";

    public const OUTPUT_REPORT_FILE = "cs-fixer-diff-report.xml";

    protected array $files;

    public function __construct($files)
    {
        $this->files = $files;
    }

    public function exec(): void
    {
        $dom = new \DOMDocument("1.0", "UTF-8");
        $dom->loadXML("<testsuites></testsuites>");
        $dom->formatOutput = true;

        foreach ($this->files as $file) {
            if (file_exists($file)) {
                $process = new Process(
                    [
                        self::BIN_DIR,
                        "fix",
                        $file,
                        "--dry-run",
                        "--config=" . self::CONFIG_FILE,
                        "--format=junit",
                        "--diff",
                        "--using-cache=no",
                        "-vvv"
                    ]
                );
                $process->run();

                $currentDom = new \DOMDocument();
                $currentDom->loadXML($process->getOutput());

                $testSuite = $currentDom->getElementsByTagName('testsuite')->item(0);

                if (!empty($testSuite)) {
                    $node = $dom->importNode($testSuite, true);
                    $dom->documentElement->appendChild($node);
                }
            }
        }

        $dom->save(self::OUTPUT_REPORT_FILE);
    }
}
