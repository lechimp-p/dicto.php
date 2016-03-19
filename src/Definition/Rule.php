<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Definition;

abstract class Rule extends Definition {
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

    public function __construct($mode, Variables\Variable $subject) {
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
     * @return Variable
     */
    public function subject() {
        return $this->subject;
    }
}

