* The hierarchy of Artifact was broken, as soon as i defined SourceCodeLineArtifact.
  It does neither have a name nor dependencies or invocations. Or has it? Maybe i
  already made the mistake when introducing an Artifact as violator in Violation.
  But that could yield usefull information for the output of violations...
* Remove outdate _and, _except, ... on Variable. Looks also like Rules could need
  a general cleanup. What is DependOn::invoke? What is a better name for 
  Invoke::invokes?

* Make Result be an interface. Theres no need to calculate all results up front
  then.
    -> Calculating results up front makes it possible to traverse the codebase
       only once.
* An Analyzer then is just a factory for results.
    -> This seems to be correct regarding PDepend, as the implementation of
       Analyzer for PDepend does initialisation of PDepend and then spits out
       a result retreived in a way that seems to be against the grain of
       PDepend somehow.

= Structure of PDepend =

* **Engine**:
    - is the runner for the analysis process
    - orchestrates parsing and analysis process
    - depends on an engine config, a cache factory and an analyzer factory
    - `analyze` returns the AST of the project
    - one could add report generators

* **Report Generator**
    - creates a report of code metrics in a specific format
    - 'requests' analyzers, logs their results and creates the final result

* **Analyzer**
    - measures some metrics of the code by visting the AST
