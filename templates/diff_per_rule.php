<?php

function template_diff_per_rule(array $report) {
?>

====================================================================================
 DICTO - automated architectural tests     (https://github.com/lechimp-p/dicto.php)
====================================================================================
 report with emphasis on the diff

 commit      : <?= $report["current"]["commit_hash"] ?> 
 compared to : <?= $report["previous"]["commit_hash"] ?> 

 violations
    total    : <?= $report["violations"]["total"] ?> 
    added    : <?= $report["violations"]["added"] ?> 
    resolved : <?= $report["violations"]["resolved"] ?> 

<?php foreach ($report["rules"] as $rule) { ?>
------------------------------------------------------------------------------------
 (!) <?= wordwrap($rule["rule"], 80, "\n     ", true) ?> 
------------------------------------------------------------------------------------
 <?= wordwrap(str_replace("\n", " ", $rule["explanation"]), 80, "\n ", false); ?> 

 violations
    total    : <?= $rule["violations"]["total"] ?> 
    added    : <?= $rule["violations"]["added"] ?> 
    resolved : <?= $rule["violations"]["resolved"] ?> 

<?php   foreach ($rule["violations"]["list"] as $v) { ?>
    <?= $v["file"] ?> (l. <?= $v["line_no"] ?>) <?= (isset($v["resolved_in"]) ? "Resolved" : "") . "\n" ?>
<?php   } ?>

<?php }
}
