<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\App\Implementation;

use Lechimp\Dicto\App as App;
use Lechimp\Dicto\Definition\Rules as Rules;

class  RuleLoader implements App\RuleLoader {
    /**
     * @inheritdocs
     */
    public function load_rules_from($rule_file_path) {
    }
}
