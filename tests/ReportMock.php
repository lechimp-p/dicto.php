<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

use Lechimp\Dicto\Report\Report;

class ReportMock extends Report {
    public $data = [];

    public function __construct($queries = null, array $config = []) {
        $this->config = $config;
        if (isset($config["data"])) {
            $this->data = $config["data"];
        }
    }

    protected function default_template_path() {
        return __DIR__."/../templates/json.php";
    }

    public function generate() {
        return $this->data;
    }

    public function _template_function_name($path) {
        return $this->template_function_name($path);
    }
}


