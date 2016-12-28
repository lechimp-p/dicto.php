<?php

/**
 * Base class for reports.
 */
abstract class Report {
    /**
     * @var array<string,mixed>
     */
    protected $config;

    /**
     * @var ResultDB
     */
    protected $result_db;

    public function __construct(ResultDB $result_db, array $config) {
        $this->result_db = $result_db;
        $this->config = $config;
    }

    /**
     * Generate the report.
     *
     * @return string
     */
    abstract public function generate();
}
