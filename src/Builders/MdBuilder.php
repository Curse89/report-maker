<?php

namespace ReportMaker\Builders;

use Symfony\Component\Process\Process;

class MdBuilder extends Builder
{
    protected const REPORT_CLASS = self::BASE_REPORT_CLASS . "MessDetector\\Gitlab";

    protected const BIN_DIR = self::VENDOR_BIN_DIR . "phpmd";

    protected array $files;

    public function __construct($files)
    {
        $this->files = $files;
    }

    /**
     * @throws \JsonException
     */
    public function exec(): void
    {
        $lintRes = [];

        foreach ($this->files as $file) {
            if (file_exists($file)) {
                $process = new Process([self::BIN_DIR, $file, self::REPORT_CLASS, "controversial,./phpmd.xml.dist"]);
                $process->run(
                    function ($type, $buffer) {
                        if (Process::ERR === $type) {
                            echo 'ERR > '.$buffer;
                        } else {
                            echo 'OUT > '.$buffer;
                        }
                    }
                );

                /*if (!$process->isSuccessful()) {                     КОД завершения процесса всегда 2 - ОШИБКА
                    throw new ProcessFailedException($process);
                }*/

                $lintRes[] = \json_decode($process->getOutput(), false, 512, JSON_THROW_ON_ERROR);
            }
        }

        if (!empty($lintRes)) {
            $lintRes = array_merge(...$lintRes);
            file_put_contents('phpmd-report.json', json_encode($lintRes, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        }
    }
}
