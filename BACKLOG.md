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
* "Buildins" after i know what the name means at least it means error suppressor.
* What happens if a relevant statement spans several lines? is that even possible
  with the relations i defined so far?

# Cleanup
* Remove outdate _and, _except, ... on Variable. Looks also like Rules could need
  a general cleanup. What is DependOn::invoke? What is a better name for 
  Invoke::invokes?
