<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the license along with the code.
 */

namespace Lechimp\Dicto\Graph;

/**
 * An entity in the graph has a type and stores key value pairs as properties.
 * It will be concretized to a node or a relation.
 */
abstract class Entity {
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array<string,mixed>
     */
    protected $properties = [];

    /**
     * @param   string              $type
     * @param   array<string,mixed> $properties
     */
    public function __construct($type, $properties) {
        assert('is_string($type)');
        $this->type = $type;
        foreach ($properties as $key => $value) {
            $this->setProperty($key, $value);
        }
    }

    /**
     * Get the type.
     *
     * @return  string
     */
    public function type() {
        return $this->type;
    }

    /**
     * Set a new property.
     *
     * @param   string  $key
     * @param   mixed   $value
     * @throws  \InvalidArgumentException   if property is already set
     * @return  null
     */
    protected function setProperty($key, $value) {
        assert('is_string($key)');
        if (array_key_exists($key, $this->properties)) {
            throw new \InvalidArgumentException("Property $key already set.");
        }
        $this->properties[$key] = $value;
    }

    /**
     * Get the properties.
     *
     * @return  array<string,mixed>
     */
    public function properties() {
        return $this->properties;
    }
}
