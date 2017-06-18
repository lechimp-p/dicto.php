<?php

/**
 * Example report entry:
 * [19180] => Array ...
 * [19181] => Array
 *  (
 *       [file] => webservice/soap/classes/class.ilSoapTestAdministration.php
 *       [line_no] => 140
 *       [introduced_in] => 2
 *       [last_seen_in] => 2
 *       [url] => https://github.com/ILIAS-eLearning/ILIAS/blob/9db77d8a61af49d229bf2444712f18b20941d8d5/webservice/soap/classes/class.ilSoapTestAdministration.php#L140
 *   )
 *
 * @param array $report
 * @return void
 */
function template_diff_per_rule_bootstrap(array $report) {
    $total = $report["violations"]["total"];
    $resolved = $report["violations"]["resolved"];
    $added = $report["violations"]["added"];
    $diff = $resolved - $added;
    if ($resolved == 0 && $added == 0) {
        $msg = 0;
    }
    else if ($diff == 0) {
        $msg = 1;
    }
    else if ($diff > 0 && $added == 0) {
        $msg = 2;
    }
    else if ($diff > 0) {
        $msg = 3;
    }
    else if ($diff < 0 && $resolved != 0) {
        $msg = 4;
    }
    else {
        $msg = 5;
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link 
        rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
        integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
        crossorigin="anonymous">
    <script
        src="https://code.jquery.com/jquery-2.2.4.min.js"
        integrity="sha384-rY/jv8mMhqDabXSo+UCggqKtdmBfd3qC2/KvyTDNQ6PcUJXaxK1tMepoQda4g5vB"
        crossorigin="anonymous">
    </script> 
    <script
        src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous">
    </script>
    <script>

        var debounce_timeout = null;
        var debounce_time = 150; // ms

        function filterRules(searchTerm) {
            var rules = document.querySelectorAll(".rule");

            var totalOverviewViolations = 0;
            var addedOverviewViolations = 0;
            var resolvedOverviewViolations = 0;

            var diff = 0;

            //foreach rule
            for (var i = 0; i < rules.length; i++) {
                var totalTag = rules[i].querySelector(".violation-total");
                var addedTag = rules[i].querySelector(".violation-added");
                var resolvedTag = rules[i].querySelector(".violation-resolved");
                var ruleViolations = rules[i].querySelectorAll(".rule-violations .list-group-item");

                var totalViolations = 0;
                var addedViolations = 0;
                var resolvedViolations = 0;

                //hide not matching violations
                for (var y = 0; y < ruleViolations.length; y++) {
                    var violation = ruleViolations[y];

                    if (violation.innerHTML.toUpperCase().indexOf(searchTerm.toUpperCase()) > -1) {
                        violation.classList.remove("no-display");
                        if(violation.classList.contains('list-group-item-danger')) {
                            addedViolations++;
                        }

                        // resolved violation are no violations at all but should
                        // be rendered therefore don't increment the total if we get a resolved violation.
                        if(violation.classList.contains('list-group-item-success')) {
                            resolvedViolations++;
                        }
                        else {
                            totalViolations++;
                        }
                    }

                    else {
                        violation.classList.remove("no-display");
                        violation.classList.add("no-display");
                    }
                }

                //hide empty rules
                if (totalViolations === 0 && resolvedViolations === 0) {
                    violation.classList.remove("no-display");
                    violation.classList.add("no-display");
                }
                else {
                    violation.classList.remove("no-display");
                }

                totalTag.innerHTML = totalViolations;
                addedTag.innerHTML = addedViolations;
                resolvedTag.innerHTML = resolvedViolations;

                rules[i].classList.remove("panel-default");
                rules[i].classList.remove("panel-danger");
                rules[i].classList.remove("panel-success");
                diff = resolvedViolations - addedViolations;
                if (diff == 0) {
                    rules[i].classList.add("panel-default");
                }
                else if (diff < 0) {
                    rules[i].classList.add("panel-danger");
                }
                else {
                    rules[i].classList.add("panel-success");
                }

                totalOverviewViolations += totalViolations;
                addedOverviewViolations += addedViolations;
                resolvedOverviewViolations += resolvedViolations;
            }


            var totalTag = document.querySelector("#violations-overview-total");
            var addedTag = document.querySelector("#violations-overview-added");
            var resolvedTag = document.querySelector("#violations-overview-resolved");

            totalTag.innerHTML = Number(totalOverviewViolations);
            addedTag.innerHTML = Number(addedOverviewViolations);
            resolvedTag.innerHTML = Number(resolvedOverviewViolations);

            diff = resolvedOverviewViolations - addedOverviewViolations;
            var msg = 0;
            if (resolvedOverviewViolations == 0 && addedOverviewViolations == 0) {
                msg = 0;
            }
            else if (diff == 0) {
                msg = 1;
            }
            else if (diff > 0 && addedOverviewViolations == 0) {
                msg = 2;
            }
            else if (diff > 0) {
                msg = 3;
            }
            else if (diff < 0 && resolvedOverviewViolations != 0) {
                msg = 4;
            }
            else {
                msg = 5;
            }

            var messages = document.querySelector("#violations-overview-message").children;
            for(var i = 0; i < messages.length; i++) {
                messages[i].classList.remove("no-display");
                if (i != msg) {
                    messages[i].classList.add("no-display");
                }
            }
        }

        function onSearchInput(value) {
            clearTimeout(debounce_timeout);
            setTimeout(function() {
                debounce_timeout = null;
                filterRules(value);
            }, debounce_time);
        }
    </script>
    <style type="text/css">
        .no-display { display:none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1> DICTO
                <a href="https://github.com/lechimp-p/dicto.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="24px" height="24px" version="1.1">
                        <g>
                            <path
                                d="m 23.999323,-0.00214894 c -13.243142,0 -23.9831342,11.02089394 -23.9831342,24.61680394 0,10.874311 6.8718866,20.101457 16.4030042,23.35801 1.200039,0.225163 1.637354,-0.534951 1.637354,-1.187773 0,-0.584819 -0.0206,-2.132247 -0.03239,-4.185914 C 11.352518,44.08596 9.9448638,39.298602 9.9448638,39.298602 8.8537843,36.454598 7.2812164,35.697508 7.2812164,35.697508 c -2.1777417,-1.526273 0.1649134,-1.49605 0.1649134,-1.49605 2.4074427,0.173784 3.6737422,2.537239 3.6737422,2.537239 2.139459,3.761279 5.614421,2.674754 6.980848,2.044599 0.217921,-1.58974 0.837818,-2.674753 1.522504,-3.289795 -5.325821,-0.6226 -10.9255175,-2.733691 -10.9255175,-12.166353 0,-2.688355 0.9350006,-4.884072 2.4692845,-6.605282 -0.247371,-0.622599 -1.070464,-3.125081 0.235591,-6.514615 0,0 2.012829,-0.6618879 6.595068,2.522128 1.912702,-0.545529 3.965286,-0.817539 6.004619,-0.828117 2.037859,0.01062 4.088969,0.282588 6.004617,0.828117 4.579295,-3.1840159 6.589179,-2.522128 6.589179,-2.522128 1.309,3.389534 0.485904,5.892016 0.240007,6.514615 1.537229,1.72121 2.464868,3.916927 2.464868,6.605282 0,9.456841 -5.608531,11.53771 -10.950551,12.146708 0.859905,0.760114 1.627048,2.262208 1.627048,4.559172 0,3.289796 -0.02945,5.944904 -0.02945,6.751864 0,0.658866 0.432898,1.425025 1.649134,1.18475 9.523755,-3.262596 16.389752,-12.482185 16.389752,-23.354987 0,-13.59591 -10.739992,-24.61680394 -23.987552,-24.61680394"
                                style="fill:#1b1817;fill-opacity:1;fill-rule:evenodd;stroke:none" />
                        </g>
                    </svg>
                </a><br />
                <small>automated architectural tests</small>
            </h1>
        </div>

        <div class="panel-group">
            <div class="row">
                <div class="col-lg-12">
                    <input
                            type="text"
                            class="form-control"
                            id="search-field"
                            name="search-field"
                            placeholder="Filter by filename ..."
                            oninput="onSearchInput(this.value)"
                    />
                </div>
            </div>
        </div>

        <div class="panel-group">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        Report with Emphasis on the Diff
                    </h4>
                </div>
                <div class="panel-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-9">
                                <dl class="dl-horizontal">
                                    <dt>commit</dt>
                                    <dd><?= $report["current"]["commit_hash"] ?></dd>
                                    <dt>compared to</dt>
                                    <dd><?= $report["previous"]["commit_hash"] ?></dd>
                                </dl>
                                <div class="jumbotron" id="violations-overview-message">
                                    <p <?=($msg!=0)?'class="no-display"':""?>>
                                        Nothing new...
                                    </p>
                                    <p <?=($msg!=1)?'class="no-display"':""?>>
                                        You added and removed violations, but no change
                                        in total. Maybe you refactored some code?
                                    </p>
                                    <p class="text-success<?=($msg!=2)?' no-display':""?>">
                                        You resolved some violations. Great Success!
                                    </p>
                                    <p class="text-success<?=($msg!=3)?' no-display':""?>">
                                        You resolved more violations than you added. Nice!
                                    </p>
                                    <p class="text-danger<?=($msg!=4)?' no-display':""?>">
                                        You added more violations than you resolved. Don't
                                        give up!
                                    </p>
                                    <p class="text-danger<?=($msg!=5)?' no-display':""?>">
                                        You added some violations. Please take care of the
                                        code!
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">Violations</h4>
                                    </div>
                                    <div class="panel-body">
                                        <ul class="list-group">
                                            <li class="list-group-item">
                                                <strong>total</strong>
                                                <span class="badge" id="violations-overview-total"><?=$total?></span>
                                            </li>
                                            <li class="list-group-item <?=$resolved>0?"list-group-item-success":""?>">
                                                resolved
                                                <span class="badge" id="violations-overview-resolved"><?=$resolved?></span>
                                            </li>
                                            <li class="list-group-item <?=$added>0?"list-group-item-danger":""?>">
                                                added 
                                                <span class="badge" id="violations-overview-added"><?=$added?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="panel-group"
            id="rules-accordion"
            role="tablist"
            aria-multiselectable="true">
<?php   $i = 0;
        foreach ($report["rules"] as $rule) {
            $i++;
            $total = $rule["violations"]["total"];
            $resolved = $rule["violations"]["resolved"];
            $added = $rule["violations"]["added"];
            $diff = $resolved - $added;
            if ($diff == 0) {
                $class = "default";
            }
            else if ($diff < 0) {
                $class = "danger";
            }
            else {
                $class = "success";
            }
?>
            <div class="panel panel-<?=$class?> rule">
                <div class="panel-heading" role="tab" id="rule_<?=$i?>_heading">
                    <h4 class="panel-title">
                        <a
                            role="<?=$i==1?"button":"collapsed"?>"
                            data-toggle="collapse"
                            data-parent="#rules-accordion"
                            href="#rule_<?=$i?>"
                            aria-expanded="<?=$i==1?"true":"false"?>"
                            aria-controls"#rule_<?=$i?>">
                            <?=htmlentities(wordwrap($rule["rule"], 80, " ", true))?>
                        </a>
                    </h4>
                </div>
                <div
                    id="rule_<?=$i?>"
                    class="panel-collapse collapse <?=$i==1?"in":""?>"
                    role="tabpanel"
                    aria-labelledby="rule_<?=$i?>_heading">
                    <div class="panel-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="well well-sm">
                                        <?= htmlentities(str_replace("\n", " ", $rule["explanation"])) ?> 
                                    </div>
                                    <ul class="list-group rule-violations">
<?php       foreach ($rule["violations"]["list"] as $v) {
                if(isset($v["last_seen_in"])) {
                    $cl = "list-group-item-success";
                }
                else if ($v["introduced_in"] == $report["run_id"]) {
                    $cl = "list-group-item-danger";
                }

                else {
                    $cl = "";
                }
?>
                                        <li class="list-group-item <?=$cl?>">
                                            <?=$v["file"]?> (l. <?=$v["line_no"]?>)
<?php           if ($v["url"] !== null) { ?>
                                            <a href="<?=$v["url"]?>" target="_blank">
                                                <span
                                                    class="glyphicon glyphicon-zoom-in"
                                                    aria-hidden="true">
                                                </span>
                                            </a>
<?php           } ?>
                                        </li>
<?php       } ?>
                                    </ul>
                                </div>
                                <div class="col-md-3">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">Violations</h4>
                                        </div>
                                        <div class="panel-body">
                                            <ul class="list-group">
                                                <li class="list-group-item">
                                                    <strong>total</strong>
                                                    <span class="badge violation-total"><?=$total?></span>
                                                </li>
                                                <li class="list-group-item <?=$resolved>0?"list-group-item-success":""?>">
                                                    resolved
                                                    <span class="badge violation-resolved"><?=$resolved?></span>
                                                </li>
                                                <li class="list-group-item <?=$added>0?"list-group-item-danger":""?>">
                                                    added 
                                                    <span class="badge violation-added"><?=$added?></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php } ?>
        </div>
    </div>
</body>
</html>
<?php
}
