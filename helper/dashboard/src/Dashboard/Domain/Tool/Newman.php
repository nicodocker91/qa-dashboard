<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Tool;

use Dashboard\Domain\Generalisation\ToolDashboardInterface;
use Dashboard\Infrastructure\Parameters;
use Dashboard\Infrastructure\TraitSummary;
use Dashboard\Infrastructure\View;
use SimpleXMLElement;

/**
 * Class Newman
 *
 * This class manages data for the Newman Tool logs.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class Newman implements ToolDashboardInterface
{
    use TraitSummary;

    /** @var string Name of the folder where all logs of this tool are stored. */
    public const LOG_FOLDER_NAME = 'newman';

    /** @var string Human readable name of the tool. */
    public const TOOL_NAME = 'Newman';

    /** @var string File name of the JUnit XML file report on unit tests. */
    protected const NEWMAN_REPORT_FILE_UNIT = 'newman-junit.xml';

    /** @var float Coefficient taken to calculate the global ranking. */
    public const SUMMARY_COEFFICIENT = 0;

    /**
     * Newman constructor.
     */
    public function __construct()
    {
        $view = View::getInstance();

        // Initialize total summary.
        $view->set('newmanData', true);
        $view->set('newmanData_total_summary_requests_#', 0);
        $view->set('newmanData_total_summary_tests_#', 0);
        $view->set('newmanData_total_summary_passed_#', 0);
        $view->set('newmanData_total_summary_failures_#', 0);
        $view->set('newmanData_details', []);
        $view->set('newmanData_total_time', null);

        $this->parseNewmanUnit();
    }

    /**
     * Prepares the summary and the details to help the view display the data of the PHPUnit tests.
     * @return Newman
     */
    protected function parseNewmanUnit(): Newman
    {
        $folder = Parameters::get('path-log') . '/' . static::LOG_FOLDER_NAME;
        $jUnitReport = $folder . \DIRECTORY_SEPARATOR . static::NEWMAN_REPORT_FILE_UNIT;

        return $this->parseData($jUnitReport);
    }

    /**
     * Parses the jUnit reports to extract the values in summary and in details.
     *
     * @param string $jUnitReportFile
     * @return Newman
     */
    private function parseData(string $jUnitReportFile): Newman
    {
        // Use xml report to get detailed error and warning information.
        if (!\is_readable($jUnitReportFile) || '' === \trim($xml = \file_get_contents($jUnitReportFile))) {
            return $this;
        }

        $view = View::getInstance()->set('_newman', $this);
        $view->set('newmanData_exists', true);

        $dataXml = \simplexml_load_string($xml);
        $mainAttributes = $dataXml->attributes();

        $nbRequests = (int)$mainAttributes->tests;
        $nbTests = 0;
        $nbFailures = 0;
        $nbPassed = 0;
        $time = (float)$mainAttributes->time;

        $aNewmanDetailed = [];
        foreach ($dataXml->children() as $testSuiteTag) {
            /** @var SimpleXMLElement $testSuiteTag */
            $testSuiteTagAttributes = $testSuiteTag->attributes();

            $testPassed = (int)$testSuiteTagAttributes->tests - (int)$testSuiteTagAttributes->failures;

            $aNewmanDetailed[(string)$testSuiteTagAttributes->name] = [
                'tests' => (int)$testSuiteTagAttributes->tests,
                'passed' => $testPassed,
                'success' => 0 === (int)$testSuiteTagAttributes->failures,
                'failures' => (int)$testSuiteTagAttributes->failures,
                'time' => (float)$testSuiteTagAttributes->time,
                'details' => $this->parseTestSuite($testSuiteTag),
                // Score is used to rank the worst test cases.
                'score' => (int)$testSuiteTagAttributes->failures * $nbRequests + $testPassed
            ];
            $nbTests += (int)$testSuiteTagAttributes->tests;
            $nbFailures += (int)$testSuiteTagAttributes->failures;
            $nbPassed += $testPassed;
        }

        \uasort($aNewmanDetailed, function ($a, $b) {
            return ($b['score'] <=> $a['score']);
        });

        $view->set('newmanData_total_summary_requests_#', $nbRequests);
        $view->set('newmanData_total_summary_tests_#', $nbTests);
        $view->set('newmanData_total_summary_passed_#', $nbPassed);
        $view->set('newmanData_total_summary_failures_#', $nbFailures);
        $view->set('newmanData_total_time', $time);
        $view->set('newmanData_details', $aNewmanDetailed);
        $view->set('newmanData_isSuccess', $nbTests === $nbPassed);

        $view->set('newmanData_total_summary_passed_%', 100 * \round($nbPassed / \max(1, $nbTests), 4));
        $view->set('newmanData_total_summary_failures_%', 100 * \round($nbFailures / \max(1, $nbTests), 4));

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
            if ('testcase' !== $testCaseTag->getName()) {
                continue;
            }

            $testCaseTagAttributes = $testCaseTag->attributes();
            $failureTag = $testCaseTag->children()[0];

            $testCaseDetails[(string)$testCaseTagAttributes->name] = [
                'success' => null === $failureTag,
                'message' => (string)$failureTag,
            ];
        }

        return $testCaseDetails;
    }

    /**
     * @inheritDoc
     */
    public function getHTMLMenu(): string
    {
        return View::getInstance()->import('blocks/nav/newman.phtml');
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

        $nbTests = $view->get('newmanData_total_summary_tests_#', 0);
        $nbPassed = $view->get('newmanData_total_summary_passed_#', 0);

        if (0 === $nbTests) {
            return null;
        }

        return \max(0, 100 * $nbPassed / $nbTests);
    }
}
