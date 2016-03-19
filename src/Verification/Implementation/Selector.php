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

use \Lechimp\Dicto\Verification as Ver;
use \Lechimp\Dicto\Definition as Def;

class Selector implements Ver\Selector {
    /**
     * @inheritdocs
     */
    public function matches(Def\Variables\Variable $def, Ver\Artifact $artifact) {
        $cls = get_class($def);
        switch ($cls) {
            case "Lechimp\\Dicto\\Definition\\Variables\\Classes":
                return $artifact instanceof Ver\ClassArtifact;
            case "Lechimp\\Dicto\\Definition\\Variables\\Functions":
                return $artifact instanceof Ver\FunctionArtifact;
            case "Lechimp\\Dicto\\Definition\\Variables\\Globals":
                return $artifact instanceof Ver\GlobalArtifact;
            case "Lechimp\\Dicto\\Definition\\Variables\\Buildins":
                return $artifact instanceof Ver\BuildinArtifact;
            case "Lechimp\\Dicto\\Definition\\Variables\\Files":
                return $artifact instanceof Ver\FileArtifact;
            case "Lechimp\\Dicto\\Definition\\Variables\\WithName":
                return $this->matches($def->variable(), $artifact)
                   and $this->matches_regexp($def->regexp(), $artifact->name());
            case "Lechimp\\Dicto\\Definition\\Variables\\AsWellAs":
                return $this->matches($def->left(), $artifact)
                    or $this->matches($def->right(), $artifact);
            case "Lechimp\\Dicto\\Definition\\Variables\\ButNot":
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
