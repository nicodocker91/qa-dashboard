<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Entity;

/**
 * Class SummaryElement
 *
 * This class manages a summary element that is caracterized by its name, value and coefficient.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class SummaryElement
{
    /**
     * @var string Unique identifier of the element summary.
     */
    protected $id;

    /**
     * @var string Name of the element we have a summary.
     */
    protected $name;

    /**
     * @var null|float Percentage value accorded to this element summary. If NULL, this summary must be ignored.
     */
    protected $value;

    /**
     * @var float Coefficient that helps to calculate a more precise global ranking.
     */
    protected $coefficient;

    /**
     * SummaryElement constructor.
     *
     * @param string $id
     * @param string $name
     * @param null|float $value
     * @param float $coefficient Default value is 1.
     */
    public function __construct(string $id, string $name, ?float $value, float $coefficient = 1)
    {
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
        $this->coefficient = $coefficient;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return null|float
     */
    public function getValue(): ?float
    {
        return $this->value;
    }

    /**
     * @return float
     */
    public function getCoefficient(): float
    {
        return $this->coefficient;
    }

    /**
     * Returns the self object in array form.
     * @return array
     */
    public function toArray(): array
    {
        return \get_object_vars($this);
    }
}
