<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Services;

use Dashboard\Domain\Entity\SummaryElement;

/**
 * Class Summary
 *
 * This class manages orders of summary calculation for all tools built.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class Summary
{
    /**
     * @var SummaryElement[] List of summary values. Used to manage a global project summary.
     */
    protected $summaryList = [];

    /**
     * @var float Global note value of the whole current analytics.
     */
    protected $globalNote = 0;

    /**
     * Returns the global note score calculated.
     *
     * @return float
     */
    public function getGlobalNote(): float
    {
        return $this->globalNote;
    }

    /**
     * Add a summary value to the list to be able to calculate a global project value.
     *
     * @param SummaryElement $summaryElement Object that contains all properties of the summary.
     * @return Summary
     */
    public function addSummary(SummaryElement $summaryElement): Summary
    {
        if (null !== $summaryElement->getValue()) {
            $this->summaryList[$summaryElement->getId()] = $summaryElement;
        }
        return $this;
    }

    /**
     * Calculates the global project note using all summaries in list.
     *
     * @return Summary
     */
    public function calculateGlobal(): Summary
    {
        if (empty($this->summaryList)) {
            return $this;
        }

        $finalCoefficient = 0;
        foreach ($this->summaryList as $summaryElement) {
            $this->globalNote += $summaryElement->getValue() * $summaryElement->getCoefficient();
            $finalCoefficient += $summaryElement->getCoefficient();
        }

        $this->globalNote /= $finalCoefficient;
        return $this;
    }

    /**
     * Exports all summaries in a JSON formatted file.
     *
     * @return string The JSON encoded exported.
     */
    public function export(): string
    {
        $summaryList = [];
        foreach ($this->summaryList as $id => $summaryElement) {
            $summaryList[$id] = $summaryElement->toArray();
        }
        return \json_encode(['global' => $this->globalNote, 'tools' => $summaryList]);
    }
}
