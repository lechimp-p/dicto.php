<?php

namespace Lechimp\Dicto\Report;

/**
 * Shows the diff in violations between two commits per rule.
 */
class DiffPerRuleReport extends Report
{
    /**
     * @inheritdoc
     */
    protected function default_template()
    {
        return "diff_per_rule";
    }

    /**
     * @return  string|null
     */
    protected function source_url()
    {
        return $this->custom_config_value("source_url", null);
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $cur_run = $this->queries->last_run();
        $prev_run = $this->queries->run_with_different_commit_before($cur_run);
        $source_url = $this->source_url();
        $current = $this->queries->run_info($cur_run);
        $previous = $this->queries->run_info($prev_run);

        return
            [ "run_id" => $cur_run
            , "current" => $current
            , "previous" => $this->queries->run_info($prev_run)
            , "violations" =>
                [ "total" => $this->queries->count_violations_in($cur_run)
                , "added" => $this->queries->count_added_violations($prev_run, $cur_run)
                , "resolved" => $this->queries->count_resolved_violations($prev_run, $cur_run)
                ]
            , "rules" => array_map(
                    function ($rule) use ($cur_run, $prev_run, $current, $previous, $source_url) {
                    $rule_info = $this->queries->rule_info($rule);
                    $violations = array_merge(
                            $this->queries->violations_of($rule, $cur_run),
                            $this->queries->resolved_violations($rule, $prev_run, $cur_run)
                        );
                    usort($violations, function ($l, $r) {
                        $file_cmp = strcmp($l["file"], $r["file"]);
                        if ($file_cmp == 0) {
                            if ($l["line_no"] == $r["line_no"]) {
                                return 0;
                            }
                            if ($l["line_no"] < $r["line_no"]) {
                                return -1;
                            } else {
                                return 1;
                            }
                        }
                        return $file_cmp;
                    });
                    return
                        [ "rule" => $rule_info["rule"]
                        , "explanation" => $rule_info["explanation"]
                        , "violations" =>
                            [ "total" => $this->queries->count_violations_in($cur_run, $rule)
                            , "added" => $this->queries->count_added_violations($prev_run, $cur_run, $rule)
                            , "resolved" => $this->queries->count_resolved_violations($prev_run, $cur_run, $rule)
                            , "list" => array_map(
                                    function ($v) use ($current, $previous, $source_url) {
                                    if ($source_url !== null) {
                                        $v["url"] = $this->make_url(
                                                            $source_url,
                                                            (isset($v["last_seen_in"])) ? $previous["commit_hash"] : $current["commit_hash"],
                                                            $v["file"],
                                                            $v["line_no"]
                                                        );
                                    } else {
                                        $v["url"] = null;
                                    }
                                    return $v;
                                },
                                    $violations
                                )]
                        ];
                },
                    $this->queries->analyzed_rules($cur_run)
                )
            ];
    }

    protected function make_url($source_url, $commit_hash, $file, $line)
    {
        return
            str_replace(
                "{COMMIT}",
                $commit_hash,
                str_replace(
                "{FILE}",
                $file,
                str_replace(
                "{LINE}",
                $line,
                $source_url
            )
            )
            );
    }
}
