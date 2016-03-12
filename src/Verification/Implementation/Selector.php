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
    public function matches(Definition\Variable $def, Verification\Artifact $artifact) {
        $cls = get_class($def);
        switch ($cls) {
            case "Lechimp\\Dicto\\Definition\\ClassVariable":
                return $artifact instanceof Verification\ClassArtifact;
            case "Lechimp\\Dicto\\Definition\\FunctionVariable":
                return $artifact instanceof Verification\FunctionArtifact;
            case "Lechimp\\Dicto\\Definition\\GlobalVariable":
                return $artifact instanceof Verification\GlobalArtifact;
            case "Lechimp\\Dicto\\Definition\\BuildinVariable":
                return $artifact instanceof Verification\BuildinArtifact;
            case "Lechimp\\Dicto\\Definition\\FileVariable":
                return $artifact instanceof Verification\FileArtifact;
            case "Lechimp\\Dicto\\Definition\\WithNameVariable":
                return $this->matches($def->variable(), $artifact)
                   and $this->matches_regexp($def->regexp(), $artifact->name());
            case "Lechimp\\Dicto\\Definition\\AndVariable":
                return $this->matches($def->left(), $artifact)
                    or $this->matches($def->right(), $artifact);
            case "Lechimp\\Dicto\\Definition\\ExceptVariable":
                return $this->matches($def->left(), $artifact)
                   and !$this->matches($def->right(), $artifact);
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
