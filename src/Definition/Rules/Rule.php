<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Definition\Rules;
use Lechimp\Dicto\Rules as R;
use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Definition\Variables as Vars;

class Rule extends Def\Definition {
    const MODE_CANNOT   = "CANNOT";
    const MODE_MUST     = "MUST";
    const MODE_ONLY_CAN = "ONLY_CAN";

    static $modes = array
        ( Rule::MODE_CANNOT
        , Rule::MODE_MUST
        , Rule::MODE_ONLY_CAN
        );

    /**
     * @var string
     */
    private $mode;

    /**
     * @var Vars\Variable
     */
    private $subject;

    /**
     * @var R\Schema
     */
    private $schema;

    /**
     * @var array
     */
    private $arguments;

    public function __construct($mode, Vars\Variable $subject, R\Schema $schema, array $arguments) {
        assert('in_array($mode, self::$modes)');
        $schema->check_arguments($arguments);
        $this->mode = $mode;
        $this->subject = $subject;
        $this->schema = $schema;
        $this->arguments = $arguments;
    }

    public function explain($explanation) {
        $r = new Rule($this->mode, $this->subject, $this->schema, $this->arguments);
        $r->setExplanation($r);
        return $r;
    }

    /**
     * @return string
     */
    public function mode() {
        return $this->mode;
    }

    /**
     * Definition of the entities this rule was defined for.
     *
     * @return  Vars\Variable
     */
    public function subject() {
        return $this->subject;
    }

    /**
     * Definition of the entities this rule needs to be checked on.
     *
     * In the default case the rule needs to be checked on every entity that
     * is not subject() if the mode is MODE_ONLY_CAN, as this really says
     * something about the other entities.
     *
     * @return  Vars\Variable
     */
    public function checked_on() {
        if ($this->mode() == self::MODE_ONLY_CAN) {
            return new Vars\ButNot
                        ( "ONLY_CAN_INVERSION"
                        , new Vars\Everything("EVERYTHING")
                        , $this->subject()
                        );
        }
        return $this->subject();
    }

    /**
     * Get all variables referenced by the rule.
     *
     * @return  Vars\Variable[]
     */
    public function variables() {
        $vars = array($this->subject());
        foreach ($this->arguments as $argument) {
            if ($argument instanceof Vars\Variable) {
                $vars[] = $argument;
            }
        }
        return $vars;
    }

    /**
     * Get the schema that was used for the rule.
     *
     * // TODO: This could be implemented here...
     *
     * @return R\Schema
     */
    public function schema() {
        return $this->schema;
    }

    /**
     * Pretty print the rule.
     *
     * @return string
     */
    public function pprint() {
        $name = $this->subject()->name();
        switch ($this->mode()) {
            case Def\Rules\Rule::MODE_CANNOT:
                return "$name cannot ".$this->schema()->pprint($this);
            case Def\Rules\Rule::MODE_MUST:
                return "$name must ".$this->schema()->pprint($this);
            case Def\Rules\Rule::MODE_ONLY_CAN:
                return "only $name can ".$this->schema()->pprint($this);
            default:
                throw new \Exception("Unknown rule mode '".$this->mode()."'");
        }
    }

    /**
     * Get the argument at the index.
     *
     * @throws  \OutOfRangeException
     * @param   int     $index
     * @return  mixed 
     */
    public function argument($index) {
        if ($index < 0 || $index >= count($this->arguments)) {
            throw new \OutOfRangeException("'$index' out of range.");
        }
        return $this->arguments[$index];
    }
}

