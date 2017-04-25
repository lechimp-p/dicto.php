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
 * Compiles an AST to a ruleset.
 */
class Compiler implements ArgumentParser {
    /**
     * @var V\Variable[]
     */
    protected $predefined_variables;

    /**
     * @var R\Schema[]
     */
    protected $schemas;

    /**
     * @var array<string,R\Property[]>
     */
    protected $properties;

    /**
     * @var array<string,V\Variable[]>
     */
    protected $variables = array();

    /**
     * @var R\Rule[]
     */
    protected $rules = array();

    /**
     * @var string|null
     */
    protected $last_explanation = null;

    /**
     * @var Parameter[]|null
     */
    protected $current_parameters = [];

    /**
     * @param   V\Variable[]    $predefined_variables
     * @param   R\Schema[]      $schemas
     * @param   V\Property[]    $properties
     */
    public function __construct( array $predefined_variables
                               , array $schemas
                               , array $properties) {
        $this->predefined_variables = array_map(function(V\Variable $v) {
            return $v;
        }, $predefined_variables);

        $this->schemas = [];
        foreach ($schemas as $schema) (function(R\Schema $s) {
            $this->schemas[$s->name()] = $s;
        })($schema);

        $this->properties = [];
        foreach ($properties as $property) (function(V\Property $p) {
            $this->properties[$p->parse_as()] = $p;
        })($property);
    }

    /**
     * Compile an AST to an according entity.
     *
     * @param   AST\Node    $node
     * @return  mixed
     */
    public function compile(AST\Root $node) {
        $this->variables = array();
        $this->rules = array();
        $this->current_parameters = [];
        $this->add_predefined_variables();
        return $this->compile_root($node);
    }

    protected function compile_root(AST\Root $node) {
        foreach ($node->lines() as $line) {
            if ($line instanceof AST\Assignment) {
                $this->compile_assignment($line);
                $this->last_explanation = null;
            }
            else if ($line instanceof AST\Rule) {
                $this->compile_rule($line);
                $this->last_explanation = null;
            }
            else if ($line instanceof AST\Explanation) {
                $this->last_explanation = $line;
            }
            else {
                throw new \LogicException("Can't compile '".get_class($line)."'");
            }
        }
        $this->purge_predefined_variables();
        return new Ruleset($this->variables, $this->rules);
    }

    protected function compile_assignment(AST\Assignment $node) {
        $def = $this->compile_definition($node->definition());
        if ($this->last_explanation !== null) {
            $def = $def->withExplanation($this->last_explanation->content());
        }
        $this->add_variable
            ("".$node->name()
            , $def
            );
    }

    protected function compile_rule(AST\Rule $node) {
        $property = $node->definition();
        // TODO: This is morally wrong. In general all rules are existence rules
        // so I should push the schema into the definition of the targeted variables
        // on the interpretation side as well.
        if (!($property instanceof AST\Property)) {
            throw new \LogicException("Expected definition of rule to be AST\Property");
        }
        $schema = $this->get_schema("".$property->id());
        $rule = new R\Rule
            ( $this->compile_qualifier($node->qualifier())
            , $this->compile_definition($property->left())
            , $schema
            , $this->compile_parameters($schema, $property->parameters())
            );
        if ($this->last_explanation !== null) {
            $rule = $rule->withExplanation($this->last_explanation->content());
        }
        $this->rules[] = $rule;
    }

    protected function compile_definition(AST\Definition $node) {
        if ($node instanceof AST\Name) {
            return $this->get_variable("$node");
        }
        if ($node instanceof AST\Any) {
            return $this->compile_any($node);
        }
        if ($node instanceof AST\Except) {
            return $this->compile_except($node);
        }
        if ($node instanceof AST\Property) {
            return $this->compile_property($node);
        }
        throw new \LogicException("Can't compile '".get_class($node)."'");
    }

    protected function compile_any(AST\Any $node) {
        $definitions = $node->definitions();

        // short circuit for any with only one element.
        // TODO: this should become part of some transformation pass of
        // the AST.
        if (count($definitions) == 1) {
            return $this->compile_definition($definitions[0]);
        }

        return new V\Any
            (array_map
                ( [$this, "compile_definition"]
                , $definitions
                )
            );
    }

    protected function compile_except(AST\Except $node) {
        return new V\Except
            ( $this->compile_definition($node->left())
            , $this->compile_definition($node->right())
            );
    }

    protected function compile_property(AST\Property $node) {
        $variable = $this->compile_definition($node->left());
        $property = $this->get_property("".$node->id());
        $parameters = $this->compile_parameters($property, $node->parameters());
        return new V\WithProperty($variable, $property, $parameters);
    }

    protected function compile_parameters($prop_or_schema, array $parameters) {
        assert('$prop_or_schema instanceof Lechimp\Dicto\Variable\Property || $prop_or_schema instanceof Lechimp\Dicto\Rules\Rule');
        // TODO: rename fetch_arguments to fetch_parameters
        $this->current_parameters = $parameters;
        $parameters = $prop_or_schema->fetch_arguments($this);
        if (count($this->current_parameters) > 0) {
            // TODO: add line info here.
            throw new \ParserException("Too much parameters.");
        }
        return $parameters;
    }

    protected function compile_qualifier(AST\Qualifier $node) {
        $which = $node->which();
        if ($which === AST\Qualifier::MUST) {
            return R\Rule::MODE_MUST;
        }
        if ($which == AST\Qualifier::CANNOT) {
            return R\Rule::MODE_CANNOT;
        }
        if ($which == AST\Qualifier::ONLY_X_CAN) {
            return R\Rule::MODE_ONLY_CAN;
        }
        throw new \LogicException("Unknown qualifier '$which'");
    }

    /**
     * Add a variable to the variables currently known.
     *
     * @param   string      $name
     * @param   V\Variable  $def
     * @return null
     */
    protected function add_variable($name, V\Variable $def) {
        assert('is_string($name)');
        if (array_key_exists($name, $this->variables)) {
            throw new ParserException("Variable '$name' already defined.");
        }
        assert('$def instanceof Lechimp\\Dicto\\Variables\\Variable');
        $this->variables[$name] = $def->withName($name);
    }

    /**
     * Get a predefined variable.
     *
     * @param   string  $name
     * @return  V\Variable
     */
    protected function get_variable($name) {
        if (!array_key_exists($name, $this->variables)) {
            throw new ParserException("Unknown variable '$name'.");
        }
        return $this->variables[$name];
    }

    /**
     * Get a property. Yes. Really.
     *
     * @param   string  $name
     * @return  V\Property
     */
    protected function get_property($name) {
        if (!array_key_exists($name, $this->properties)) {
            throw new ParserException("Unknown property '$name'.");
        }
        return $this->properties[$name];
    }

    /**
     * Get a schema. By name.
     *
     * @param   string  $name
     * @return  V\Schema
     */
    protected function get_schema($name) {
        if (!array_key_exists($name, $this->schemas)) {
            throw new ParserException("Unknown schema '$name'.");
        }
        return $this->schemas[$name];
    }

    /**
     * Add all predefined variables to the current set of variables.
     *
     * @return null
     */
    protected function add_predefined_variables() {
        foreach ($this->predefined_variables as $predefined_var) {
            $this->add_variable($predefined_var->name(), $predefined_var);
        }
    }

    /**
     * Purge all predefined variables from the current set of variables.
     *
     * @return null
     */
    protected function purge_predefined_variables() {
        foreach ($this->predefined_variables as $predefined_var) {
            unset($this->variables[$predefined_var->name()]);
        }
    }

    // IMPLEMENTATION OF ArgumentParser

    /**
     * @inheritdoc
     */
    public function fetch_string() {
        $node = $this->next_current_parameter(AST\StringValue::class);
        return "".$node;
    }

    /**
     * @inheritdoc
     */
    public function fetch_variable() {
        $node = $this->next_current_parameter(AST\Definition::class);
        return $this->compile_definition($node);
    }

    protected function next_current_parameter($class) {
        if (count($this->current_parameters) == 0) {
            // TODO: add location info
            throw new \ParserException("Tried to fetch a parameter, but none was left.");
        }
        $arg = array_shift($this->current_parameters);
        if (!($arg instanceof $class)) {
            // TODO: add location info
            throw new \ParserException("Tried to fetch a '%class' parameter but next is '".get_class($arg)."'");
        }
        return $arg;
    }
}
