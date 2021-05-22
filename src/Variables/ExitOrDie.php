<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Variables;

use Lechimp\Dicto\Variables\Any;
use Lechimp\Dicto\Variables\LanguageConstructs;

class ExitOrDie extends Any
{
    public function __construct()
    {
        parent::__construct(
            [ new LanguageConstruct("die", "die")
            , new LanguageConstruct("exit", "exit")
            ],
            "ExitOrDie"
        );
    }
}
