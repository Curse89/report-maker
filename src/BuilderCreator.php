<?php

namespace ReportMaker;

use ReportMaker\Builders\Builder;

use Symfony\Component\Console\Input\ArgvInput;

use ReportMaker\Builders\MdBuilder;

class BuilderCreator
{
    protected const TYPE_MD = "phpmd";
    protected const TYPE_CS_FIXER = "php-cs-fixer";

    protected const MODE_DEFINITION = ["--mode", "-m"];

    public ArgvInput $input;

    public function __construct()
    {
        $this->input = new ArgvInput();
    }

    public function createBuilder(): Builder
    {
        $mode = $this->getParameter(self::MODE_DEFINITION, null);

        switch ($mode) {
            case self::TYPE_MD:
                $args = $this->getParameters(MdBuilder::getRequiredParameters());

                return new MdBuilder(...$args);

            default:
                throw new \RuntimeException("The type parameter has an invalid value");
        }
    }

    protected static function checkParameter(?string $parameterVal, string $parameterName)
    {
        if (empty($parameterVal)) {
            throw new \RuntimeException("Parameter '$parameterName' mush have a value");
        }
    }

    protected function getParameter(array $definition, ?string $default): string
    {
        $parameterVal = $this->input->getParameterOption($definition, $default);
        $parameterName = mb_substr($definition[0], 0, 2);

        static::checkParameter($parameterVal, $parameterName);

        return $parameterVal;
    }

    protected function getParameters(array $parameters): array
    {
        $args = [];
        foreach ($parameters as $parameter) {
            $args[] = $this->$parameter['method']($parameter['definition'], null);
        }

        return $args;
    }

    protected function getChangedFiles(array $definition, ?string $default): ?array
    {
        $files = $this->getParameter($definition, $default);

        return explode(" ", $files);
    }
}
