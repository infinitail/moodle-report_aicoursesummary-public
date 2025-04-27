<?php

namespace report_aicoursesummary\task;

require_once __DIR__.'/../query_question_process.php';
require_once __DIR__.'/../build_user_prompt.php';
require_once __DIR__.'/../query_user_process.php';

/**
 * Scheduled task
 */
class query extends \core\task\scheduled_task {
    /**
     * Define Scheduled task name
     */
    public function get_name(){
        return get_string('querytask', 'report_aicoursesummary');
    }

    public function execute() {
        global $CFG, $DB;

        mtrace('Query task started');

        mtrace('Start question query');
        $questionquery = new \report_aicoursesummary\query_question_process;
        $questionquery->perform();
        mtrace('Question query finished');

        mtrace('Start build user query');
        $buildprompt = new \report_aicoursesummary\build_user_prompt;
        $buildprompt->perform();
        mtrace('Build user query finished');

        mtrace('Start user query');
        $userquery = new \report_aicoursesummary\query_user_process;
        $userquery->perform();
        mtrace('User query finished');

        mtrace('Query task finished');
    }
}