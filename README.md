[![Build Status](https://travis-ci.org/lechimp-p/dicto.php.svg?branch=master)](https://travis-ci.org/lechimp-p/dicto.php)
[![Scrutinizer](https://scrutinizer-ci.com/g/lechimp-p/dicto.php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lechimp-p/dicto.php)
[![Coverage](https://scrutinizer-ci.com/g/lechimp-p/dicto.php/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lechimp-p/dicto.php)

# dicto checks architectural rules.

**This is an implementation of [dicto](http://scg.unibe.ch/dicto) in and for PHP.**

## Try it out

It's not finished, but

* Clone this project and checkout the master branch.
* Clone [ILIAS Code](https://github.com/ILIAS-eLearning/ILIAS) as target project
  for analysis.
* Open example/ilias.config.yaml and adjust the project.root variable to the
  location of your ILIAS-repo.
* Use hhvm to get the best speed and: `hhvm dicto.php examples/ilias.config.yaml`
* What dicto crunch the ILIAS files, perform analysis and output the results of
  the analysis.
* See how rules are defined in examples/ilias.rules. The set of available rules
  and variables is not completed and things might not work as expected, though.

