<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Verification\Implementation;

use \Lechimp\Dicto\Verification as Verification;
use \Lechimp\Dicto\Definition as Definition;

class Selector implements Verification\Selector {
    /**
     * @inheritdocs
     */
    public function matches(Definition\_Variable $def, Verification\Artifact $artifact) {
        $cls = get_class($def);
        switch ($cls) {
            case "Lechimp\\Dicto\\Definition\\_Class":
                return $artifact instanceof Verification\ClassArtifact;
            case "Lechimp\\Dicto\\Definition\\_Function":
                return $artifact instanceof Verification\FunctionArtifact;
            case "Lechimp\\Dicto\\Definition\\_Global":
                return $artifact instanceof Verification\GlobalArtifact;
            case "Lechimp\\Dicto\\Definition\\_Buildin":
                return $artifact instanceof Verification\BuildinArtifact;
            case "Lechimp\\Dicto\\Definition\\_File":
                return $artifact instanceof Verification\FileArtifact;
            case "Lechimp\\Dicto\\Definition\\_WithName":
                return $this->matches($def->variable(), $artifact)
                   and $this->matches_regexp($def->regexp(), $artifact->name());
            default:
                throw new \Exception("Unknown variable type $cls");
        }
    }

    /**
     * Does the string match the regexp?
     *
     * @param   string  $regexp
     * @param   string  $subject
     * @return  bool
     */
    protected function matches_regexp($regexp, $subject) {
        assert('is_string($regexp)');
        assert('is_string($subject)');
        return preg_match("%$regexp%", $subject) === 1;
    }
}
