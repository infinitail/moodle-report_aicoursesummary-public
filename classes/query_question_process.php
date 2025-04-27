<?php

namespace report_aicoursesummary;

require_once __DIR__.'/variables_trait.php';

class query_question_process 
{
    use variables_trait;

    public function perform()
    {
        global $DB;

        // TODO: Check AI instance is configured

        $systemcontext = \context_system::instance();

        // status = REPORT_AICOURSESUMMARY_QUESTION_QUERYING  の report_aicoursesummary_courses の行だけ取得する。
        $summary = $DB->get_record(self::TABLE_SUMMARY, ['status' => self::STATUS_QUESTION_QUERYING], '*', IGNORE_MULTIPLE);

        if ($summary === false) {
            return false;
        }

        // 取得した report_aicoursesummary_courses.id で report_aicoursesummary_questions.coursesummary を検索。
        $questions = $DB->get_records(self::TABLE_QUESTION, ['coursesummary' => $summary->id]);

        foreach ($questions as $question) {
            mtrace('Processing Question ID: ' . $question->id);

            if (! is_null($question->questionunveil)) {
                continue;
            }

            // Prepare the action.
            $action = new \core_ai\aiactions\generate_text(
                contextid: $systemcontext->id,
                userid: $summary->owner,
                prompttext: $question->questionprompt,
            );

            // Send the action to the AI manager.
            $manager = \core\di::get(\core_ai\manager::class);
            $response = $manager->process_action($action);

            $responsedata = (object) $response->get_response_data();

            //if ($responsedata) {        // TODO: ADD check error case  
                $update = [
                    'id'             => $question->id,
                    'questionunveil' => $responsedata->generatedcontent,
                    'qresponseid'    => $responsedata->id,
                    //'dateresponded'  => time(),
                ];

                $success = $DB->update_record(self::TABLE_QUESTION, $update);
            //}

            set_time_limit(10);
        }

        // All completed
        $success = $DB->update_record(self::TABLE_SUMMARY, ['id' => $summary->id, 'status' => self::STATUS_USER_QUERY_DEPLOYING]);

        return $success;
    }
}