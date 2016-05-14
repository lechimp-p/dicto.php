<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the licence along with the code.
 */

namespace Lechimp\Dicto\Indexer;

use Lechimp\Dicto\Analysis\Consts;
use PhpParser\Node as N;

/**
 * Detects invocations.
 */
class InvocationsListener extends Listener {
    public function on_enter_misc(array $entities, \PhpParser\Node $node) {
        $ref_id = null;
        if ($node instanceof N\Expr\MethodCall) {
            // The 'name' could also be a variable like in $this->$method();
            if (is_string($node->name)) {
                $ref_id = $this->insert->get_reference
                    ( Consts::METHOD_ENTITY
                    , $node->name
                    , $this->file_path
                    , $node->getAttribute("startLine")
                    );
            }
        }
        elseif($node instanceof N\Expr\FuncCall) {
            // Omit calls to closures, we would not be able to
            // analyze them anyway atm.
            // Omit functions in arrays, we would not be able to
            // analyze them anyway atm.
            if (!($node->name instanceof N\Expr\Variable ||
                  $node->name instanceof N\Expr\ArrayDimFetch)) {
                $ref_id = $this->insert->get_reference
                    ( Consts::FUNCTION_ENTITY
                    , $node->name->parts[0]
                    , $this->file_path
                    , $node->getAttribute("startLine")
                    );
            }
        }
        if ($ref_id !== null) {
            // We need to record a invocation in every invoking entity now.
            $start_line = $node->getAttribute("startLine");
            $source_line = $this->lines_from_to($start_line, $start_line);

            foreach ($entities as $entity) {
                if ($entity[0] == Consts::FILE_ENTITY) {
                    continue;
                }
                $this->insert->relation
                    ( "invoke"
                    , $entity[1]
                    , $ref_id
                    , $this->file_path
                    , $start_line
                    , $source_line
                    );
            }
        }
    }
}
