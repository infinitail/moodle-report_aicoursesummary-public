<?php

namespace report_aicoursesummary;

trait variables_trait {
    const TABLE_SUMMARY = 'report_aicoursesummary_summaries';
    const TABLE_QUESTION = 'report_aicoursesummary_questions';
    const TABLE_USER = 'report_aicoursesummary_users';

    const STATUS_PRE_DEPLOY           = 1;
    const STATUS_QUESTION_DEPLOYED    = 11;
    const STATUS_USER_DEPLOYED        = 12;
    const STATUS_QUESTION_QUERYING    = 21;
    const STATUS_CONFIRMATION_WAITING = 31;    // future option
    const STATUS_USER_QUERY_DEPLOYING = 41;
    const STATUS_USER_QUERYING        = 51;
    const STATUS_QUERY_COMPLETED      = 91;
    const STATUS_PUBLISHED            = 95;
    const STATUS_CANCELLED            = 98;
    const STATUS_ABORTED              = 99;

    private $_status_definition = null;
    private $_max_phase_id = 4;

    private function _define_status() {
        $this->_status_definition = [
            self::STATUS_PRE_DEPLOY => [
                'phase_id' => 1,
                'name' => get_string('statuspredeploy', 'report_aicoursesummary'),
            ],
            self::STATUS_QUESTION_DEPLOYED => [
                'phase_id' => 1,
                'name' => get_string('statusquestiondeployed', 'report_aicoursesummary'),
            ],
            self::STATUS_USER_DEPLOYED => [
                'phase_id' => 1,
                'name' => get_string('statususerdeployed', 'report_aicoursesummary'),
            ],
            self::STATUS_QUESTION_QUERYING => [
                'phase_id' => 2,
                'name' => get_string('statusquestionquerying', 'report_aicoursesummary'),
            ],
            self::STATUS_CONFIRMATION_WAITING => [
                'phase_id' => 0,    // not use
                'name' => get_string('statusconfirmwaiting', 'report_aicoursesummary'),
            ],
            self::STATUS_USER_QUERY_DEPLOYING => [
                'phase_id' => 3,
                'name' => get_string('statususerquerydeploying', 'report_aicoursesummary'),
            ],
            self::STATUS_USER_QUERYING  => [
                'phase_id' => 3,
                'name' => get_string('statususerquerying', 'report_aicoursesummary'),
            ],
            self::STATUS_QUERY_COMPLETED => [
                'phase_id' => 4,
                'name' => get_string('statusquerycompleted', 'report_aicoursesummary'),
            ],
            /* Currently not used below */
            self::STATUS_PUBLISHED => [
                'phase_id' => 9,
                'name' => get_string('statuspublished', 'report_aicoursesummary'),
            ],
            self::STATUS_CANCELLED => [
                'phase_id' => 0,
                'name' => get_string('statuscancelled', 'report_aicoursesummary'),
            ],
            self::STATUS_ABORTED => [
                'phase_id' => 0,
                'name' => get_string('statusaborted', 'report_aicoursesummary'),
            ]
        ];
    }

    /**
     * Return name of status
     * 
     * @param int $status
     * @return string
     */
    public function get_status_name(int $status)
    {
        $this->_define_status();
        return $this->_status_definition[$status]['name'];
    }

    /**
     * Get phase id by status
     * 
     * @param int $status
     * @return int
     */
    public function get_phase_id(int $status)
    {
        $this->_define_status();
        return $this->_status_definition[$status]['phase_id'];
    }

    /**
     * Get pre defined max phase id
     * 
     * @param void
     * @return int
     */
    public function get_max_phase_id()
    {
        return $this->_max_phase_id;
    }
}