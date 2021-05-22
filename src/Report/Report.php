<?php

namespace Lechimp\Dicto\Report;

/**
 * Base class for reports.
 */
abstract class Report
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Queries
     */
    protected $queries;

    public function __construct(Queries $queries, Config $config)
    {
        $this->queries = $queries;
        $this->config = $config;
    }

    /**
     * Get the default template for the report.
     *
     * This could be:
     *   - a filename, which is then assumed to be in templates-directory of
     *     dicto or in the current working directory, if not found in dicto
     *     templates
     *   - an absolute path
     *
     * @return string
     */
    abstract protected function default_template();

    /**
     * Get the name of the template.
     *
     * Uses config["template"] or falls back to default template.
     *
     * @return string
     */
    protected function template()
    {
        return $this->custom_config_value("template", $this->default_template());
    }

    /**
     * Get the absolute path for a template.
     *
     * Does only realpath(..) if an absolute path is given. Searches dicto-templates
     * directory, config-directory and current working directory otherwise.
     *
     * @throws  \InvalidArgumentException if none of the strategies leads to an
     *                                    existing file.
     * @param   string  $name
     * @return  string
     */
    protected function template_path($name)
    {
        $path = $name;
        if (substr($name, 0, 1) !== "/") {
            $candidates =
                [ __DIR__ . "/../../templates/{$name}.php"
                , __DIR__ . "/../../templates/{$name}"
                , $this->config->path() . "/{$name}"
                ];
            foreach ($candidates as $candidate) {
                if (file_exists($candidate)) {
                    $path = $candidate;
                    break;
                }
            }
        }
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Can't find path to template '$name'.");
        }
        return $path;
    }

    /**
     * Load a template from a path.
     *
     * Expects the file to contain a function with the same name as the file
     * (minus postfix) prefixed with template_, that writes to stdout.
     *
     * @param   string  $template_path
     * @return  \Closure    array -> null
     */
    protected function load_template($template_path)
    {
        $tpl_fct_name = $this->template_function_name($template_path);
        require_once($template_path);
        return function (array $report) use ($tpl_fct_name) {
            ob_start();
            $tpl_fct_name($report);
            return ob_get_clean();
        };
    }

    /**
     * Derive the name of the template function from a filename.
     *
     * @param   string  $path
     * @return  string
     */
    protected function template_function_name($path)
    {
        $matches = [];
        if (!preg_match("%(.*/)?([^./]+)[.]php%i", $path, $matches)) {
            throw new \RuntimeException("Path '$path' seems not to point to a template.");
        }
        return "template_" . $matches[2];
    }

    /**
     * Generate the report.
     *
     * Should return a structured array containing the information in the report.
     *
     * @return array
     */
    abstract public function generate();

    /**
     * Write the report to some handle using a template.
     *
     * @param   resource    $handle
     * @return  null
     */
    public function write($handle)
    {
        assert('is_resource($handle)');
        $template_path = $this->template_path($this->template());
        $template = $this->load_template($template_path);
        $report = $this->generate();
        $printed_report = $template($report);
        fputs($handle, $printed_report);
    }

    /**
     * Get value from the custom config or default if it does not exist.
     *
     * @param   string      $key
     * @param   mixed       $default
     * @return  mixed
     */
    protected function custom_config_value($key, $default)
    {
        if (isset($this->config->config()[$key])) {
            return $this->config->config()[$key];
        }
        return $default;
    }
}
