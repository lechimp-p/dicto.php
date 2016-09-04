<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 *
 * Copyright (c) 2016, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Analysis;

use Lechimp\Dicto\Variables\Variable;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Query\Expression\CompositeExpression;

/**
 * Interface that defines what a query from the database needs to know.
 */
interface Query {
    // Naming

    /**
     * @return string
     */
    public function name_table();

    /**
     * @return string
     */
    public function file_table();

    /**
     * @return string
     */
    public function source_table();

    /**
     * @return string
     */
    public function relation_table();

    /**
     * @return string
     */
    public function definition_table();

    /**
     * @return string
     */
    public function method_info_table();

    /**
     * Get a builder to create queries.
     *
     * @return  QueryBuilder
     */
    public function builder();
}
