# Indexer
* Besides entities, dependencies and invokations i felt the need to introduce a
  reference to model locations in the code where i know a name and a possible
  type of an entity, but not its identity, source or location of definition. Is
  this a good idea? Could we dereference some or all references?
* How should i keep track of namespaces? Is it just a part of the name? Could i
  store it separately?
* It may be a good idea to use tuple (start,end) for all places where we record
  a location in a file.

# Things to write tests for
* What happens if i use namespaces?
* What happens if i use closures and call them like `$closure()`?
* What happens if a relevant statement spans several lines? is that even possible
  with the relations i defined so far?
* What happens if i use a 'multi global' statement: global $a1, $a2;

# Definition
* Expose `everything` on fluid interface.
* Make `language_construct` more pleasant to use.
* Define known types of `language_construct`s.

# DB
* Use another schema for the database. There could be a file table, where each
  file is stored in separate lines. That would make the database smaller and
  also ContainText easier.
* Make rules (and maybe vars) able to initialize their own tables.

# Cleanup
* Reorder tests to match the different rules better.
* Make Engine (and propably Indexer and Analyzer) depend on a logger. Make them
  log.
* Vars could be cleaned up; Classes, Methods, etc. are only required for pattern
  matching in Query::compile_var.
