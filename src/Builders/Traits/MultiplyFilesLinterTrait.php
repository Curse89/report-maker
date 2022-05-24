<?php

namespace ReportMaker\Builders\Traits;

trait MultiplyFilesLinterTrait
{
    protected array $files;

    protected function getExistenceFiles(): void
    {
        $this->files = \array_filter($this->files, static function (string $file) {
            return \file_exists($file);
        });
    }
}
