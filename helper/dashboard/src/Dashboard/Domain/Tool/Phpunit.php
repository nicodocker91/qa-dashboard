<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Tool;

use Dashboard\Domain\Generalisation\ToolDashboardInterface;
use Dashboard\Infrastructure\Parameters;
use Dashboard\Infrastructure\TraitSummary;
use Dashboard\Infrastructure\View;
use SimpleXMLElement;

/**
 * Class Phpunit
 *
 * This class manages data for the Phpunit Tool logs.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class Phpunit implements ToolDashboardInterface
{
    use TraitSummary;

    /** @var string Name of the folder where all logs of this tool are stored. */
    public const LOG_FOLDER_NAME = 'phpunit';

    /** @var string Human readable name of the tool. */
    public const TOOL_NAME = 'PHPUnit with Coverage';

    /** @var float Coefficient taken to calculate the global ranking. */
    public const SUMMARY_COEFFICIENT = 12;

    /** @var string File name of the JUnit XML file report on unit tests. */
    protected const PHPUNIT_REPORT_FILE_UNIT = 'phpunit-unit.xml';

    /** @var string File name of the Clover XML file report on code coverage unit tests. */
    protected const CODE_COVERAGE_UNIT = 'coverage-clover.xml';

    /**
     * Phpunit constructor.
     */
    public function __construct()
    {
        $view = View::getInstance();

        // Initialize total summary.
        $view->set('phpunitData_total_summary_is_success', true);
        $view->set('phpunitData_total_summary_tests_#', 0);
        $view->set('phpunitData_total_summary_assertions_#', 0);
        $view->set('phpunitData_total_summary_errors_#', 0);
        $view->set('phpunitData_total_summary_failures_#', 0);
        $view->set('phpunitData_total_summary_skipped_#', 0);

        $this->parsePhpUnit();

        // Review values of total for formatting.
        $view->set(
            'phpunitData_total_summary_tests_#',
            \number_format($view->get('phpunitData_total_summary_tests_#', 0))
        );
        $view->set(
            'phpunitData_total_summary_assertions_#',
            \number_format($view->get('phpunitData_total_summary_assertions_#', 0))
        );
        $view->set(
            'phpunitData_total_summary_errors_#',
            \number_format($view->get('phpunitData_total_summary_errors_#', 0))
        );
        $view->set(
            'phpunitData_total_summary_failures_#',
            \number_format($view->get('phpunitData_total_summary_failures_#', 0))
        );
        $view->set(
            'phpunitData_total_summary_skipped_#',
            \number_format($view->get('phpunitData_total_summary_skipped_#', 0))
        );
    }

    /**
     * Prepares the summary and the details to help the view display the data of the PHPUnit tests.
     * @return Phpunit
     */
    protected function parsePhpUnit(): Phpunit
    {
        $folder = Parameters::get('path-log') . '/' . static::LOG_FOLDER_NAME;
        $jUnitReport = $folder . \DIRECTORY_SEPARATOR . static::PHPUNIT_REPORT_FILE_UNIT;
        $coverageReport = $folder . \DIRECTORY_SEPARATOR . static::CODE_COVERAGE_UNIT;

        return $this->parseData($jUnitReport)->parseCoverage($coverageReport);
    }

    /**
     * Parses the jUnit reports to extract the values in summary and in details.
     *
     * @param string $jUnitReportFile
     * @return Phpunit
     */
    private function parseData(string $jUnitReportFile): Phpunit
    {
        // Use xml report to get detailed error and warning information.
        if (!\is_readable($jUnitReportFile) || '' === \trim($xml = \file_get_contents($jUnitReportFile))) {
            return $this;
        }

        // Reset each time the _phpunit variable to ensure its existence only if at least one of phpunit group has
        // been run.
        $view = View::getInstance()->set('_phpunit', $this);

        $view->set('phpunitData_unit_exists', true);

        $dataXml = \simplexml_load_string($xml);
        $summaryAttributes = $dataXml->testsuite[0]->attributes();

        $nbTests = (int)$summaryAttributes->tests;
        $nbAssertions = (int)$summaryAttributes->assertions;
        $nbErrors = (int)$summaryAttributes->errors;
        $nbFailures = (int)$summaryAttributes->failures;
        $nbSkipped = (int)$summaryAttributes->skipped;
        $time = (float)$summaryAttributes->time;

        // Add in the total.
        $isSuccess = $view->get('phpunitData_total_summary_is_success', true);
        $view->set('phpunitData_total_summary_is_success', $isSuccess && 0 === ($nbErrors + $nbFailures + $nbSkipped));
        $view->set(
            'phpunitData_total_summary_tests_#',
            $view->get('phpunitData_total_summary_tests_#', 0) + $nbTests
        );
        $view->set(
            'phpunitData_total_summary_assertions_#',
            $view->get('phpunitData_total_summary_assertions_#', 0) + $nbAssertions
        );
        $view->set(
            'phpunitData_total_summary_errors_#',
            $view->get('phpunitData_total_summary_errors_#', 0) + $nbErrors
        );
        $view->set(
            'phpunitData_total_summary_failures_#',
            $view->get('phpunitData_total_summary_failures_#', 0) + $nbFailures
        );
        $view->set(
            'phpunitData_total_summary_skipped_#',
            $view->get('phpunitData_total_summary_skipped_#', 0) + $nbSkipped
        );
        $view->set(
            'phpunitData_total_time',
            $view->get('phpunitData_total_time', 0) + $time
        );

        $nbSuccess = $nbTests - ($nbErrors + $nbFailures + $nbSkipped);
        $view->set('phpunitData_unit_summary_is_success', $nbSuccess === $nbTests);
        $view->set('phpunitData_unit_summary_tests_#', \number_format($nbTests));
        $view->set('phpunitData_unit_summary_assertions_#', \number_format($nbAssertions));
        $view->set('phpunitData_unit_summary_errors_#', \number_format($nbErrors));
        $view->set('phpunitData_unit_summary_failures_#', \number_format($nbFailures));
        $view->set('phpunitData_unit_summary_skipped_#', \number_format($nbSkipped));
        $view->set('phpunitData_unit_summary_success_#', \number_format($nbSuccess));

        $percentageError = 100 * \round($nbErrors / $nbTests, 4);
        $percentageFailure = 100 * \round($nbFailures / $nbTests, 4);
        $percentageSkipped = 100 * \round($nbSkipped / $nbTests, 4);
        $percentageSuccess = 100.0 - ($percentageError + $percentageFailure + $percentageSkipped);

        $view->set('phpunitData_unit_summary_tests_#', \number_format($nbTests));
        $view->set('phpunitData_unit_summary_errors_%', $percentageError);
        $view->set('phpunitData_unit_summary_failures_%', $percentageFailure);
        $view->set('phpunitData_unit_summary_skipped_%', $percentageSkipped);
        $view->set('phpunitData_unit_summary_success_%', $percentageSuccess);

        $view->set('phpunitData_unit_summary_no_success_/', ($nbErrors + $nbFailures + $nbSkipped) / $nbTests);

        // Managed details.
        $aPhpunitDetailed = [];
        foreach ($dataXml->testsuite[0] as $testSuiteTag) {
            /** @var SimpleXMLElement $testSuiteTag */
            $testSuiteTagAttributes = $testSuiteTag->attributes();
            $testCaseDetails = $this->parseTestSuite($testSuiteTag);

            $aPhpunitDetailed[(string)$testSuiteTagAttributes->name] = [
                'tests' => (int)$testSuiteTagAttributes->tests,
                'assertions' => (int)$testSuiteTagAttributes->assertions,
                'errors' => (int)$testSuiteTagAttributes->errors,
                'failures' => (int)$testSuiteTagAttributes->failures,
                'skipped' => (int)$testSuiteTagAttributes->skipped,
                'details' => $testCaseDetails,
                // Score is used to rank the worst test cases. High score means lots of errors, failures and skipped.
                'score' => (int)$testSuiteTagAttributes->errors * ($nbTests ** 2)
                    + (int)$testSuiteTagAttributes->failures * $nbTests
                    + (int)$testSuiteTagAttributes->skipped
            ];
        }

        \uasort($aPhpunitDetailed, function ($a, $b) {
            return ($a['score'] > $b['score'] ? -1 : ($a['score'] < $b['score'] ? 1 : 0));
        });

        $view->set('phpunitData_unit_details', $aPhpunitDetailed);

        return $this;
    }

    /**
     * @param SimpleXMLElement $testSuiteTag
     * @return array
     */
    private function parseTestSuite(SimpleXMLElement $testSuiteTag): array
    {
        $testCaseDetails = [];
        foreach ($testSuiteTag->children() as $testCaseTag) {
            /** @var SimpleXMLElement $testCaseTag */
            if ('testsuite' === $testCaseTag->getName()) {
                $testCaseDetails = \array_merge($testCaseDetails, $this->parseTestSuite($testCaseTag));
            } else {
                $testCaseTagAttributes = $testCaseTag->attributes();
                /** @var SimpleXMLElement $infoTestTag */
                $infoTestTag = $testCaseTag->children()[0];

                // If information about the test case is empty, it is a success.
                if (null === $infoTestTag) {
                    $testCaseDetails[(string)$testCaseTagAttributes->name] = [
                        'file' => (string)$testCaseTagAttributes->file,
                        'line' => (int)$testCaseTagAttributes->line,
                        'type' => 'success',
                        'message' => null,
                    ];
                } else {
                    $testCaseDetails[(string)$testCaseTagAttributes->name] = [
                        'file' => (string)$testCaseTagAttributes->file,
                        'line' => (int)$testCaseTagAttributes->line,
                        'type' => $infoTestTag->getName(),
                        'message' => (string)$infoTestTag,
                    ];
                }
            }
        }

        return $testCaseDetails;
    }

    /**
     * Parses the code coverage file if readable to calculate the percentage of project covered.
     *
     * @param string $coverageReportFile
     * @return Phpunit
     */
    private function parseCoverage(string $coverageReportFile): Phpunit
    {
        // Use xml report to get metrics information.
        if (!\is_readable($coverageReportFile) || '' === \trim($xml = \file_get_contents($coverageReportFile))) {
            return $this;
        }

        $view = View::getInstance();
        $view->set('phpunitData_coverage_unit_exists', true);

        $dataXml = \simplexml_load_string($xml);
        /** @var SimpleXMLElement $metricsTag */
        $metricsTag = $dataXml->project[0]->metrics[0];
        $metricsAttributes = $metricsTag->attributes();

        if (0 === (int)$metricsAttributes->statements) {
            $view->set('phpunitData_coverage_unit_exists', false);
            return $this;
        }

        $ratio = (int)$metricsAttributes->coveredstatements / (int)$metricsAttributes->statements;
        $view->set('phpunitData_coverage_unit_global_/', $ratio);
        $view->set('phpunitData_coverage_unit_global_%', 100 * \round($ratio, 4));

        $badgeType = ['danger', 'warning', 'info', 'success'][($ratio >= .5) + ($ratio >= .75) + ($ratio >= .9)];
        $view->set('phpunitData_coverage_unit_global_badge', $badgeType);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHTMLMenu(): string
    {
        return View::getInstance()->import('blocks/nav/phpunit.phtml');
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
        $view = View::getInstance();
        // If no unit tests: no summary available.
        if (!$view->get('phpunitData_unit_exists', false)) {
            return null;
        }
        // If no coverage: no summary available.
        if (!$view->get('phpunitData_coverage_unit_exists', false)) {
            return null;
        }

        $phpunitSuccess = \max(0, 1 - $view->get('phpunitData_unit_summary_no_success_/'));
        $phpunitCoverage = \min(1, $view->get('phpunitData_coverage_unit_global_/'));

        return 100 * \min(1, \max(0, $phpunitCoverage * $phpunitSuccess));
    }
}
