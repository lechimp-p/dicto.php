<?php

namespace Lechimp\Dicto\Report;

/**
 * Base class for reports.
 */
abstract class Report {
    /**
     * @var array<string,mixed>
     */
    protected $config;

    /**
     * @var Queries 
     */
    protected $queries;

    public function __construct(Queries $queries, array $config) {
        $this->queries = $queries;
        $this->config = $config;
    }

    /**
     * Get the path to the default template file for the report.
     *
     * @return string
     */
    abstract protected function default_template_path();

    /**
     * Get the path to the template file.
     *
     * Use config["template_path"], fall back to default.
     *
     * @return string
     */
    protected function template_path() {
        if (isset($this->config["template_path"])) {
            return $this->config["template_path"];
        }
        return $this->default_template_path();
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
    protected function load_template($template_path) {
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
    protected function template_function_name($path) {
        $matches = [];
        if (!preg_match("%(.*/)?([^./]+)[.]php%i", $path, $matches)) {
            throw new \RuntimeException("Path '$path' seems not to point to a template.");
        }
        return "template_".$matches[2];
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
    public function write($handle) {
        assert('is_resource($handle)');
        $template_path = $this->template_path();
        $template = $this->load_template($template_path);
        $report = $this->generate();
        $printed_report = $template($report);
        fputs($handle, $printed_report);
    }
}
