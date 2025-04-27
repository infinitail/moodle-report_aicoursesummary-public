<?php

namespace report_aicoursesummary\task;

require_once __DIR__.'/../deploy_question_process.php';
require_once __DIR__.'/../deploy_user_process.php';

/**
 * Adhoc task
 */
class deploy extends \core\task\adhoc_task {
    /**
     * Define Scheduled task name
     */
    public function get_name(){
        return get_string('deploytask', 'report_aicoursesummary');
    }

    public function execute() {
        global $CFG, $DB;
        
        mtrace('Deploy task started');

        $data = $this->get_custom_data();

        mtrace('Start question deploy');
        $qdeploy = new \report_aicoursesummary\deploy_question_process;
        $qdeploy->perform($data->id);
        mtrace('Question deploy finished');

        mtrace('Start user deploy');
        $udeploy = new \report_aicoursesummary\deploy_user_process;
        $udeploy->perform($data->id);
        mtrace('Question deploy finished');

        mtrace('Deploy task finished');
    }
}