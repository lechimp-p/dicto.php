* Besides entities, dependencies and invokations i felt the need to introduce a
  reference to model locations in the code where i know a name and a possible
  type of an entity, but not its identity, source or location of definition. Is
  this a good idea? Could we dereference some or all references?
* How should i keep track of namespaces? Is it just a part of the name? Could i
  store it separately?

# Cleanup
* Remove outdate _and, _except, ... on Variable. Looks also like Rules could need
  a general cleanup. What is DependOn::invoke? What is a better name for 
  Invoke::invokes?
