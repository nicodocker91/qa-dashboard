<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Services\Pdepend;

/**
 * Class Average
 *
 * This class manages averages calculations in Pdepend tool.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class Average
{
    /** @var float */
    protected $cyclomaticComplexity = 0;

    /** @var float */
    protected $nPath = 0;

    /** @var float */
    protected $halsteadBug = 0;

    /** @var float */
    protected $maintainabilityIndex = 0;

    /**
     * Adds values to each fields to be able to manage the average on them.
     *
     * @param float $cc The Cyclomatic Complexity value.
     * @param float $nPath The NPath value.
     * @param float $hb The Halstead Bugs probability value.
     * @param float $mi The Maintainability Index value.
     * @return Average
     */
    public function add(float $cc, float $nPath, float $hb, float $mi): Average
    {
        $this->cyclomaticComplexity += $cc;
        $this->nPath += $nPath;
        $this->halsteadBug += $hb;
        $this->maintainabilityIndex += $mi;

        return $this;
    }

    /**
     * Get all averages calculated about the number of elements.
     *
     * @param int $nbElements
     * @return array
     */
    public function getAverage(int $nbElements): array
    {
        if ($nbElements <= 0) {
            return [];
        }

        return [
            'ccn2' => $this->cyclomaticComplexity / $nbElements,
            'nPath' => $this->nPath / $nbElements,
            'hb' => $this->halsteadBug / $nbElements,
            'mi' => $this->maintainabilityIndex / $nbElements,
        ];
    }

    /**
     * Adds to the current object the averages already calculated (mostly from a deeper level).
     *
     * @param array $averages
     * @return Average
     */
    public function addFromAverages(array $averages): Average
    {
        if (empty($averages)) {
            return $this;
        }

        return $this->add($averages['ccn2'], $averages['nPath'], $averages['hb'], $averages['mi']);
    }
}
