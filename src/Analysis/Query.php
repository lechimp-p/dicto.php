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
    //
    // Get the names of the different tables used by database.
    /**
     * @return string
     */
//    public function source_file_table();

    /**
     * @return string
     */
//    public function entity_table();

    /**
     * @return string
     */
//    public function reference_table();

    /**
     * @return string
     */
//    public function relations_table();

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
    // TODO: This seems to be a bridge to the new database
    // schema, as the location is currently located in a separate
    // table, but could be moved to the relations table. This
    // should be possible when the inserter interface gets a new
    // shape.
    public function reference_table();

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
    // TODO: implement this
    //public function name_table();

    /**
     * Get a builder to create queries.
     *
     * @return  QueryBuilder
     */
    public function builder();
}
