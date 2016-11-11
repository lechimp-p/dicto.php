[![Build Status](https://travis-ci.org/lechimp-p/dicto.php.svg?branch=master)](https://travis-ci.org/lechimp-p/dicto.php)
[![Scrutinizer](https://scrutinizer-ci.com/g/lechimp-p/dicto.php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lechimp-p/dicto.php)
[![Coverage](https://scrutinizer-ci.com/g/lechimp-p/dicto.php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lechimp-p/dicto.php)

# dicto checks architectural rules.

**This is an implementation of [dicto](http://scg.unibe.ch/dicto) in and for PHP.**

## Motivation

I'm a member of the [ILIAS](http://www.ilias.de) Open Source Community. ILIAS is a
LMS (Learning Managment System) which recently had it's 18th birthday. In an effort
to refactor and streamline this old code base, some other ILIAS devs and me introduced
[dicto](http://scg.unibe.ch/dicto).

dicto is a tool that allows developers to express rules in a natural-like language
and then finds violation of these rules on a code base. It is written in Smalltalk
and utilizes off-the-shelf tools for the analysis work.

When working with the tool in the community it became appearant to me that a real
addoption of the tool is hindered by the fact, that it is written in Smalltalk.
Nobody really knows, how one could run the tool on any machine. When a new type
of rule would be required, no one could implement it in Smalltalk without some
effort. In general, the tool is kind of intangible.

This is the attempt to reimplement the great original idea (thx. A.C. and O.T.!)
in PHP. General goals where, to make the tool accessible for PHP programmers and
make it executable without problems in a standard PHP environment.

## Try it out

It's not completely finished, but

* Clone this project and checkout the master branch.
* Clone [ILIAS Code](https://github.com/ILIAS-eLearning/ILIAS) as target project
  for analysis.
* Open example/ilias.config.yaml and adjust the project.root variable to the
  location of your ILIAS-repo.
* Use hhvm (>3.15) to get the best speed and: `hhvm dicto.php examples/ilias.config.yaml`
* Watch dicto crunch the ILIAS files, perform analysis and output the results of
  the analysis.
* See how rules are defined in examples/ilias.rules. The set of available rules
  and variables is not completed and things might not work as expected, though.

## How To

Create a rules file and a config.yml. Run `hhvm dicto.php $PATH_TO_CONFIG` where
$PATH_TO_CONFIG is the location of your config.yml.

### Writing down rules

The rules file has two basic type of entities, variables and rules. A variable
describes entities in your codebase, while rules but constraints on variables.

#### Variables

A variable is defined by using the form

**```MyNewVariable = $SOME_ENTITIES```**

where `$SOME_ENTITIES` is one of the following forms (with nested `$ENTITIES`s):

* **`File`**: Every class in your codebase.
* **`Namespace`**: Every namespace in your codebase.
* **`Class`**: Every class in your codebase.
* **`Interface`**: Every interface in your codebase.
* **`Trait`**: Every trait in your codebase.
* **`Method`**: Every method in your codebase.
* **`Function`**: Every function in your codebase.
* **`Global`**: Every function in your codebase.
* **`Exit`**: The build in exit function.
* **`Die`**: The build in die function.
* **`ErrorSuppressor`**: The build in exit function.
* **`Eval`**: The build in eval function.
* **`$ENTITIES with name: "$REGEXP"`**: Any of the given `$ENTITIES` where the name
  matches the given `$REGEXP`. The `$REGEXP` is according to `preg_match` but without
  regexp delimiters.
* **`$ENTITIES in: $OTHER_ENTITIES`**: Any of the given `$ENTITIES` that is somehow
  contained in `$OTHER_ENTITIES`.
* **`{$ENTITIES, $OTHER_ENTITIES}`**: All entities that are either `$ENTITIES` or
  `$OTHER_ENTITIES`.
* **`$ENTITIES except $OTHER_ENTITIES`**: All `$ENTITIES` that are not at the same
  time `$OTHER_ENTITIES`.

#### Rules

A rule is a statement over some variable. There are three modes a rule can be
expressed in:

* **`$ENTITIES cannot $STATEMENT`**
* **`$ENTITIES must $STATEMENT`**
* **`only $ENTITIES can $STATEMENT`**

where $ENTITIES is some previously defined variable or another entity definition
as used when defining variables.

Currently there are three different statements that could be used to express
rules on on the codebase:

* **`$ENTITIES cannot depend on $OTHER_ENTITIES`**: If the defined $ENTITIES either
  call the `$OTHER_ENTITIES` or read or write to it (if it is a global) that is
  a violation.
* **`$ENTITIES must invoke $OTHER_ENTITIES`**: If the $ENTITIES do not call the
  `$OTHER_ENTITIES`, that is a violation.
* **`only $ENTITIES can contain text "$REGEXP"`**: If any other than `$ENTITIES`
  contain a text that matches the given `$REGEXP` (according to `preg_match`,
  no regexp delimiters), that is a violation.


### Config

TBD

## Shortcommings and Outlook

Currently this tool is able to detect violations according to the rules the
ILIAS community has defined. This means that it might not be able to analyse
rules according to your needs. There are currently a lot of things that are
missing:

* dicto.php does not know anything about inheritance, interface implementation
  or usage of traits.
* It does not recognize any parameters to methods and does not know anything
  about variables in methods other than globals.
* The system of statements that can be made is very weak. What does one mean
  exactly, when saying "depend on"? Inheriting? Calling? Reading??
* DocStrings are not considered.

There are also some general features missing:

* The difference between rules and properties is somehow arbitrary, why can't
  is say `cannot have name` or `depending on`. In general, every rule basically
  is a statement over the existence of entities with some properties.
* There are minimal attempts to use information from git, but information of
  git could be used a lot better to spare time reindexing unchanged files.

From an implementation perspective I consider the codebase solid, with with
space for improvements:

* The performance is a lot better after i did many optimizations, but there
  are still improvements to be made. When the system can process more information,
  it will take longer time to run. The analysis is performed on an in memory
  representation of the dependency graph. By moving analysis to SQL one could
  use a multi client database to have parallel processing and faster analysis.

## Contributions

TBD
