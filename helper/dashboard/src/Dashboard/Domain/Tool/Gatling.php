<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Tool;

use Dashboard\Domain\Generalisation\ToolDashboardInterface;
use Dashboard\Infrastructure\TraitSummary;

/**
 * Class Gatling
 *
 * This class manages data for the Gatling Tool logs.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class Gatling implements ToolDashboardInterface
{
    use TraitSummary;

    /** @var string Name of the folder where all logs of this tool are stored. */
    public const LOG_FOLDER_NAME = 'gatling';

    /** @var string Human readable name of the tool. */
    public const TOOL_NAME = 'Gatling';

    /** @var float Coefficient taken to calculate the global ranking. */
    public const SUMMARY_COEFFICIENT = 0;

    /**
     * @inheritDoc
     */
    public function getHTMLMenu(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getHTMLTab(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getHTMLSummary(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function calculateSummary(): ?float
    {
        return null;
    }
}
