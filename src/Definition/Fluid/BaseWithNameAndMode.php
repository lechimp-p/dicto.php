<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Definition\Fluid;
use Lechimp\Dicto\Definition as Def;

/**
 * Base class for all fluid interfaces.
 */
class BaseWithNameAndMode extends BaseWithName {
    /**
     * @var string  one of Rule::$modes
     */
    protected $mode;

    public function __construct(Def\RuleDefinitionRT $rt, $name, $mode) {
        parent::__construct($rt, $name);
        assert('in_array($mode, \\Lechimp\\Dicto\\Definition\\Rule::$modes)');
        $this->mode = $mode;
    }
}
