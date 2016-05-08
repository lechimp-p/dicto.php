<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Dicto as Dicto;

class RuleFromFSLoader implements RuleLoader {
    /**
     * @inheritdocs
     */
    public function load_rules_from($rule_file_path) {
        if (!file_exists($rule_file_path)) {
            throw new \InvalidArgumentException("$rule_file_path does not exist.");
        }
        // TODO: Some more checking on the file...
        Dicto::startDefinition();
        require($rule_file_path);
        return Dicto::endDefinition();
    }
}
