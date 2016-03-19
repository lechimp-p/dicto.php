<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Dicto\Verification\Implementation;

use \Lechimp\Dicto\Verification as Verification;
use \Lechimp\Dicto\Definition as Def;

class Verifier implements Verification\Verifier {
    /**
     * @var Verification\Selector
     */
    private $selector;

    public function __construct(Verification\Selector $selector) {
        $this->selector = $selector;
    }

    /**
     * @inheritdocs
     */
    public function has_subject(Def\Rules\Rule $rule, Verification\Artifact $artifact) {
        $mode = $rule->mode(); 
        switch($mode) {
            case Def\Rules\Rule::MODE_CANNOT:
            case Def\Rules\Rule::MODE_MUST:
                return $this->selector->matches($rule->subject(), $artifact);
            case Def\Rules\Rule::MODE_ONLY_CAN:
                return !$this->selector->matches($rule->subject(), $artifact);
            default:
                throw new \Exception("Unknown rule mode '$mode'");
        }
    }

    /**
     * @inheritdocs
     */
    public function violations_in(Def\Rules\Rule $rule, Verification\Artifact $artifact) {
        if (!$this->has_subject($rule, $artifact)) {
            return array();
        }

        $cls = get_class($rule);
        switch ($cls) {
            case "Lechimp\\Dicto\\Definition\\Rules\\DependOn":
                return $this->depend_on_violations($rule, $artifact);
            case "Lechimp\\Dicto\\Definition\\Rules\\Invoke":
                return $this->invoke_violations($rule, $artifact);
            case "Lechimp\\Dicto\\Definition\\Rules\\ContainText":
                return $this->contains_text_violations($rule, $artifact);
            default:
                throw new \Exception("Unknown rule type '$cls'");
        }
    }

    protected function depend_on_violations(Def\Rules\DependOn $rule, Verification\Artifact $artifact) {
        $mode = $rule->mode();
        $var = $rule->dependency();
        switch($mode) {
            case Def\Rules\Rule::MODE_ONLY_CAN:
            case Def\Rules\Rule::MODE_CANNOT:
                $violations = array();
                foreach ($artifact->dependencies() as $dep) {
                    if ($this->selector->matches($var, $dep)) {
                        $violations[] = new Verification\Violation($artifact, $rule, $dep);
                    }
                }
                return $violations;
            case Def\Rules\Rule::MODE_MUST:
                foreach ($artifact->dependencies() as $dep) {
                    if ($this->selector->matches($var, $dep)) {
                        return array();
                    }
                }
                return array(new Verification\Violation($artifact, $rule, $artifact));
            default:
                throw new \Exception("Unknown rule mode '$mode'");
        }
    }

    protected function invoke_violations(Def\Rules\Invoke $rule, Verification\Artifact $artifact) {
        $mode = $rule->mode();
        $var = $rule->invokes();
        switch($mode) {
            case Def\Rules\Rule::MODE_ONLY_CAN:
            case Def\Rules\Rule::MODE_CANNOT:
                $violations = array();
                foreach ($artifact->invocations() as $dep) {
                    assert('$dep instanceof Lechimp\\Dicto\\Verification\\FunctionArtifact');
                    if ($this->selector->matches($var, $dep)) {
                        $violations[] = new Verification\Violation($artifact, $rule, $dep);
                    }
                }
                return $violations;
            case Def\Rules\Rule::MODE_MUST:
                foreach ($artifact->invocations() as $dep) {
                    assert('$dep instanceof Lechimp\\Dicto\\Verification\\FunctionArtifact');
                    if ($this->selector->matches($var, $dep)) {
                        return array();
                    }
                }
                return array(new Verification\Violation($artifact, $rule, $artifact));
            default:
                throw new \Exception("Unknown rule mode '$mode'");
        }
    }

    protected function contains_text_violations(Def\Rules\ContainText $rule, Verification\Artifact $artifact) {
        $mode = $rule->mode();
        $regexp = $rule->regexp();
        switch($mode) {
            case Def\Rules\Rule::MODE_ONLY_CAN:
            case Def\Rules\Rule::MODE_CANNOT:
                $violations = array();
                $source = $artifact->source();
                $lines = explode("\n", $artifact->source());
                $count = 0;
                foreach ($lines as $line) {
                    if (preg_match("%$regexp%", $line)) {
                        $line = new Verification\SourceCodeLineArtifact($artifact->file(), $artifact->start_line() + $count, $line);
                        $violations[] = new Verification\Violation($artifact, $rule, $line);
                    }
                    $count++;
                }
                return $violations;
            case Def\Rules\Rule::MODE_MUST:
                $source = $artifact->source();
                if (preg_match("%$regexp%", $source)) {
                    return array();
                }
                return array(new Verification\Violation($artifact, $rule, $artifact));
            default:
                throw new \Exception("Unknown rule mode '$mode'");
        }
    }
}
