<?php

namespace Lechimp\Dicto\Report;

/**
 * Generates reports from configs.
 */
class Generator {
    /**
     * @var Queries
     */
    protected $queries;

    public function __construct(Queries $queries) {
        $this->queries = $queries;
    }

    /**
     * Generate a report according to config.
     *
     * @param   Config
     * @return  null
     */
    public function generate(Config $config) {
        $this->maybe_load_source($config->source_path());
        $report = $this->build_report($config->class_name(), $config->config());
        $handle = $this->open_handle($config->target());
        $report->write($handle);
        fclose($handle);
    }

    /**
     * Load source for report if required.
     *
     * @param   string|null $path
     * @return  null
     */
    protected function maybe_load_source($path) {
        if ($path === null) {
            return;
        }
        if (!file_exists($path)) {
            throw new \RuntimeException("Could not load non-existing file '$path'.");
        }
        require_once($path);
    }

    /**
     * Build a report instance.
     *
     * @param   string  $class_name
     * @param   array   $config
     * @throws  \RuntimeException if no corresponding class was found.
     * @throws  \RuntimeException if given class does not inherit from namespace.
     * @return  Report
     */
    protected function build_report($class_name, array $config) {
        $fq_name = $this->fully_qualified_class_name($class_name);
        if (!class_exists($fq_name)) {
            throw new \RuntimeException("Class '$class_name' does not a exist.");
        }
        $report = new $fq_name($this->queries, $config);
        if (!is_subclass_of($report, Report::class)) {
            throw new \RuntimeException("'$class_name' is not a Report-class.");
        }
        return $report;
    }

    /**
     * Open a handle to write the report to.
     *
     * @param   string  $handle_name
     * @return  resource
     */
    protected function open_handle($handle_name) {
        $handle = fopen($handle_name, "w");
        if (!$handle) {
            throw new \RuntimeException("Could not open handle '$handle_name'.");
        }
        return $handle;
    }

    /**
     * Derive fully qualified class name from the provided class name.
     *
     * If the class name starts with an "\" it is considered already fully
     * qualified.
     * If not, we check if $nameReport is found in \Lechimp\Dicto\Report
     * namespace or if $name is found in said namespace.
     * If that is also not the case, we assume the class is in the global
     * namespace.
     *
     * @return  string
     */
    protected function fully_qualified_class_name($name) {
        if (substr($name, 0, 1) == "\\") {
            return $name;
        }
        $fq = "\\Lechimp\\Dicto\\Report\\{$name}Report";
        if (class_exists($fq)) {
            return $fq;
        }
        $fq = "\\Lechimp\\Dicto\\Report\\$name";
        if (class_exists($fq)) {
            return $fq;
        }
        return "\\$name";
    }
}
