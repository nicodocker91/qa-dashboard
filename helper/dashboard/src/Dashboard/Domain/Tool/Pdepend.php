<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Tool;

use Dashboard\Domain\Generalisation\{ToolDashboardBuilderInterface, ToolDashboardSummaryInterface};
use Dashboard\Domain\Services\Pdepend\Average;
use Dashboard\Infrastructure\Parameters;
use Dashboard\Infrastructure\TraitSummary;
use Dashboard\Infrastructure\View;
use SimpleXMLElement;

/**
 * Class Pdepend
 *
 * This class manages data for the Pdepend Tool logs.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class Pdepend implements ToolDashboardBuilderInterface, ToolDashboardSummaryInterface
{
    use TraitSummary;

    /** @var string Name of the folder where all logs of this tool are stored. */
    public const LOG_FOLDER_NAME = 'pdepend';

    /** @var string Human readable name of the tool. */
    public const TOOL_NAME = 'pDepend';

    /** @var float Coefficient taken to calculate the global ranking. */
    public const SUMMARY_COEFFICIENT = 1.5;

    /**
     * PhpCpd constructor.
     */
    public function __construct()
    {
        $view = View::getInstance();
        $view->set('_pdepend', $this);

        $folder = Parameters::get('path-log') . '/' . static::LOG_FOLDER_NAME;

        if (!\is_file($folder . '/summary.xml')) {
            return;
        }

        $view->set('pdependData', true);

        if (\is_file($folder . '/overview-pyramid.svg')) {
            $view->set('pdepend_overviewPyramid_svg', \file_get_contents($folder . '/overview-pyramid.svg'));
        }
        if (\is_file($folder . '/dependencies.svg')) {
            $view->set('pdepend_overviewDependency_svg', \file_get_contents($folder . '/dependencies.svg'));
        }

        $view->set('pdepend_nbMethods', 0);
        $this->parseXmlData(\simplexml_load_string(\trim(\file_get_contents($folder . '/summary.xml'))));
    }

    /**
     * Parses XML data from pdepend generated file.
     *
     * @param SimpleXMLElement $xml
     * @return Pdepend
     */
    protected function parseXmlData(SimpleXMLElement $xml): Pdepend
    {
        if (!isset($xml->package)) {
            return $this;
        }

        $projectLevel = [
            'packages' => [],
            'averages' => [],
            'violations' => 0,
        ];

        $avg = new Average();

        /** @var SimpleXMLElement $package */
        foreach ($xml->package as $package) {
            $noc = (int)$package->attributes()->noc; // Number of classes
            $noi = (int)$package->attributes()->noi; // Number of interfaces

            $packageLevel = ['values' => ['noc' => $noc, 'noi' => $noi]];
            $this->manageClassLevel($packageLevel, $package);
            $avg->addFromAverages($packageLevel['averages']);
            $projectLevel['packages'][(string)$package->attributes()->name] = $packageLevel;
            $projectLevel['violations'] += $packageLevel['violations'];
        }

        $projectLevel['averages'] = $avg->getAverage(\count($xml->package));

        \uasort($projectLevel['packages'], function ($a, $b) {
            return ($a['violations'] > $b['violations'] ? -1 : ($a['violations'] < $b['violations'] ? 1 : 0));
        });

        View::getInstance()->set('pdepend_global_violations_#', $projectLevel['violations']);
        View::getInstance()->set('pdependData_details', $projectLevel);

        return $this;
    }

    /**
     * Manages the data at method level to feed the class level.
     *
     * @param array $packageLevel
     * @param SimpleXMLElement $package
     * @return Pdepend
     */
    protected function manageClassLevel(array &$packageLevel, SimpleXMLElement $package): Pdepend
    {
        $packageLevel['classes'] = [];
        $packageLevel['averages'] = [];
        $packageLevel['violations'] = 0;

        if (!isset($package->class)) {
            return $this;
        }

        $avg = new Average();

        // Calculate for classes.
        /** @var SimpleXMLElement $class */
        foreach ($package->class as $class) {
            $ca = (int)$class->attributes()->ca; // Afferent coupling
            $ce = (int)$class->attributes()->ce; // Efferent coupling
            $nom = (int)$class->attributes()->nom; // Number of methods
            $vars = (int)$class->attributes()->vars; // Number of properties

            View::getInstance()->set('pdepend_nbMethods', $nom + View::getInstance()->get('pdepend_nbMethods'));

            $classLevel = ['type' => 'class', 'values' => ['ca' => $ca, 'ce' => $ce, 'nom' => $nom, 'vars' => $vars]];
            $this->manageMethodLevel($classLevel, $class);
            $avg->addFromAverages($classLevel['averages']);
            $packageLevel['classes'][(string)$class->attributes()->name] = $classLevel;
            $packageLevel['violations'] += $classLevel['violations'];
        }

        // Calculate for traits.
        /** @var SimpleXMLElement $trait */
        foreach ($package->trait as $trait) {
            $ca = (int)$trait->attributes()->ca; // Afferent coupling
            $ce = (int)$trait->attributes()->ce; // Efferent coupling
            $nom = (int)$trait->attributes()->nom; // Number of methods
            $vars = (int)$trait->attributes()->vars; // Number of properties

            View::getInstance()->set('pdepend_nbMethods', $nom + View::getInstance()->get('pdepend_nbMethods'));

            $traitLevel = ['type' => 'trait', 'values' => ['ca' => $ca, 'ce' => $ce, 'nom' => $nom, 'vars' => $vars]];
            $this->manageMethodLevel($traitLevel, $trait);
            $avg->addFromAverages($traitLevel['averages']);
            $packageLevel['classes'][(string)$trait->attributes()->name] = $traitLevel;
            $packageLevel['violations'] += $traitLevel['violations'];
        }

        // Special case for number of traits in package as it is not counted in the attributes of the XML pdepend.
        $packageLevel['values']['not'] = \count($package->trait);

        $packageLevel['averages'] = $avg->getAverage(\count($package->class) + $packageLevel['values']['not']);

        return $this;
    }

    /**
     * Manages the data at method level to feed the class level.
     *
     * @param array $classLevel
     * @param SimpleXMLElement $class
     * @return Pdepend
     */
    protected function manageMethodLevel(array &$classLevel, SimpleXMLElement $class): Pdepend
    {
        $classLevel['methods'] = [];
        $classLevel['averages'] = [];
        $classLevel['violations'] = 0;

        if (!isset($class->method)) {
            return $this;
        }

        $avg = new Average();

        /** @var SimpleXMLElement $method */
        foreach ($class->method as $method) {
            $ccn = (int)$method->attributes()->ccn2; // Cyclomatic complexity
            $nPath = (int)$method->attributes()->npath; // NPath
            $he = (float)$method->attributes()->he; // Halstead Effort
            $hb = (float)$method->attributes()->hb; // Halstead Bugs probability
            $mi = (float)$method->attributes()->mi; // Maintainability Index

            $methodLevel = [
                'values' => ['ccn2' => $ccn, 'nPath' => $nPath, 'he' => $he, 'hb' => $hb, 'mi' => $mi],
                'violations' => [
                    'ccn2' => $ccn > 1, // Cyclomatic complexity must not get over 1.
                    'nPath' => $nPath > 1, // NPath must not get over 1.
                    'he' => false, // Effort has no violation limit has it is relied on bugs (hb).
                    'hb' => $hb > 0.5, // Number of bugs must not exceed half of 1.
                    'mi' => $mi <= 50 // Maintainability percentage must be at least 50.
                ],
            ];
            $avg->add($ccn, $nPath, $hb, $mi);
            $classLevel['methods'][(string)$method->attributes()->name] = $methodLevel;
            $classLevel['violations'] += \count(\array_filter($methodLevel['violations']));
        }

        $classLevel['averages'] = $avg->getAverage(\count($class->method));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHTMLMenu(): string
    {
        return View::getInstance()->import('blocks/nav/pdepend.phtml');
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
        if (!$view->get('pdependData', false) || 0 === $view->get('pdepend_nbMethods', 0)) {
            return null;
        }

        $nbPotentialViolations = 4 * $view->get('pdepend_nbMethods');
        $nbViolations = $view->get('pdepend_global_violations_#');

        return (float)100 * ($nbPotentialViolations - $nbViolations) / $nbPotentialViolations;
    }
}
