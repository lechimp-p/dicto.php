<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Rules;

use Lechimp\Dicto\Definition\Definition;
use Lechimp\Dicto\Variables\Variable;
use Lechimp\Dicto\Variables\Except;
use Lechimp\Dicto\Variables\Everything;
use Lechimp\Dicto\Analysis\Query;
use Doctrine\DBAL\Driver\Statement;

class Rule extends Definition {
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
     * @var Variable
     */
    private $subject;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @param string $mode
     */
    public function __construct($mode, Variable $subject, Schema $schema, array $arguments) {
        assert('in_array($mode, self::$modes)');
        assert('$schema->arguments_are_valid($arguments)');
        $this->mode = $mode;
        $this->subject = $subject;
        $this->schema = $schema;
        $this->arguments = $arguments;
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
     * @return  Variable
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
     * @return  Variable
     */
    public function checked_on() {
        if ($this->mode() == self::MODE_ONLY_CAN) {
            return new Except
                ( new Everything("EVERYTHING")
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
            if ($argument instanceof Variable) {
                $vars[] = $argument;
            }
        }
        return $vars;
    }

    /**
     * Get the schema that was used for the rule.
     *
     * @return Schema
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
            case self::MODE_CANNOT:
                return "$name cannot ".$this->schema()->pprint($this);
            case self::MODE_MUST:
                return "$name must ".$this->schema()->pprint($this);
            case self::MODE_ONLY_CAN:
                return "only $name can ".$this->schema()->pprint($this);
            default:
                throw new \Exception("Unknown rule mode '".$this->mode()."'");
        }
    }

    /**
     * Compile the rule to SQL.
     *
     * @param   Query       $query
     * @return Statement
     */
    public function compile(Query $query) {
        return $this->schema->compile($query, $this);
    }

    /**
     * Turn a query result into a violation.
     *
     * @param   array   $row
     * @return  \Lechimp\Dicto\Analysis\Violation
     */
    public function to_violation(array $row) {
        return $this->schema->to_violation($this, $row);
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

    /**
     * Get all arguments.
     *
     * @return array
     */
    public function arguments() {
        return $this->arguments;
    }
}

