<?php

namespace Lechimp\Dicto\Report;

/**
 * Shows the diff in violations between two commits per rule.
 */
class TotalPerRuleReport extends Report
{
    /**
     * @inheritdoc
     */
    protected function default_template()
    {
        return "total_per_rule";
    }

    /**
     * TODO: Move this to Report.
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
        $source_url = $this->source_url();
        $current = $this->queries->run_info($cur_run);
        return
            [ "run_id" => $cur_run
            , "current" => $current
            , "violations" =>
                [ "total" => $this->queries->count_violations_in($cur_run)
                ]
            , "rules" => array_map(
                    function ($rule) use ($cur_run, $current, $source_url) {
                    $rule_info = $this->queries->rule_info($rule);
                    return
                        [ "rule" => $rule_info["rule"]
                        , "explanation" => $rule_info["explanation"]
                        , "violations" =>
                            [ "total" => $this->queries->count_violations_in($cur_run, $rule)
                            , "list" => array_map(
                                    function ($v) use ($current, $source_url) {
                                    if ($source_url !== null) {
                                        $v["url"] = $this->make_url(
                                                            $source_url,
                                                            $current["commit_hash"],
                                                            $v["file"],
                                                            $v["line_no"]
                                                        );
                                    } else {
                                        $v["url"] = null;
                                    }
                                    return $v;
                                },
                                    $this->queries->violations_of($rule, $cur_run)
                                )
                            ]
                        ];
                },
                    $this->queries->analyzed_rules($cur_run)
                )
            ];
    }

    /**
     * TODO: Move this to Report.
     *
     * @param   string  $source_url
     * @param   string  $commit_hash
     * @param   string  $file
     * @param   int     $line
     * @return  string
     */
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
