<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Indexer;

use Lechimp\Dicto\Variables\Variable;
use PhpParser\Node as N;

/**
 * Interface to implement Rules that visit the AST.
 *
 * ATTENTION: Do not expect the jump labels to be called in any specific order,
 * i.e. be aware that using internal state in your implementation might wreak
 * havoc when used in conjunction with the jump labels.
 */
interface ASTVisitor {
    /**
     * Return (class => method) mapping for Nodes the Visitor is interested in
     * and the corresponding handlers.
     *
     * @return  array<string, string>
     */
    public function visitorJumpLabels();
}
