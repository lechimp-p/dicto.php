<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Graph\Node;

/**
 * A variable with a certain property.
 */
class WithProperty extends Variable {
    /**
     * @var Variable
     */
    private $other;

    /**
     * @var Property
     */
    private $property;

    /**
     * @var array
     */
    private $arguments;

    public function __construct(Variable $other, Property $property, array $arguments) {
        parent::__construct();
        assert('$property->arguments_are_valid($arguments)');
        $this->other = $other;
        $this->property = $property;
        $this->arguments = $arguments;
    }

    /**
     * @return  Variable
     */
    public function variable() {
        return $this->other;
    }

    /**
     * @inheritdocs
     */
    public function meaning() {
        // TODO: maybe i should use the name here?
        return $this->variable()->meaning()." with ".$this->property->name().": ".$this->argument_list();
    }

    protected function argument_list() {
        $args = array();
        foreach ($this->arguments as $argument) {
            if (is_string($argument)) {
                $args[] = "\"$argument\"";
            }
            else {
                throw new \LogicException("Unknown arg type: ".gettype($argument));
            }
        }
        return implode(", ", $args);
    }

    /**
     * @inheritdocs
     */
    public function compile() {
        $left_condition = $this->other->compile();
        $property_condition = $this->property->compile($this->arguments);

        return function(Node $n) use ($left_condition, $property_condition) {
            return $left_condition($n)
                && $property_condition($n);
        };
    }
}
