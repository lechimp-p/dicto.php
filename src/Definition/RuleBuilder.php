<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 * 
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Definition;

use Lechimp\Dicto\Rules\Ruleset;
use Lechimp\Dicto\Variables as V;
use Lechimp\Dicto\Rules as R;

/**
 * Builds Rulesets from strings.
 */
class RuleBuilder {
    /**
     * @var ASTParser
     */
    protected $parser;

    /**
     * @var Compiler
     */
    protected $compiler;

    /**
     * @param   V\Variable[]    $predefined_variables
     * @param   R\Schema[]      $schemas
     * @param   V\Property[]    $properties
     */
    public function __construct( array $predefined_variables
                               , array $schemas
                               , array $properties) {
        $this->parser = $this->build_parser();
        $this->compiler = $this->build_compiler($predefined_variables, $schemas, $properties);
    }

    /**
     * @return AST\Factory
     */
    protected function build_factory() {
        return new AST\Factory();
    }

    /**
     * @return ASTParser
     */
    protected function build_parser() {
        return new ASTParser($this->build_factory());
    }

    /**
     * @param   V\Variable[]    $predefined_variables
     * @param   R\Schema[]      $schemas
     * @param   V\Property[]    $properties
     * @return  Compiler
     */
    protected function build_compiler( array $predefined_variables
                                     , array $schemas
                                     , array $properties) {
        return new Compiler($predefined_variables, $schemas, $properties);
    }

    /**
     * @param   string
     * @return  Ruleset
     */
    public function parse($source) {
        assert('is_string($source)');
        $ast = $this->parser->parse($source);
        return $this->compiler->compile($ast);
    }
}
