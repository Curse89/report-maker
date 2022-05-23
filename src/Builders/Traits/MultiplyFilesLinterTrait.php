<?php

namespace ReportMaker\Builders\Traits;

trait MultiplyFilesLinterTrait
{
    protected array $files;

    protected function getExistenceFiles(): string
    {
        $existFiles = "";

        $this->files = \array_filter($this->files, static function (string $file) {
            return \file_exists($file);
        });

        if (!empty($this->files)) {
            $existFiles = \implode(" ", $this->files);
        }

        return $existFiles;
    }
}
