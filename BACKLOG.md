# Things to write tests for
* What happens if i use closures and call them like `$closure()`?
* What happens if a relevant statement spans several lines? is that even possible
  with the relations i defined so far?

# Definition
* Make syntax errors in rules being outputted more nicely.

# DB
* Maybe make rules (and maybe vars) able to initialize their own tables.
* Maybe move DBs to their own namespace.

# Cleanup
* Every submodule could have its own Config, those could then be combined to the
  big global config.
* DBFactory is a misnomer. Its more like a manager, which is a very non descriptive
  word.
* Use custom rules in indexer test.
* Engine starts to become a mess, maybe it could be refactored to use some different
  classes.
* Introduce folders in tests
* Rename Property::arguments to Property::parameters. Seems to be a better fit.
* Rename "definition" in AST to "variable".
* RuleBuilder could go away.
* bootstrap-template for diff-per-rule report needs cleanup (factoring out some functions
  in JS, unify logic for colors and messages between php and JS)

# ASTParser
* Seems to be a bit clunky, since I derived it from RuleBuilder. Maybe rewrite.
* Record source code locations to improve error output in Compiler.
* Some tests are missing, e.g. for {..} in a argument list.

# Report
* Introduce some nice way to use different and also custom report generators. That
  would mean to introduce the possibility to add custom classes to the config. 
* Maybe exchange the current name for reports by something like tags.

# Issues
* Write some paragraph in the README about other similar yet different projects.
* It is inconsistent, that Invoke makes invocations in methods of classes related
  to the class itself, but `only XYZClasses can invoke` also flags the methods in
  XYZClasses.
* The language for rules currently is more powerful than the analysis. I can say
  something like `Classes cannot relate to Methods in OtherClasses` but i would
  not be able to analyse that rule correctly, as method_references are not related
  to a class.
* It seems as everything that "invokes" should also "depend on".
* The ResultDB currently won't know if a run completed.
* Introduce a "run" command, that first analyzes and then reports.

# Execution plan for introducing git (and further improvements)
* The engine then somehow needs to figure out what to do based on the last run and
  the current state of the source.
    -> This would also mean that we could also only reindex files that have changed
       between two commits.
* Time measurement in dicto.php seems to be a bit out of line. It better be moved
  to the commands.
