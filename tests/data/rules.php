<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the licence along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;

Dicto::configuration(array
    ( "project" => array
        ( "root" => __DIR__."/src"
        )
    , "analysis" => array
        ( "ignore" => array
            ( ".*\\.omit_me"
            )
        )
    )
);

// adjust RuleLoaderTest::VARIABLES_IN_RULES_PHP and tests
// if you change these.
Dicto::AClasses()->means()->classes()->with()->name("A.*");
Dicto::BClasses()->means()->classes()->with()->name("B.*");
Dicto::ABClasses()->means()->AClasses()->as_well_as()->BClasses();
Dicto::AFunctions()->means()->functions()->with()->name("a_.*");
Dicto::BFunctions()->means()->functions()->with()->name("b_.*");
Dicto::ANotBFunctions()->means()->AFunctions()->but_not()->BFunctions();
Dicto::Suppressor()->means()->language_construct("@");
Dicto::FooFiles()->means()->files()->with()->name("foo");

Dicto::AClasses()->must()->invoke()->AFunctions();
