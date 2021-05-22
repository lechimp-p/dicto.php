<?php

function template_total_per_rule(array $report)
{
    ?>

====================================================================================
 DICTO - automated architectural tests     (https://github.com/lechimp-p/dicto.php)
====================================================================================
 report over one analysis run 

 commit      : <?= $report["current"]["commit_hash"] ?> 

 violations  : <?= $report["violations"]["total"] ?> 

<?php foreach ($report["rules"] as $rule) { ?>
------------------------------------------------------------------------------------
 (!) <?= wordwrap($rule["rule"], 80, "\n     ", true) ?> 
------------------------------------------------------------------------------------
 <?= wordwrap(str_replace("\n", " ", $rule["explanation"]), 80, "\n ", false); ?> 

 violations  : <?= $rule["violations"]["total"] ?> 

<?php   foreach ($rule["violations"]["list"] as $v) { ?>
    <?= $v["file"] ?> (l. <?= $v["line_no"] ?>)
<?php   } ?>

<?php }
}
