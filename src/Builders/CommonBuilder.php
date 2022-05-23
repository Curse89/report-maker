<?php

namespace ReportMaker\Builders;

use JsonException;

class CommonBuilder extends Builder
{
    protected const OUTPUT_REPORT_FILE = "gl-code-quality-report.json";

    protected const REPORTS = [
        CsBuilder::CUSTOM_OUTPUT_REPORT_FILE,
        CsBuilder::PHPCOMPATIBILITY_OUTPUT_REPORT_FILE,
        MdBuilder::OUTPUT_REPORT_FILE,
        StanBuilder::OUTPUT_REPORT_FILE
    ];

    /**
     * @throws JsonException
     */
    public function exec(): void
    {
        $lintRes = [];

        foreach (self::REPORTS as $report) {
            if (\file_exists($report)) {
                $lintRes[] = \json_decode(
                    \file_get_contents($report),
                    false,
                    512,
                    JSON_THROW_ON_ERROR
                );
            }
        }

        if (!empty($lintRes)) {
            $lintRes = \array_merge(...$lintRes);
        }

        \file_put_contents(
            self::OUTPUT_REPORT_FILE,
            \json_encode($lintRes, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
        );
    }
}
