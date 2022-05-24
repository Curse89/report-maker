<?php

namespace ReportMaker\Builders;

use JsonException;
use ReportMaker\Builders\Traits\MultiplyFilesLinterTrait;
use Symfony\Component\Process\Process;

class StanBuilder extends Builder
{
    use MultiplyFilesLinterTrait;

    protected const BIN_DIR = self::VENDOR_BIN_DIR . "phpstan";

    protected const STAN_CONFIG_DIR = self::BASE_CONFIG_DIR . "phpstan/";

    protected const CONFIG_FILE = "phpstan.neon";

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
        $this->copyStanFilesToProject();

        $this->getExistenceFiles();

        $lintRes = [];

        if (!empty($this->files)) {
            $process = new Process(
                [
                    self::BIN_DIR,
                    "analyse",
                    "-l",
                    "7",
                    "-c",
                    self::CONFIG_FILE,
                    ...$this->files,
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

    protected function copyStanFilesToProject(): void
    {
        $files = \scandir(self::STAN_CONFIG_DIR);

        foreach ($files as $file) {
            if (strpos($file, ".")) {
                if (\strpos($file, ".neon")) {
                    $destinationFile = "$file";
                } else {
                    $destinationFile = "tests/$file";
                }

                if (!\file_exists($destinationFile)) {
                    \copy(self::STAN_CONFIG_DIR . $file, $destinationFile);
                }
            }
        }
    }
}
