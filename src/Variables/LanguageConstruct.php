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

use Lechimp\Regexp\Regexp;
use Lechimp\Dicto\Graph\PredicateFactory;

class LanguageConstruct extends Variable
{
    /**
     * @var string
     */
    private $construct_name;

    public function __construct($construct_name, $name = null)
    {
        parent::__construct($name);
        assert('is_string($construct_name)');
        $this->construct_name = $construct_name;
    }

    /**
     * @return  string
     */
    public function construct_name()
    {
        return $this->construct_name;
    }

    /**
     * @inheritdoc
     */
    public function meaning()
    {
        return $this->construct_name();
    }

    /**
     * @inheritdocs
     */
    public function compile(PredicateFactory $f)
    {
        return $f->_and([$f->_type_is(Variable::LANGUAGE_CONSTRUCT_TYPE)
            , $f->_property("name")->_equals($this->construct_name())
            ]);
    }
}
