<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Generalisation;

use Dashboard\Domain\Services\Summary;

/**
 * Interface ToolDashboardSummaryInterface
 *
 * Interface that must be implemented by each Tool class buildable through the ToolFactory.
 * Defines the methods that must returns summaries of data calculated through the analysis.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
interface ToolDashboardSummaryInterface
{
    /**
     * Returns the summary in percentage of success of a tool. Returns NULL if no data calculated.
     * @return null|float
     */
    public function calculateSummary(): ?float;

    /**
     * Set the Summary object and add the summary for the given tool.
     *
     * @param Summary $summary
     * @return $this
     */
    public function setSummary(Summary $summary);
}
