<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\App;

use Lechimp\Dicto\Rule\Ruleset;
use Lechimp\Dicto\Definition\RuleBuilder;
use Lechimp\Dicto\Definition\ParserException;

class RuleLoader {
    /**
     * @var RuleBuilder
     */
    protected $parser;

    public function __construct(RuleBuilder $parser) {
        $this->parser = $parser;
    }

    /**
     * Load rules from file at given path.
     *
     * @param   string  $rule_file_path
     * @throws  ParserException if file can't be parsed.
     * @return  Ruleset
     */
    public function load_rules_from($rule_file_path) {
        if (!file_exists($rule_file_path)) {
            throw new \InvalidArgumentException("$rule_file_path does not exist.");
        }
        $content = file_get_contents($rule_file_path);
        return $this->parser->parse($content);
    }
}
