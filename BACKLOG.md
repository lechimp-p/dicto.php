* The hierarchy of Artifact was broken, as soon as i defined SourceCodeLineArtifact.
  It does neither have a name nor dependencies or invocations. Or has it? Maybe i
  already made the mistake when introducing an Artifact as violator in Violation.
  But that could yield usefull information for the output of violations...
* Drop constraint that but_not and as_well_as only work for variables of the same
  type.
