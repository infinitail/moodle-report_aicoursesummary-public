<?php

namespace report_aicoursesummary;

require_once __DIR__.'/variables_trait.php';

class summary 
{
    use variables_trait;

    /**
     * Get course summary list of course
     * 
     * @param ?int $courseid
     * @return array|false
     */
    public function get_course_summary_list(?int $courseid)
    {
        global $DB;

        if (is_null($courseid)) {
            return $DB->get_records(self::TABLE_SUMMARY, []);
        }

        return $DB->get_records(self::TABLE_SUMMARY, ['course' => $courseid]);
    }

    /**
     * 
     * @param int $summaryid
     * @return string
     */
    public function get_progress(int $summaryid)
    {
        global $DB;

        $summary = $DB->get_record(self::TABLE_SUMMARY, ['id' => $summaryid], '*', MUST_EXIST);
        
        switch ($summary->status) {
            case self::STATUS_QUESTION_QUERYING:
                $records = $DB->get_records(self::TABLE_QUESTION, ['coursesummary' => $summaryid]);
                $filteredrecords = array_filter($records, function($value){ if(!empty($value->questionunveil)) return $value; });
                return '('.count($filteredrecords).'/'.count($records).')' ;

            case self::STATUS_USER_QUERYING:
                $records = $DB->get_records(self::TABLE_USER, ['coursesummary' => $summaryid]);
                $filteredrecords = array_filter($records, function($value){ if(!empty($value->airesponse)) return $value; });
                return '('.count($filteredrecords).'/'.count($records).')' ;

            default:
                // no extra param
                return '';
        }
    }
}
