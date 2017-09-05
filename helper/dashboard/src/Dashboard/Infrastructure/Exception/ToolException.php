<?php
declare(strict_types = 1);

namespace Dashboard\Infrastructure\Exception;

use Exception;

/**
 * Class ToolException
 *
 * Manages all exceptions related to the Tool management that can be thrown.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class ToolException extends Exception
{
    /**
     * @var int Default base code to use in this class.
     * Each static method dedicated to returning an instance of self must add an integer between 1 and 999 to identify
     * the Exception thrown.
     */
    protected const DEFAULT_CODE = 1000;

    /**
     * Returns an Exception explaining that the asked tool is unknown of the available list.
     * @param string $tool Name of the tool that is unknown.
     * @param int|null $code Code of the Exception when thrown. Default defined by class constant and method order.
     * @param Exception|null $previous Previous Exception if given. None by default.
     * @return ToolException
     */
    public static function unknownToolName(string $tool, int $code = null, Exception $previous = null): ToolException
    {
        $code = $code ?? (static::DEFAULT_CODE + 1);
        return new static(\sprintf('Unknown tool name "%s" asked by the Dashboard.', $tool), $code, $previous);
    }
}
