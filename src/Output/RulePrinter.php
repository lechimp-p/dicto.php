<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Output;
use Lechimp\Dicto\Definition as Def;

/**
 * Prints rules.
 */
class RulePrinter {
    /**
     * @param   Def\Rules\Rule  $rule
     * @return  string
     */ 
    public function pprint(Def\Rules\Rule $rule) {
        return $this->print_head($rule).$this->print_tail($rule);
    }

    protected function print_head(Def\Rules\Rule $rule) {
        $name = $rule->subject()->name();
        switch ($rule->mode()) {
            case Def\Rules\Rule::MODE_CANNOT:
                return "$name cannot ";
            case Def\Rules\Rule::MODE_MUST:
                return "$name must ";
            case Def\Rules\Rule::MODE_ONLY_CAN:
                return "only $name can ";
            default:
                throw new \Exception("Unknown rule mode '".$rule->mode()."'");
        } 
    }

    protected function print_tail(Def\Rules\Rule $rule) {
        $cls = get_class($rule);

        switch ($cls) {
            case "Lechimp\\Dicto\\Definition\\Rules\\Invoke":
                return "invoke ".$rule->invokes()->name();
            case "Lechimp\\Dicto\\Definition\\Rules\\DependOn":
                return "depend on ".$rule->dependency()->name();
            case "Lechimp\\Dicto\\Definition\\Rules\\ContainText":
                return "contain text \"".$rule->regexp()."\"";
            default:
                throw new \Exception("Unknown rule '".$cls."'");
        }
    }
}
