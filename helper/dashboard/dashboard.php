<?php
declare(strict_types = 1);

require_once 'www/autoload.php';

use Dashboard\Domain\Entity\Dashboard;
use Dashboard\Domain\Services\Summary;
use Dashboard\Infrastructure\Parameters;

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . DIRECTORY_SEPARATOR);

// Process to prepare all variables in the view.
Parameters::parseArguments($argv);

/** @noinspection PhpUnhandledExceptionInspection */
(new Dashboard(new Summary()))->detectBuildTime()->parseTools()->calculateScore()->export()->checkAcceptanceValue();
