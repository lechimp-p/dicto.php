<?php

namespace Lechimp\Dicto\Report;

/**
 * Queries on the ResultDB.
 */
class Queries {
    /**
     * @var ResultDB
     */
    protected $result_db;

    public function __construct(ResultDB $result_db) {
        $this->result_db = $result_db;
    }

    /**
     * Get the id of the current run.
     *
     * @return  int
     */
    public function current_run() {
        $b = $this->result_db->builder();
        $res = $b
            ->select("id")
            ->from("runs")
            ->orderBy("id", "DESC")
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        if ($res) {
            return (int)$res["id"];
        }
        throw new \RuntimeException("Result database contains no runs.");
    }

    /**
     * Get the id of the previous run.
     *
     * @return  int
     */
    public function previous_run() {
        $b = $this->result_db->builder();
        $res = $b
            ->select("id")
            ->from("runs")
            ->orderBy("id", "DESC")
            ->setMaxResults(2)
            ->execute();
        // Drop current
        $res->fetch();
        $res = $res->fetch();
        if ($res) {
            return (int)$res["id"];
        }
        throw new \RuntimeException("Result database contains no previous run.");
    }

    /**
     * Get the id of previous run with another commit id as current run.
     *
     * @return  int
     */
    public function previous_run_with_different_commit() {
        $cur = $this->current_run();
        $commit_hash = $this->run_info($cur)["commit_hash"];
        $b = $this->result_db->builder();
        $res = $b
            ->select("id")
            ->from("runs")
            ->where("commit_hash <> ?")
            ->setParameter(0, $commit_hash)
            ->orderBy("id", "DESC")
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        if ($res) {
            return (int)$res["id"];
        }
        throw new \RuntimeException("Result database contains previous run with a different commit.");
    }

    /**
     * Get the id of the last run for a certain commit.
     *
     * @param   string  $commit_hash
     * @return  int|null
     */
    public function last_run_for($commit_hash) {
        $b = $this->result_db->builder();
        $res = $b
            ->select("id")
            ->from("runs")
            ->where("commit_hash = ?")
            ->setParameter(0, $commit_hash)
            ->orderBy("id", "DESC")
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        if ($res) {
            return (int)$res["id"];
        }
        throw new \RuntimeException("Result database contains no runs.");
    }

    /**
     * Get information about a run.
     *
     * @param   int $run
     * @return  array<string,string>     with keys 'commit_hash'
     */
    public function run_info($run) {
        $b = $this->result_db->builder();
        $res = $b
            ->select("commit_hash")
            ->from("runs")
            ->where("id = ?")
            ->setParameter(0, $run)
            ->execute()
            ->fetch();
        if ($res) {
            return $res;
        }
        throw new \RuntimeException("Result database contains no run with id '$run'.");
    }

    /**
     * Get the amount of violations in a run.
     *
     * @param   int         $run
     * @param   int|null    $rule
     * @return  int
     */
    public function count_violations_in($run, $rule = null) {
        $b = $this->result_db->builder();
        $res = $b
            ->select("COUNT(*) cnt")
            ->from("runs", "rs")
            ->innerJoin("rs", "violations", "vs",
                "rs.id >= vs.first_seen AND rs.id <= vs.last_seen")
            ->where("rs.id = ?")
            ->setParameter(0, $run)
            ->execute()
            ->fetch();
        if ($res) {
            return (int)$res["cnt"];
        }
        throw new \RuntimeException("Result database contains no run with id '$run'.");
    }

    /**
     * Get the amount of violations that were added in a run.
     *
     * @param   int         $run_former
     * @param   int         $run_latter
     * @param   int|null    $rule
     * @return  int
     */
    public function count_added_violations($run_former, $run_latter, $rule = null) {
    }

    /**
     * Get the amount of violations that were resolved in a run.
     *
     * @param   int         $run_former
     * @param   int         $run_latter
     * @param   int|null    $rule
     * @return  int
     */
    public function count_resolved_violations($run_former, $run_latter, $rule = null) {
    }

    /**
     * Get the rules that were analysed in a run.
     *
     * @param   int $run
     * @return  int[]
     */
    public function analysed_rules($run) {
    }

    /**
     * Get information about a rule.
     *
     * @param   int $rule
     * @return  array<string,string>    with keys 'rule', 'explanation'
     */
    public function rule_info($rule) {
    }

    /**
     * Get the violations off a rule.
     *
     * @param   int $rule
     * @return  array<string,string|int>    with keys 'file', 'line_no', 'introduced_in'
     */
    public function violations_of($rule) {
    }
}
