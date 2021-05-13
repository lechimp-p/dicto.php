<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Report\Report;
use Lechimp\Dicto\Report\Config;

class ReportConfigMock extends Config {
    public function __construct(array $config) {
        parent::__construct(__DIR__, "", "", $config);
    }
}

class ReportMock extends Report {
    public $data = [];

    public function __construct($queries = null, $config = []) {
        if (is_array($config)) {
            $config = new ReportConfigMock($config);
        }
        $this->config = $config;
        if (isset($config->config()["data"])) {
            $this->data = $config->config()["data"];
        }
    }

    protected function default_template() {
        return __DIR__."/../templates/json.php";
    }

    public function generate() {
        return $this->data;
    }

    public function _template_function_name($path) {
        return $this->template_function_name($path);
    }

    public function _template_path($name) {
        return $this->template_path($name);
    }
}


