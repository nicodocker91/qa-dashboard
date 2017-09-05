<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Generalisation;

/**
 * Interface ToolDashboardBuilderInterface
 *
 * Interface that must be implemented by each Tool class buildable through the ToolFactory.
 * Defines the methods that must returns the HTML string for the whole tab of the tool and the HTML that summarize it.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
interface ToolDashboardBuilderInterface
{
    /**
     * Returns the HTML that must be part of the menu in the dashboard.
     * @return string
     */
    public function getHTMLMenu(): string;

    /**
     * Returns the HTML that must be part of the whole tab about the current Tool.
     * @return string
     */
    public function getHTMLTab(): string;

    /**
     * Returns the HTML that must be part of the summary about the current Tool to be used in the Dashboard Home.
     * @return string
     */
    public function getHTMLSummary(): string;
}
