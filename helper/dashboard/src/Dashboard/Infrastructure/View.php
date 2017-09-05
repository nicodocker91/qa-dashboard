<?php
declare(strict_types = 1);

namespace Dashboard\Infrastructure;

/**
 * Class View
 *
 * This class manages view variables in a data field.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class View
{
    /** @var string Base path of all views the dashboard must use. */
    protected const VIEW_BASE_PATH = 'www/views/';

    /** @var null|View Singleton instance of the view. */
    protected static $instance;

    /** @var array The data field. */
    protected $data = [];

    /**
     * Retrieves the singleton instance of the view.
     * @return View
     */
    public static function getInstance(): View
    {
        if (null === static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Protect the constructor method.
     */
    protected function __construct()
    {
        // Do nothing except declaring the constructor protected.
    }

    /**
     * Protect the clone method.
     */
    protected function __clone()
    {
        // Do nothing except declaring the clone method protected.
    }

    /**
     * Sets a value in the data view.
     * @param string $name Name of the data.
     * @param mixed $value Value of the data.
     * @return View
     */
    public function set(string $name, $value): View
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Consider the current data view as an array and add an element to this data.
     * @param string $name
     * @param $value
     * @return View
     */
    public function add(string $name, $value): View
    {
        $this->data[$name] = (array)$this->data[$name];
        $this->data[$name][] = $value;
        return $this;
    }

    /**
     * Retrieves a data view with its name.
     * @param string $name
     * @param null $defaultValue
     * @return mixed|null
     */
    public function get(string $name, $defaultValue = null)
    {
        return $this->data[$name] ?? $defaultValue;
    }

    /**
     * Imports a view-part in another file. Content of the view-part is returned.
     * @param string $viewName Name of the view-part to import.
     * @return string
     */
    public function import(string $viewName): string
    {
        $viewPath = static::VIEW_BASE_PATH . $viewName;

        \ob_start();
        require $viewPath;
        return \ob_get_clean();
    }
}
