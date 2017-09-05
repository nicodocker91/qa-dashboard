<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Entity;

use Dashboard\Domain\Factory\ToolFactory;
use Dashboard\Domain\Generalisation\ToolDashboardSummaryInterface;
use Dashboard\Domain\Services\Summary;
use Dashboard\Infrastructure\{
    Exception\ToolException, Parameters, View
};

/**
 * Class Dashboard
 *
 * This class is the main Dashboard class that is the entry point of the script for object relations.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class Dashboard
{
    /** @var Summary Object that is used to prepare and calculate summaries for each tools. */
    protected $summary;

    /**
     * Dashboard constructor.
     *
     * @param Summary $summary The Summary object used to calculate summaries.
     */
    public function __construct(Summary $summary)
    {
        $this->summary = $summary;
    }

    /**
     * Parses all data tools in the log folders to build dashboard data.
     *
     * @return Dashboard
     * @throws ToolException
     */
    public function parseTools(): Dashboard
    {
        $folderToAnalyze = Parameters::get('path-log');
        foreach (\glob($folderToAnalyze . '/*', \GLOB_ONLYDIR) as $logFolder) {
            $tool = ToolFactory::build(\basename($logFolder));
            if ($tool instanceof ToolDashboardSummaryInterface) {
                $tool->setSummary($this->summary);
            }
        }

        return $this;
    }

    /**
     * Detects and assign in View the build date time.
     *
     * @return Dashboard
     */
    public function detectBuildTime(): Dashboard
    {
        $buildReleaseNumber = Parameters::get('build-release');
        [$year, $month, $day, $hour, $minute] = \sscanf($buildReleaseNumber, '%4d%2d%2d%2d%2d');
        $dateString = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute;

        // If hour and minutes are "00:00", consider the build as the day only
        if (0 === $hour && 0 === $minute) {
            // Format looks like "Monday 27 June 1994"
            View::getInstance()->set('DashboardBuildDate_human', \strftime('%A %d %B %Y', \strtotime($dateString)));
        } else {
            // Format looks like "Monday 27 June 1994 at 21:35"
            View::getInstance()->set('DashboardBuildDate_human', \strftime('%A %d %B %Y at %R', \strtotime($dateString)));
        }

        return $this;
    }

    /**
     * Exports in file the summary and the HTML dashboard.
     *
     * @return Dashboard
     */
    public function export(): Dashboard
    {
        $folder = Parameters::get('path-log');
        \file_put_contents($folder . '/summary.json', $this->summary->calculateGlobal()->export());
        \file_put_contents($folder . '/dashboard.html', View::getInstance()->import('dashboard.phtml'));

        return $this;
    }
}
