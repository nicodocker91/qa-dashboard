<?php
declare(strict_types = 1);

namespace Dashboard\Infrastructure;

/**
 * Class Parameters
 *
 * This class manages arguments in a data field.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class Parameters
{
    /** @var array The data field. */
    protected static $data = [];

    /**
     * Parses arguments given to set them into the data field.
     * @param array $arguments
     * @return void
     */
    public static function parseArguments(array $arguments): void
    {
        \parse_str(\implode('&', \array_slice($arguments, 1)), static::$data);
    }

    /**
     * Retrieves an arguments with its name.
     * @param string $name
     * @param null $defaultValue
     * @return mixed|null
     */
    public static function get(string $name, $defaultValue = null)
    {
        return static::$data[$name] ?? $defaultValue;
    }
}
