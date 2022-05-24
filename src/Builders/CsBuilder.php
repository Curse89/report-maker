<?php

namespace ReportMaker\Builders;

use ReportMaker\Builders\Traits\MultiplyFilesLinterTrait;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CsBuilder extends Builder
{
    use MultiplyFilesLinterTrait;

    protected const REPORT_CLASS = self::BASE_REPORT_CLASS . "CodeSniffer\\Gitlab";

    protected const BIN_DIR = self::VENDOR_BIN_DIR . "phpcs";

    protected const STAND_PHPCOMPATIBILITY = "PHPCompatibility";
    protected const STAND_CUSTOM = self::BASE_CONFIG_DIR . "phpcs.xml.dist";

    public const PHPCOMPATIBILITY_OUTPUT_REPORT_FILE = "php_compatibility-report.json";
    public const CUSTOM_OUTPUT_REPORT_FILE = "phpcs-report.json";

    protected static array $requiredParameters = [
        'files' => [
            'definition' => ["--files", "-f"],
            'method' => 'getChangedFiles',
            'nullable' => false
        ],
        'standard' => [
            'definition' => ["--standard", "-s"],
            'method' => 'getParameter',
            'nullable' => true
        ],
        'php_version' => [
            'definition' => ["--version", "-v"],
            'method' => 'getParameter',
            'nullable' => false
        ]
    ];

    protected ?string $standard;

    protected string $version;

    public function __construct($files, $standard, $version)
    {
        $this->files = $files;
        $this->standard = $standard;
        $this->version = $version;
    }

    public function exec(): void
    {
        $this->getExistenceFiles();

        if (!empty($this->files)) {
            if (self::STAND_PHPCOMPATIBILITY === $this->standard) {
                $phpcsConfig = new Process(
                    [
                        self::BIN_DIR,
                        "--config-set",
                        "installed_paths",
                        "vendor/phpcompatibility/php-compatibility"
                    ]
                );
                $phpcsConfig->run();

                if (!$phpcsConfig->isSuccessful()) {
                    throw new ProcessFailedException($phpcsConfig);
                }

                $process = new Process(
                    [
                        self::BIN_DIR,
                        "-s",
                        "-p",
                        ...$this->files,
                        "--standard=" . self::STAND_PHPCOMPATIBILITY,
                        "--report-" . self::REPORT_CLASS,
                        "--report-file=" . self::PHPCOMPATIBILITY_OUTPUT_REPORT_FILE,
                        "--no-cache",
                        "--runtime-set",
                        "testVersion",
                        $this->version
                    ]
                );

                $process->run();
            } elseif (self::STAND_CUSTOM === $this->standard || null === $this->standard) {
                $command = [
                    self::BIN_DIR,
                    "-p",
                    ...$this->files,
                    "--report-" . self::REPORT_CLASS,
                    "--report-file=" . self::CUSTOM_OUTPUT_REPORT_FILE,
                    "--no-cache",
                    "--runtime-set",
                    "testVersion",
                    $this->version
                ];

                if (self::STAND_CUSTOM === $this->standard) {
                    $command[] = "--standard=" . self::STAND_CUSTOM;
                }

                $process = new Process($command);

                $process->run();
            }
        }
    }
}
