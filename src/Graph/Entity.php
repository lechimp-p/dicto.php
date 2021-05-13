<?php
/******************************************************************************
 * An implementation of dicto (scg.unibe.ch/dicto) in and for PHP.
 * 
 * Copyright (c) 2016 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under GPLv3. You should have received 
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
     * @var array<string,mixed>|null
     */
    private $properties = [];

    /**
     * @param   string                      $type
     * @param   array<string,mixed>|null    $properties
     */
    public function __construct($type, array $properties = null) {
        assert('is_string($type)');
        $this->type = $type;
        if ($properties !== null) {
            foreach ($properties as $key => $value) {
                $this->set_property($key, $value);
            }
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
     * Set a property.
     *
     * @param   string  $key
     * @param   mixed   $value
     * @return  null
     */
    private function set_property($key, $value) {
        assert('is_string($key)');
        $this->properties[$key] = $value;
    }

    /**
     * Get the properties.
     *
     * @return  array<string,mixed>
     */
    public function properties() {
        if ($this->properties === null) {
            return [];
        }
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
