<?php

namespace ReportMaker\Builders;

use JsonException;
use Symfony\Component\Process\Process;

class MdBuilder extends Builder
{
    protected const REPORT_CLASS = self::BASE_REPORT_CLASS . "MessDetector\\Gitlab";

    protected const BIN_DIR = self::VENDOR_BIN_DIR . "phpmd";

    protected const CONFIG_FILE = "./phpmd.xml.dist";

    public const OUTPUT_REPORT_FILE = "phpmd-report.json";

    protected array $files;

    public function __construct($files)
    {
        $this->files = $files;
    }

    /**
     * @throws JsonException
     */
    public function exec(): void
    {
        $lintRes = [];

        foreach ($this->files as $file) {
            if (file_exists($file)) {
                $process = new Process(
                    [
                        self::BIN_DIR,
                        $file,
                        self::REPORT_CLASS,
                        "controversial," . self::CONFIG_FILE
                    ]
                );
                $process->run();

                $lintRes[] = \json_decode($process->getOutput(), false, 512, JSON_THROW_ON_ERROR);
            }
        }

        if (!empty($lintRes)) {
            $lintRes = array_merge(...$lintRes);
        }

        \file_put_contents(
            self::OUTPUT_REPORT_FILE,
            \json_encode($lintRes, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
        );
    }
}
