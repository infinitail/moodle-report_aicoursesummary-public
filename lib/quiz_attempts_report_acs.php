<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/mod/quiz/report/overview/overview_form.php';
require_once $CFG->dirroot.'/mod/quiz/report/overview/overview_options.php';
require_once __DIR__.'/quiz_overview_acs.php';

class quiz_attempts_report_acs extends mod_quiz\local\reports\attempts_report {
    public $course;

    // Override display() and use it but nothing display
    final public function get_report($quiz, $cm, $course) {
        return $this->display($quiz, $cm, $course);
    }

    // https://github.com/moodle/moodle/blob/master/mod/quiz/report/overview/report.php#L47
    public function display($quiz, $cm, $course) {
        global $DB, $OUTPUT, $PAGE;

        list($currentgroup, $studentsjoins, $groupstudentsjoins, $allowedjoins) =
            $this->init('overview', 'quiz_overview_settings_form', $quiz, $cm, $course);

        $options = new quiz_overview_options('overview', $quiz, $cm, $course);
        //$options->process_settings_from_params();
        $options->pagesize   = PHP_INT_MAX;     // Set "unlimited"
        $options->onlygraded = 1;
        $options->states     = ['finished'];    // Ignore "inprogress", "overdue", "abandoned"
        //$options->tsort      = 'idnumber';
        //$options->tdir       = '4';

        $this->form->set_data($options->get_initial_form_data());

        // Load the required questions.
        $questions = quiz_report_get_significant_questions($quiz);

        // Prepare for downloading, if applicable.
        //$courseshortname = format_string($course->shortname, true,
        //        ['context' => context_course::instance($course->id)]);
        $table = new quiz_overview_acs($quiz, $this->context, $this->qmsubselect,
                $options, $groupstudentsjoins, $studentsjoins, $questions, $options->get_url());
        //echo '<pre>QUESTION OVERVIEW'; var_dump($overview); echo '</pre>';

        //$filename = quiz_report_download_filename(get_string('overviewfilename', 'quiz_overview'),
        //        $courseshortname, $quiz->name);
        //$overview->is_downloading($options->download, $filename,
        //        $courseshortname . ' ' . format_string($quiz->name, true));
        raise_memory_limit(MEMORY_EXTRA);

        $this->hasgroupstudents = false;
        if (!empty($groupstudentsjoins->joins)) {
            $sql = "SELECT DISTINCT u.id
                      FROM {user} u
                    $groupstudentsjoins->joins
                     WHERE $groupstudentsjoins->wheres";
            $this->hasgroupstudents = $DB->record_exists_sql($sql, $groupstudentsjoins->params);
        }
        $hasstudents = false;
        if (!empty($studentsjoins->joins)) {
            $sql = "SELECT DISTINCT u.id
                    FROM {user} u
                    $studentsjoins->joins
                    WHERE $studentsjoins->wheres";
            $hasstudents = $DB->record_exists_sql($sql, $studentsjoins->params);
        }
        if ($options->attempts == self::ALL_WITH) {
            // This option is only available to users who can access all groups in
            // groups mode, so setting allowed to empty (which means all quiz attempts
            // are accessible, is not a security porblem.
            $allowedjoins = new \core\dml\sql_join();
        }

        $this->course = $course; // Hack to make this available in process_actions.
        $this->process_actions($quiz, $cm, $currentgroup, $groupstudentsjoins, $allowedjoins, $options->get_url());

        $hasquestions = quiz_has_questions($quiz->id);

        $hasstudents = $hasstudents && (!$currentgroup || $this->hasgroupstudents);
        if ($hasquestions && ($hasstudents || $options->attempts == self::ALL_WITH)) {
            // Construct the SQL.
            $table->setup_sql_queries($allowedjoins);

            // Define table columns.
            $columns = [];
            $headers = [];

            $this->add_user_columns($table, $columns, $headers);
            //$this->add_grade_columns($quiz, $options->usercanseegrades, $columns, $headers, false);

            if ($options->slotmarks) {
                foreach ($questions as $slot => $question) {
                    // Ignore questions of zero length.
                    $columns[] = 'qsgrade' . $slot;
                    //$header = get_string('qbrief', 'quiz', $question->number);
                    //if (!$table->is_downloading()) {
                    //    $header .= '<br />';
                    //} else {
                    //    $header .= ' ';
                    //}
                    //$header .= '/' . quiz_rescale_grade($question->maxmark, $quiz, 'question');
                    //$headers[] = $header;
                }
            }

            //$table->define_columns($columns);
            //$table->define_headers($headers);
            //$table->sortable(true, 'uniqueid');

            $this->set_up_table_columns($table, $columns, $headers, $this->get_base_url(), $options, false);
            //$table->set_attribute('class', 'generaltable generalbox grades');

            $questiongrades = $table->out($options->pagesize, true);
            //echo '<pre>QUESTION MATRIX'; var_dump($matrix); echo '</pre>';

            return $questiongrades;
            // Terminated!
        }
    }

    /**
     * Unlock the session and allow the regrading process to run in the background.
     */
    protected function unlock_session() {
        \core\session\manager::write_close();
        ignore_user_abort(true);
    }

    /**
     * Are there any pending regrades in the table we are going to show?
     * @param string $from tables used by the main query.
     * @param string $where where clause used by the main query.
     * @param array $params required by the SQL.
     * @return bool whether there are pending regrades.
     */
    protected function has_regraded_questions($from, $where, $params) {
        global $DB;
        return $DB->record_exists_sql("
                SELECT 1
                  FROM {$from}
                  JOIN {quiz_overview_regrades} qor ON qor.questionusageid = quiza.uniqueid
                 WHERE {$where}", $params);
    }
}