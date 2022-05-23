<?php

namespace ReportMaker\Builders;

use JsonException;
use ReportMaker\Builders\Traits\MultiplyFilesLinterTrait;
use Symfony\Component\Process\Process;

class StanBuilder extends Builder
{
    use MultiplyFilesLinterTrait;

    protected const BIN_DIR = self::VENDOR_BIN_DIR . "phpstan";

    protected const CONFIG_FILE = "./phpstan.neon";

    public const OUTPUT_REPORT_FILE = "phpstan-report.json";

    public function __construct($files)
    {
        $this->files = $files;
    }

    /**
     * @throws JsonException
     */
    public function exec(): void
    {
        $changedFiles = $this->getExistenceFiles();

        $lintRes = [];

        if (!empty($changedFiles)) {
            $process = new Process(
                [
                    self::BIN_DIR,
                    "analyse",
                    "-l",
                    "7",
                    "-c",
                    self::CONFIG_FILE,
                    $changedFiles,
                    "--error-format=gitlab"
                ]
            );

            $process->run();

            $lintRes = \json_decode($process->getOutput(), false, 512, JSON_THROW_ON_ERROR);
        }

        \file_put_contents(
            self::OUTPUT_REPORT_FILE,
            \json_encode($lintRes, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
        );
    }
}
