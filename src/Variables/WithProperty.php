<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Regexp;
use Lechimp\Dicto\Graph\Node;
use Lechimp\Dicto\Graph\PredicateFactory;

/**
 * A variable with a certain property.
 */
class WithProperty extends Variable
{
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

    public function __construct(Variable $other, Property $property, array $arguments)
    {
        parent::__construct();
        assert('$property->arguments_are_valid($arguments)');
        $this->other = $other;
        $this->property = $property;
        $this->arguments = $arguments;
    }

    /**
     * @return  Variable
     */
    public function variable()
    {
        return $this->other;
    }

    /**
     * @inheritdocs
     */
    public function meaning()
    {
        return $this->variable()->meaning() . " " . $this->property->parse_as() . ": " . $this->argument_list();
    }

    protected function argument_list()
    {
        $args = array();
        foreach ($this->arguments as $argument) {
            if (is_string($argument)) {
                $args[] = "\"$argument\"";
            } elseif ($argument instanceof Regexp) {
                $args[] = "\"" . $argument->raw() . "\"";
            } elseif ($argument instanceof Variable) {
                $name = $argument->name();
                if ($name !== null) {
                    $args[] = $name;
                } else {
                    $args[] = "(" . $argument->meaning() . ")";
                }
            } else {
                throw new \LogicException("Unknown arg type: " . gettype($argument));
            }
        }
        return implode(", ", $args);
    }

    /**
     * @inheritdocs
     */
    public function compile(PredicateFactory $f)
    {
        $l = $this->other->compile($f);
        $p = $this->property->compile($f, $this->arguments);

        return $f->_and([$l,$p]);
    }
}
