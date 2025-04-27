<?php

namespace report_aicoursesummary;

require_once __DIR__.'/variables_trait.php';
require_once __DIR__.'/../lib/quiz_attempts_report_acs.php';
require_once __DIR__.'/../../../lib/modinfolib.php';
require_once __DIR__.'/../../../mod/quiz/classes/quiz_settings.php';
require_once __DIR__.'/../../../mod/quiz/report/statistics/report.php';

class deploy_user_process 
{
    use variables_trait;

    public function perform(int $summaryid)
    {
        global $DB;
        
        $summary = $DB->get_record(self::TABLE_SUMMARY, ['id' => $summaryid,'status' => self::STATUS_QUESTION_DEPLOYED]);
        if ($summary === false) {
            return false;
        }
        
        $course = get_course($summary->course);
        $coursecontext = \context_course::instance($course->id);
        
        $cmids = json_decode($summary->coursemodules);
        
        // Get course enrolled users (incl. teacher)
        $users = get_enrolled_users($coursecontext);
        
        // Filter only student
        foreach ($users as $user) {
            // Skip Teacher
            if (is_enrolled($coursecontext, $user, 'moodle/course:update')) {
                continue;
            }
        
            mtrace('Processing Student ID: ' . $user->id);

            $params = [];
            foreach ($cmids as $cmid) {
                //echo '<pre>CMID '; var_dump($cmid); echo '</pre>';
        
                $cm   = $DB->get_record('course_modules', ['id' => $cmid]);
                $quiz = $DB->get_record('quiz', ['id' => $cm->instance]);
        
                // Quiz内のQuestionの最高点などを取得
                $questions = quiz_report_get_significant_questions($quiz);
        
                $questionreport = new \quiz_attempts_report_acs();
                $questiongrades = $questionreport->get_report($quiz, $cm, $course);
                
                // 該当する学生の得点（素点）を抽出
                foreach ($questiongrades as $questiongrade) {
                    $questiongrade = (object) $questiongrade;
        
                    if ($questiongrade->userid === $user->id) {
                        $qscore = clone($questiongrade);
                        unset($qscore->fullname, $qscore->email, $qscore->userid);  // 氏名など不要な情報を削除
                        $params[$cm->id] = $qscore;
                    }
                }
            }
        
            // 回答の無い学生を無視
            if (count($params) === 0) {
                continue;
            }
            
            $entry = [
                'coursesummary' => $summary->id,
                'course'        => $summary->course,
                'user'          => $user->id,
                'aiquery'       => json_encode($params),
                'datecreated'   => time(),
            ];
            $DB->insert_record(self::TABLE_USER, $entry);
        }
         
        $success = $DB->update_record(self::TABLE_SUMMARY, ['id' => $summary->id, 'status' => self::STATUS_QUESTION_QUERYING]);
        return $success;
    }
}