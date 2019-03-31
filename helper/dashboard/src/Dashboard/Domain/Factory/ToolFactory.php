<?php
declare(strict_types = 1);

namespace Dashboard\Domain\Factory;

use Dashboard\Domain\Generalisation\ToolDashboardInterface;
use Dashboard\Domain\Tool\{
    Behat, Gatling, Newman, Pdepend, PhpCodeSniffer, PhpCpd, PhpMetrics, PhpStan, PhpStorm, Phpunit, Sonar, Uml
};
use Dashboard\Infrastructure\Exception\ToolException;

/**
 * Class ToolFactory
 *
 * This class is a factory that builds instances of Dashboard\Domain\Generalisation\ToolDashboardBuilderInterface.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class ToolFactory
{
    /** @var string[] List of available classes based on the folder name of the tool to build. */
    protected const AVAILABLE_TOOLS = [
        Behat::LOG_FOLDER_NAME => Behat::class,
        Gatling::LOG_FOLDER_NAME => Gatling::class,
        Newman::LOG_FOLDER_NAME => Newman::class,
        Pdepend::LOG_FOLDER_NAME => Pdepend::class,
        PhpCodeSniffer::LOG_FOLDER_NAME => PhpCodeSniffer::class,
        PhpCpd::LOG_FOLDER_NAME => PhpCpd::class,
        PhpMetrics::LOG_FOLDER_NAME => PhpMetrics::class,
        PhpStan::LOG_FOLDER_NAME => PhpStan::class,
        PhpStorm::LOG_FOLDER_NAME => PhpStorm::class,
        Phpunit::LOG_FOLDER_NAME => Phpunit::class,
        Sonar::LOG_FOLDER_NAME => Sonar::class,
        Uml::LOG_FOLDER_NAME => Uml::class,
    ];

    /**
     * Retrieves the singleton instance of the view.
     *
     * @param string $toolName Name of the Tool we must try to instantiate.
     * @return ToolDashboardInterface
     * @throws ToolException
     */
    public static function build(string $toolName): ToolDashboardInterface
    {
        if (\array_key_exists($toolName, static::AVAILABLE_TOOLS)) {
            $toolClass = static::AVAILABLE_TOOLS[$toolName];
            return new $toolClass();
        }
        throw ToolException::unknownToolName($toolName);
    }
}
