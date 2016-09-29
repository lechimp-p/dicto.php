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
    private $type;

    /**
     * @var array<string,mixed>
     */
    private $properties = [];

    /**
     * @param   string              $type
     * @param   array<string,mixed> $properties
     */
    public function __construct($type, array $properties) {
        assert('is_string($type)');
        $this->type = $type;
        foreach ($properties as $key => $value) {
            $this->set_property($key, $value);
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
    private function set_property($key, $value) {
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

    /**
     * Get one property.
     *
     * @param   string  $name
     * @throws  \InvalidArgumentException   if named propery does not exist
     * @return  mixed
     */
    public function property($name) {
        if (!$this->has_property($name)) {
            $type = $this->type;
            throw new \InvalidArgumentException(
                "Unknown property '$name' for entity with type '$type'");
        }
        return $this->properties[$name];
    }

    /**
     * Check if entity has a property.
     *
     * @param   string  $name
     * @return  bool
     */
    public function has_property($name) {
        return array_key_exists($name, $this->properties);
    }
}
