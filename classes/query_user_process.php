<?php

namespace report_aicoursesummary;

require_once __DIR__.'/variables_trait.php';

class query_user_process 
{
    use variables_trait;

    public function perform()
    {
        global $DB;

        $systemcontext = \context_system::instance();

        $summary = $DB->get_record(self::TABLE_SUMMARY, ['status' => self::STATUS_USER_QUERYING], '*', IGNORE_MULTIPLE);
        if ($summary === false) {
            return false;
        }
        $queries = $DB->get_records(self::TABLE_USER, ['coursesummary' => $summary->id]);

        foreach ($queries as $query) {
            // Skip already processed
            if (! is_null($query->airesponse)) {
                continue;
            }

            mtrace('Processing query ID: ' . $query->id);

            // Prepare the action.
            $action = new \core_ai\aiactions\generate_text(
                contextid: $systemcontext->id,
                userid: $summary->owner,
                prompttext: $query->aiprompt
            );
            
            // Send the action to the AI manager.
            $manager = \core\di::get(\core_ai\manager::class);
            $response = $manager->process_action($action);
            //echo '<pre>RESPONSE: '; var_dump($response); echo '</pre>';
            $responsedata = (object) $response->get_response_data();
            //echo '<pre>RESPONSE: '; var_dump($responsedata); echo '</pre>';die();

            $update = [
                'id' => $query->id,
                'airesponse' => $responsedata->generatedcontent,
                'responseid' => $responsedata->id,
                'dateresponded' => time(),
            ];

            $DB->update_record(self::TABLE_USER, $update);
        }

        $success = $DB->update_record(self::TABLE_SUMMARY, ['id' => $summary->id, 'status' => self::STATUS_QUERY_COMPLETED]);
        return $success;
    }
}