<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/mod/quiz/report/overview/overview_table.php';

// Related classes
// https://github.com/moodle/moodle/blob/master/mod/quiz/report/overview/overview_table.php
// https://github.com/moodle/moodle/blob/master/mod/quiz/report/attemptsreport.php
// https://github.com/moodle/moodle/blob/master/mod/quiz/report/default.php
// https://github.com/moodle/moodle/blob/master/lib/tablelib.php

class quiz_overview_acs extends quiz_overview_table {
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton='') {
        global $CFG, $DB;

        if (!$this->columns) {
            $onerow = $DB->get_record_sql("SELECT {$this->sql->fields} FROM {$this->sql->from} WHERE {$this->sql->where}",
                $this->sql->params, IGNORE_MULTIPLE);
            //if columns is not set then define columns as the keys of the rows returned
            //from the db.
            $this->define_columns(array_keys((array)$onerow));
            $this->define_headers(array_keys((array)$onerow));
        }
        $this->pagesize = $pagesize;
        $this->setup();
        $this->query_db($pagesize, $useinitialsbar);
        //$this->build_table(); // Disable output table
        $this->close_recordset();
        //$this->finish_output();

        //echo '<pre>'; var_dump($this->rawdata); echo '</pre>';die();
        $score_rows = [];
        foreach ($this->rawdata as $row) {
            // Get Users and scores
            $formattedrow = $this->format_row($row);
            
            //var_dump($formattedrow['fullname']);
            if (preg_match('/\/user\/(view|profile).php\?id=([0-9]+)&?/', $formattedrow['fullname'], $matches)) {
                $formattedrow['userid'] = $matches[2];
            } else {
                $formattedrow['userid'] = '';
            }
            
            
            // Get User fullname without any tag nor comment
            $formattedrow['fullname'] = strip_tags(explode('<br', $formattedrow['fullname'])[0]);

            unset($formattedrow['picture']);

            $score_rows[] = $formattedrow;
        }

        //echo '<pre>'; var_dump($score_rows); echo '</pre>';

        return $score_rows;
    }

    /**
     * @param object $attempt the row of data - see the SQL in display() in
     * mod/quiz/report/overview/report.php to see what fields are present,
     * and what they are called.
     * @return string the contents of the cell.
     */
    public function col_sumgrades($attempt) {
        if ($attempt->state != quiz_attempt::FINISHED) {
            return '-';
        }

        $grade = quiz_rescale_grade($attempt->sumgrades, $this->quiz);

        return $grade;
    }

    /**
     * @param string $colname the name of the column.
     * @param object $attempt the row of data - see the SQL in display() in
     * mod/quiz/report/overview/report.php to see what fields are present,
     * and what they are called.
     * @return string the contents of the cell.
     */
    public function other_cols($colname, $attempt) {
        if (!preg_match('/^qsgrade(\d+)$/', $colname, $matches)) {
            return null;
        }
        $slot = $matches[1];

        $question = $this->questions[$slot];
        if (!isset($this->lateststeps[$attempt->usageid][$slot])) {
            return '-';
        }

        $stepdata = $this->lateststeps[$attempt->usageid][$slot];
        $state = question_state::get($stepdata->state);

        if ($question->maxmark == 0) {
            $grade = '-';
        } else if (is_null($stepdata->fraction)) {
            if ($state == question_state::$needsgrading) {
                $grade = get_string('requiresgrading', 'question');
            } else {
                $grade = '-';
            }
        } else {
            $grade = $stepdata->fraction;       // Return percentage instead of real score
        }

        return $grade;
    }
}