<?php

namespace Lechimp\Dicto\Report;

/**
 * Shows the diff in violations between two commits per rule.
 */
class DiffPerRuleReport extends Report {
    /**
     * @inheritdoc
     */
    protected function default_template_path() {
        return __DIR__."/../../templates/diff_per_rule.php";
    }

    /**
     * @inheritdoc
     */
    public function generate() {
        $cur_run = $this->queries->current_run();
        $prev_run = $this->queries->previous_run_with_different_commit();
        return
            [ "current" => $this->queries->run_info($cur_run)
            , "previous" => $this->queries->run_info($prev_run)
            , "violations" =>
                [ "total" => $this->queries->count_violations_in($cur_run)
                , "added" => $this->queries->count_added_violations($prev_run, $cur_run)
                , "resolved" => $this->queries->count_resolved_violations($prev_run, $cur_run)
                ]
            , "rules" => array_map
                ( function($rule) use ($cur_run, $prev_run) {
                    $rule_info = $this->queries->rule_info($rule);
                    return
                        [ "rule" => $rule_info["rule"]
                        , "explanation" => $rule_info["explanation"]
                        , "violations" =>
                            [ "total" => $this->queries->count_violations_in($cur_run, $rule)
                            , "added" => $this->queries->count_added_violations($prev_run, $cur_run, $rule)
                            , "resolved" => $this->queries->count_resolved_violations($prev_run, $cur_run, $rule)
                            , "list" => $this->queries->violations_of($rule, $cur_run)
                            ]
                        ];
                }
                , $this->queries->analyzed_rules($cur_run)
                )
            ];
    }
}
