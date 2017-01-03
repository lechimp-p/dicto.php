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
    protected function template() {
        if (isset($this->config["template"])) {
            return $this->config["template"];
        }
        return $this->default_template();
    }

    /**
     * Get the path for a template name.
     *
     * Just returns, if an absolute path is given. Searches dicto-templates
     * directory and current working directory in that order, if path is not fully
     * qualified.
     *
     * @param   string  $name
     * @return  string
     */
    protected function template_path($name) {
        return $name;
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
        $template_path = $this->template_path($this->template());
        $template = $this->load_template($template_path);
        $report = $this->generate();
        $printed_report = $template($report);
        fputs($handle, $printed_report);
    }
}
