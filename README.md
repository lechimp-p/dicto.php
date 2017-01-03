[![Build Status](https://travis-ci.org/lechimp-p/dicto.php.svg?branch=master)](https://travis-ci.org/lechimp-p/dicto.php)
[![Scrutinizer](https://scrutinizer-ci.com/g/lechimp-p/dicto.php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lechimp-p/dicto.php)
[![Coverage](https://scrutinizer-ci.com/g/lechimp-p/dicto.php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lechimp-p/dicto.php)

# dicto checks architectural rules.

**This is an implementation of [dicto](http://scg.unibe.ch/dicto) in and for PHP.**

## Motivation

I'm a member of the [ILIAS](http://www.ilias.de) Open Source Community. ILIAS is
a LMS (Learning Managment System) which recently had its 18th birthday. In an
effort to refactor and streamline its old code base, some other ILIAS devs and
me have introduced [dicto](http://scg.unibe.ch/dicto) to the ILIAS development process.

dicto is a tool that allows developers to express architectural rules in a
natural-like language and then finds violations of these rules within a code base.
It's written in Smalltalk and utilizes off-the-shelf tools for the analysis work.

While working with dicto inside the community it became apparent to me that broad
adoption is hindered by the fact that it is written in Smalltalk and few people
want to run Smalltalk on their machines. Furthermore, even less people know how
to modify the code for common tasks like adding new rules. Currently, dicto is
kind of intangible.

This is an attempt to re-implement the great original idea (thanks A.C. and O.T.!)
in PHP. General goals were to make the tool accessible for PHP programmers and
make it easy to execute in a standard PHP environment.

## Try it out

It's not completely finished, but

* Clone this project and checkout the master branch.
* Clone [ILIAS](https://github.com/ILIAS-eLearning/ILIAS) as an example
  analysis target.
* Open `example/ilias.config.yaml` and adjust the project.root variable.
  to the location of your freshly checked out ILIAS repository.
* Make sure hhvm >=3.15 is installed.
* Execute `hhvm dicto.php analyze examples/ilias.config.yaml`.
* Watch dicto.php crunching the ILIAS code base and performing analysis.
* Check out the analysis results.
* See how rules are defined in `examples/ilias.rules`. The set of available rules
  and variables is not complete and things might not work as expected.
* If you are lazy, here are some [example analysis results](https://gist.github.com/lechimp-p/1e62ce404adc34491db53b78eb69962b).

## How To

Create a rules file and a config.yml. Run `hhvm dicto.php analyze $PATH_TO_CONFIG` where
$PATH_TO_CONFIG is the location of your config.yml to analyze your codebase.

### Writing down rules

The rules file has two basic type of entities, variables and rules. A variable
describes entities in your codebase, while rules put constraints on variables.

#### Variables

A variable is defined by using the form

* **`MyNewVariable = $SOME_ENTITIES`**

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
* **`ErrorSuppressor`**: The infamous error suppressor.
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

The config is read from one or more yaml files, where fields in later files
overwrite fields from the files before if set. This should make it possible
to define add a file for a given project to it's repo with the possibility
for individual developers to overwrite it locally.

Fields marked with (\*) are mandatory. Directories can be given with a
leading `./` to make them relative to the position of the first given config
file. Regexps are given in a form compatible to `preg_match` but without
delimiters.

The following fields can be used in the config:

* **project.root**(\*): The path to the source code of the project.
* **project.storage**: A directory where temporary files and results of the
  analysis are stored. Defaults to directory where config file is.
* **project.rules**(\*): Path to the rules for the project.
* **analysis.ignore**: A list of regexps, where files are ignored if their path
  matches.
* **analysis.store_index**: A boolean to determine, if the index is stored in
  a database. If set, a new database is created in `project.storage` for every
  commit hash and existing databases are used when analysing the same commit
  again. Defaults to false, i.e. dicto reindexes on every run.
* **analysis.store_results**: A boolean to determine, if the results of the
  analysis should be stored in database for analysis spanning more than one
  run. Defaults to true.
* **analysis.report_stdout**: A boolean telling whether the results of the
  analysis are written to stdout. Defaults to true.
* **analysis.report_database**: A boolean telling whether the results of the
  analysis should be written to a database containing results from differen
  runs. Defaults to true. The database can be used to create Reports ranging
  multiple runs of the analysis.

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
  I say `cannot have name` or `depending on`. In general, every rule basically
  is a statement over the existence of entities with some properties.
* There are minimal attempts to use information from git, but information of
  git could be used a lot better to not reindex unchanged files and save time.
* Error reporting is not very user friendly atm.

From an implementation perspective I consider the codebase solid, with space
for improvements:

* The performance is a lot better after I did many optimizations, but there
  are still improvements to be made. When the system can process more information,
  it will take longer time to run. The analysis is performed on an in memory
  representation of the dependency graph. By moving analysis to SQL one could
  use a multi client database to have parallel processing and faster analysis.
* The whole `App` part could benefit from the restructuring to be better configurable.

## Contributions

At the moment I am interested in general and specific feedback. Feel free to
report issues, although I can't promise to fix them in a timely manner or at all.
As outlined in [Shortcommings and Outlook](#shortcommings-and-outlook) the
current structure will see some changes most probably. Thus I am not ready to
accept PRs in general. If you want to contribute code, please contact me before
putting any effort into coding stuff.

