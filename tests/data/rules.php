<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Dicto\Dicto as Dicto;

Dicto::AClasses()->means()->classes()->with()->name("A.*");
Dicto::BClasses()->means()->classes()->with()->name("B.*");
Dicto::ABClasses()->means()->AClasses()->as_well_as()->BClasses();
Dicto::AFunctions()->means()->functions()->with()->name("a_*");
Dicto::BFunctions()->means()->functions()->with()->name("b_*");
Dicto::Suppressor()->means()->buildins()->with()->name("@");
Dicto::FooFiles()->means()->files()->with()->name("foo");

Dicto::AClasses()->must()->depend_on()->AFunctions();
