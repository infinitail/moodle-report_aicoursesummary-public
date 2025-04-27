<?php

namespace report\aicoursesummary;

require_once __DIR__.'/../../../config.php';

require_once $CFG->libdir.'/gradelib.php';
//require_once $CFG->libdir.'/tablelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/mod/quiz/report/reportlib.php';

class course_quiz
{
    /**
     * Get course quiz list
     * 
     * @param int $courseid
     * @return array
     */
    final public static function get_list(int $courseid): array
    {
        global $DB;

        $gtree = new \grade_tree($courseid, false, false);
        
        $modinfo = $gtree->modinfo;
        $cminstances = $modinfo->get_instances_of('quiz');

        $quizzes = [];
        foreach ($gtree->items as $item) {
            if ($item->itemmodule !== 'quiz') {
                continue;
            }
        
            $quiz = $DB->get_record('quiz', ['id' => $item->iteminstance]);
            $cm = $cminstances[$item->iteminstance];
        
            $quizzes[] = (object) [
                'cmid' => $cm->id,
                'name' => $item->itemname,
                'questions'      => count(quiz_report_get_significant_questions($quiz)),
                'numberofgrades' => count(quiz_get_user_grades($quiz)),
            ];
        }

        return $quizzes;
    }
}