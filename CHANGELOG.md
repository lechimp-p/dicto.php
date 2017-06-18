# 0.7.0

* Unified `Exit` and `Die` to `ExitOrDie`. I can not see a good reason why
  one would want to target one of these but not the other.
* Introduced `Everything` variable.
* Changed inner workings of parser for rule definition.
* Changed format of rules, introduced an extra ':' after the schema.
  'Foo cannot invoke Bar' became 'Foo cannot invoke: Bar'.
* Added filename filter to diff-per-rule report. (thx @d3r1w)
* diff-per-rule report now also contains file and line info about resolved violations. (thx @d3r1w)

