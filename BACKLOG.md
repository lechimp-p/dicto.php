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
* Every submodule could have its own Config, those could then be combined to the
  big global config.
* DBFactory is a misnomer. Its more like a manager, which is a very non descriptive
  word.

# Issues
* When saying "only SomeClasses can depend on SomeThing" we certainly mean, that
  also methods in SomeClasses can depend on SomeThing, but currently the analysis
  does not reflect this.

# Reporting
* Make the app aware of previous runs, most probably best done by using git infos.
  It would then be possible to not reindex the codebase when just a new rule was
  added. It would also be possible to display the commit an issue was introduced
  or to get to know how many new issues were introduced with a commit.

# Execution plan for introducing git (and further improvements)
* There needs to be some notion of a run of the analysis, that needs to be stored
  in the database
* The engine then somehow needs to figure out what to do based on the last run and
  the current state of the source.
* Instead of assuming one db, where all results are stored, there need to be
  different dbs:
    - one containing information about the runs, including info about the violations
    - one db containing indexing results per source state
  Ideally, the latter should be removable without loosing relevant (?) historical (?)
  information.
* ReportGenerator then can go away. Everyone that wants a report can write it
  standalone as a query on the persistent database.
* I could also keep the report generator for some time, to have a mode where only
  the current state of the source is analysed without taking different version
  into account.
    -> This would most probably mean that i would need two different engines.
* If i want the RunInfo to alway have a rule_set field, i would need a way to
  serialize and deserialize rules, which would make a real parser for rules be a
  desirable thing.
* If i also put the config in the RunInfo database, i most probably also should
  put names of rule schemas that were used in the config. I most probably should
  put those in the config anyway.
* If i want to implement the parser, i should separate the config from the rules
  anyway.
* It also could be nice to use a hash of the config between runs, to at least warn
  the user when the config has changed.
* Instead of storing a real ruleset in the runinfo, i could also use a RuleSetInfo
  that doesn't contain real rules. This would mitigate the requirement to build a
  parser.
