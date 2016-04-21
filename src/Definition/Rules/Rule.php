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
use Lechimp\Dicto\Definition as Def;
use Lechimp\Dicto\Definition\Variables as Vars;

abstract class Rule extends Def\Definition {
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

    public function __construct($mode, Def\Variables\Variable $subject) {
        assert('in_array($mode, self::$modes)');
        $this->mode = $mode;
        $this->subject = $subject;
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
            return new Vars\ButNot
                        ( "ONLY_CAN_INVERSION"
                        , new Vars\Everything("EVERYTHING")
                        , $this->subject()
                        );
        }
        return $this->subject();
    }
}

