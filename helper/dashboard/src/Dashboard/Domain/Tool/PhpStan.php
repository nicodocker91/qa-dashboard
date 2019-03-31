<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Tool;

use Dashboard\Domain\Generalisation\ToolDashboardInterface;
use Dashboard\Infrastructure\TraitSummary;
use Dashboard\Infrastructure\Parameters;
use Dashboard\Infrastructure\View;

/**
 * Class PhpStan
 *
 * This class manages data for the PhpStan Tool logs.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class PhpStan implements ToolDashboardInterface
{
    use TraitSummary;

    /** @var string Name of the folder where all logs of this tool are stored. */
    public const LOG_FOLDER_NAME = 'phpstan';

    /** @var string Human readable name of the tool. */
    public const TOOL_NAME = 'PHPStan';

    /** @var float Coefficient taken to calculate the global ranking. */
    public const SUMMARY_COEFFICIENT = 6;

    /**
     * PhpStan constructor.
     */
    public function __construct()
    {
        $view = View::getInstance();
        $view->set('_phpstan', $this);

        $folder = Parameters::get('path-log') . '/' . static::LOG_FOLDER_NAME;
        $reports = glob($folder . '/*.json');
        if (empty($reports)) {
            return;
        }

        $view->set('phpstanData', true);
        sort($reports, SORT_NATURAL);

        // Use json report to get total information.
        $dataJson = \array_map(static function (string $fileName): array {
            return \json_decode(\file_get_contents($fileName), true);
        }, $reports);

        $nbErrors = \end($dataJson)['totals']['file_errors'];
        $nbErrorsByLevels = [];
        $percentErrorsByLevels = [];
        $errorsByLevel = [];
        foreach ($dataJson as $level => $dataLevel) {
            $nbErrorsByLevels[$level] =
                $dataLevel['totals']['file_errors'] - ($dataJson[$level-1]['totals']['file_errors'] ?? 0);
            $percentErrorsByLevels[$level] = (
                0 === $nbErrors ? 0 : number_format(100 * $nbErrorsByLevels[$level] / $nbErrors, 2)
            );
            foreach ($dataLevel['files'] as $fileName => $fileError) {
                $relFileName = preg_replace('#^/app/#', '', $fileName);
                foreach ($fileError['messages'] as $message) {
                    $hashError = sha1($fileName . '_' . $message['message'] . ':' . $message['line']);
                    if (isset($errorsByLevel[$hashError])) {
                        continue;
                    }
                    $errorsByLevel[$hashError] = (object)[
                        'level' => number_format($level),
                        'file' => $relFileName,
                        'line' => number_format($message['line']),
                        'message' => $message['message']
                    ];
                }
            }
        }

        $errorsByFile = [];
        foreach ($errorsByLevel as $error) {
            $errorsByFile[$error->file] = $errorsByFile[$error->file] ?? [];
            $errorsByFile[$error->file][] = $error;
        }
        uasort($errorsByFile, static function ($a, $b): int {
            return \count($a) <=> \count($b);
        });

        $view->set('phpstan_nbErrors_#', number_format($nbErrors));
        $view->set('phpstan_nbErrors_byLevel_#', \array_map('\\number_format', $nbErrorsByLevels));
        $view->set('phpstan_nbErrors_byLevel_%', $percentErrorsByLevels);
        $view->set('phpstan_errors_byLevel', \array_values($errorsByLevel));
        $view->set('phpstan_errors_byFile', $errorsByFile);

        $view->set('phpstanData_hasErrors', 0 !== $nbErrors);

        $nbLevels = \count($dataJson);
        $summaryColors = [];
        for ($i=0;$i<$nbLevels; ++$i) {
            if ($i <= $nbLevels/2) {
                $r = round(217 + $i * 46 / $nbLevels);
                $g = round(83 + $i * 180 / $nbLevels);
                $b = 79;
            } else {
                $r = round(240 - round($i-$nbLevels/2) * 296 / $nbLevels);
                $g = round(173 + round($i-$nbLevels/2) * 22 / $nbLevels);
                $b = round(79 + round($i-$nbLevels/2) * 26 / $nbLevels);
            }
            $summaryColors[$i] = 'rgb(' . $r . ',' . $g . ',' . $b . ')';
        }
        $view->set('phpstan_colors_summary', $summaryColors);
    }

    /**
     * @inheritDoc
     */
    public function getHTMLMenu(): string
    {
        return View::getInstance()->import('blocks/nav/phpstan.phtml');
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
        if (!$view->get('phpstanData', false)) {
            return null;
        }

        //Function is 100 * (1 - sum[lvl=0->lvl=lvl_max]( nbErrors / (10 * lvl^2) ). Minimum 0 and Maximum 100.
        $nbErrorsByLevels = $view->get('phpstan_nbErrors_byLevel_#');
        $sum = 0;
        foreach ($nbErrorsByLevels as $level => $nbErrors) {
            $sum += $nbErrors / (0 === $level ? 1 : (10 * $level ** 2));
        }

        return min(100, max(0, 100 * (1 - $sum)));
    }
}
