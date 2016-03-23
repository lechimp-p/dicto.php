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

use Lechimp\Dicto\Dicto as Dicto;
use Lechimp\Dicto\App as App;
use Lechimp\Dicto\Definition\Rules as Rules;

class  RuleLoader implements App\RuleLoader {
    /**
     * @inheritdocs
     */
    public function load_rules_from($rule_file_path) {
        if (!file_exists($rule_file_path)) {
            throw new \InvalidArgumentException("$rule_file_path does not exist.");
        }
        // TODO: Some more checking on the file...
        Dicto::startDefinition();
        require_once($rule_file_path);
        return Dicto::endDefinition();
    }
}
