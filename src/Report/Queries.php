<?php

namespace Lechimp\Dicto\Report;

/**
 * Queries on the ResultDB.
 */
class Queries
{
    /**
     * @var ResultDB
     */
    protected $result_db;

    public function __construct(ResultDB $result_db)
    {
        $this->result_db = $result_db;
    }

    /**
     * Get the id of the last run.
     *
     * @return  int
     */
    public function last_run()
    {
        $b = $this->result_db->builder();
        $res = $b
            ->select("id")
            ->from("runs")
            ->orderBy("id", "DESC")
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        if ($res) {
            return (int) $res["id"];
        }
        throw new \RuntimeException("Result database contains no runs.");
    }

    /**
     * Get the id of the last run for a certain commit.
     *
     * @param   string  $commit_hash
     * @return  int|null
     */
    public function last_run_for($commit_hash)
    {
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
            return (int) $res["id"];
        }
        throw new \RuntimeException("Result database contains no run for commit '$commit_hash'.");
    }

    /**
     * Get the id of the run before the given run.
     *
     * @param   int     $run
     * @return  int
     */
    public function run_before($run)
    {
        $b = $this->result_db->builder();
        $res = $b
            ->select("id")
            ->from("runs")
            ->where("id < ?")
            ->setParameter(0, $run)
            ->orderBy("id", "DESC")
            ->setMaxResults(1)
            ->execute();
        $res = $res->fetch();
        if ($res) {
            return (int) $res["id"];
        }
        throw new \RuntimeException("Result database contains no run before '$run'.");
    }

    /**
     * Get the id of the run before the given run that has another commit as the
     * given run.
     *
     * @param   int     $run
     * @return  int
     */
    public function run_with_different_commit_before($run)
    {
        $commit_hash = $this->run_info($run)["commit_hash"];
        $b = $this->result_db->builder();
        $res = $b
            ->select("id")
            ->from("runs")
            ->where("commit_hash <> ? AND id < ?")
            ->setParameter(0, $commit_hash)
            ->setParameter(1, $run)
            ->orderBy("id", "DESC")
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        if ($res) {
            return (int) $res["id"];
        }
        throw new \RuntimeException("Result database contains no run before '$run' with a different commit.");
    }

    /**
     * Get information about a run.
     *
     * @param   int $run
     * @return  array<string,string>     with keys 'commit_hash'
     */
    public function run_info($run)
    {
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
    public function count_violations_in($run, $rule = null)
    {
        $b = $this->result_db->builder();
        $q = $b
            ->select("COUNT(*) cnt")
            ->from("runs", "rs")
            ->innerJoin(
                "rs",
                "violations",
                "vs",
                "rs.id >= vs.first_seen AND rs.id <= vs.last_seen"
            )
            ->innerJoin(
                "vs",
                "violation_locations",
                "vls",
                "vs.id = vls.violation_id AND rs.id = vls.run_id"
            )
            ->where("rs.id = ?")
            ->setParameter(0, $run);
        if ($rule !== null) {
            $q = $q
                ->andWhere("vs.rule_id = ?")
                ->setParameter(1, $rule);
        }
        $res = $q
            ->execute()
            ->fetch();
        if ($res) {
            return (int) $res["cnt"];
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
    public function count_added_violations($run_former, $run_latter, $rule = null)
    {
        $b = $this->result_db->builder();
        $q = $b
            ->select(
                "(SELECT COUNT (*) " .
                "FROM violation_locations vls " .
                "WHERE vls.run_id = :former AND vls.violation_id = vs.id) cnt_former"
            )
            ->addSelect(
                "(SELECT COUNT (*) " .
                "FROM violation_locations vls " .
                "WHERE vls.run_id = :latter AND vls.violation_id = vs.id) cnt_latter"
            )
            ->from("violations", "vs")
            ->where("cnt_former < cnt_latter")
            ->setParameter("former", $run_former)
            ->setParameter("latter", $run_latter);
        if ($rule !== null) {
            $q = $q
                ->andWhere("vs.rule_id = :rule")
                ->setParameter("rule", $rule);
        }
        $rows = $q->execute();
        $res = 0;
        while ($r = $rows->fetch()) {
            if ((int) $r["cnt_latter"] > (int) $r["cnt_former"]) {
                $res += (int) $r["cnt_latter"] - (int) $r["cnt_former"];
            }
        }
        return $res;
    }

    /**
     * Get the amount of violations that were resolved in a run.
     *
     * @param   int         $run_former
     * @param   int         $run_latter
     * @param   int|null    $rule
     * @return  int
     */
    public function count_resolved_violations($run_former, $run_latter, $rule = null)
    {
        $b = $this->result_db->builder();
        $q = $b
            ->select(
                "(SELECT COUNT (*) " .
                "FROM violation_locations vls " .
                "WHERE vls.run_id = :former AND vls.violation_id = vs.id) cnt_former"
            )
            ->addSelect(
                "(SELECT COUNT (*) " .
                "FROM violation_locations vls " .
                "WHERE vls.run_id = :latter AND vls.violation_id = vs.id) cnt_latter"
            )
            ->from("violations", "vs")
            ->where("cnt_former > cnt_latter")
            ->setParameter("former", $run_former)
            ->setParameter("latter", $run_latter);
        if ($rule !== null) {
            $q = $q
                ->andWhere("vs.rule_id = :rule")
                ->setParameter("rule", $rule);
        }
        $rows = $q->execute();
        $res = 0;
        while ($r = $rows->fetch()) {
            if ((int) $r["cnt_former"] > (int) $r["cnt_latter"]) {
                $res += (int) $r["cnt_former"] - (int) $r["cnt_latter"];
            }
        }
        return $res;
    }

    /**
     * Get the rules that were analyzed in a run.
     *
     * @param   int $run
     * @return  int[]
     */
    public function analyzed_rules($run)
    {
        $b = $this->result_db->builder();
        $res = $b
            ->select("rrs.id")
            ->from("runs", "rs")
            ->innerJoin(
                "rs",
                "rules",
                "rrs",
                "rs.id >= rrs.first_seen AND rs.id <= rrs.last_seen"
            )
            ->where("rs.id = ?")
            ->setParameter(0, $run)
            ->execute()
            ->fetchAll();
        return array_map(function ($r) {
            return $r["id"];
        }, $res);
    }

    /**
     * Get information about a rule.
     *
     * @param   int $rule
     * @return  array<string,string>    with keys 'rule', 'explanation'
     */
    public function rule_info($rule)
    {
        $b = $this->result_db->builder();
        $res = $b
            ->select("rule", "explanation")
            ->from("rules")
            ->where("rules.id = ?")
            ->setParameter(0, $rule)
            ->execute()
            ->fetch();
        if ($res) {
            return $res;
        }
        throw new \RuntimeException("Result database contains no rule with id '$rule'.");
    }

    /**
     * Get the violations of a rule.
     *
     * @param   int $rule
     * @param   int $run
     * @return  array<string,(string|int)>[]  with keys 'file', 'line_no', 'introduced_in'
     */
    public function violations_of($rule, $run)
    {
        $b = $this->result_db->builder();
        return $b
            ->select("vs.file", "vls.line_no", "vs.first_seen introduced_in")
            ->from("runs", "rs")
            ->innerJoin(
                "rs",
                "violations",
                "vs",
                "rs.id >= vs.first_seen AND rs.id <= vs.last_seen"
            )
            ->innerJoin(
                "vs",
                "violation_locations",
                "vls",
                "vs.id = vls.violation_id AND rs.id = vls.run_id"
            )
            ->where("rs.id = ?")
            ->andWhere("vs.rule_id = ?")
            ->setParameter(0, $run)
            ->setParameter(1, $rule)
            ->execute()
            ->fetchAll();
    }

    /**
     * Get all resolved violations of the given rule between the two given runs.
     *
     * I.e.: get violations that existed in run_former but do no exist in run_latter.
     *
     * @param int $rule
     * @param int $run_former
     * @param int $run_latter
     *
     * @return  array<string,(string|int)>[]  with keys 'file', 'line_no', 'introduced_in', 'resolved_in'
     */
    public function resolved_violations($rule, $run_former, $run_latter)
    {
        $b = $this->result_db->builder();

        return $b
            ->select('vs.file', 'vls.line_no', 'vs.first_seen introduced_in', 'vs.last_seen last_seen_in')
            ->from('violations', 'vs')
            ->innerJoin(
                'vs',
                'violation_locations',
                'vls',
                'vs.id = vls.violation_id AND vls.run_id = vs.last_seen'
            )
            ->innerJoin('vs', 'rules', 'ru', 'ru.id = vs.rule_id')
            ->where('vs.first_seen <= :former')
            ->andWhere('vs.last_seen < :latter')
            ->andWhere('ru.id = :rule')
            ->setParameter(0, $run_former)
            ->setParameter(1, $run_latter)
            ->setParameter(2, $rule)
            ->execute()
            ->fetchAll();
    }
}
