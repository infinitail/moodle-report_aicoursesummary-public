<?php

namespace report_aicoursesummary;

require_once __DIR__.'/variables_trait.php';
require_once __DIR__.'/../lib/quiz_attempts_report_acs.php';
require_once __DIR__.'/../../../lib/modinfolib.php';
require_once __DIR__.'/../../../mod/quiz/classes/quiz_settings.php';
require_once __DIR__.'/../../../mod/quiz/report/reportlib.php';
require_once __DIR__.'/../../../mod/quiz/report/statistics/report.php';

class deploy_question_process
{
    use variables_trait;

    public function perform(int $summaryid)
    {
        global $DB;

        // Find stats: PRE_DEPLOY
        $summary = $DB->get_record(self::TABLE_SUMMARY, ['id' => $summaryid, 'status' => self::STATUS_PRE_DEPLOY]);
        if ($summary === false) {
            return false;
        }

        $cmids = json_decode($summary->coursemodules);

        foreach ($cmids as $cmid) {
            mtrace('Processing Course Module ID: ' . $cmid);

            $course = get_course($summary->course);
            $cm   = $DB->get_record('course_modules', ['id' => $cmid]);
            $quiz = $DB->get_record('quiz', ['id' => $cm->instance]);

            // Dont remove this 2 lines used in exec result internally 
            $quizgrades = quiz_get_user_grades($quiz);
            $questions = quiz_report_get_significant_questions($quiz);

            $quizobj = \mod_quiz\quiz_settings::create($quiz->id);
            $structure = \mod_quiz\structure::create_for_quiz($quizobj);
            $slots = $structure->get_slots();

            $questionreport = new \quiz_attempts_report_acs();
            $questiongrades = $questionreport->get_report($quiz, $cm, $course);

            // Add Question Record
            foreach ($slots as $slot) {
                $questiongradelist = [];
                foreach ($questiongrades as $questiongrade) {
                    foreach ($questiongrade as $key => $value) {
                        if ($key === 'qsgrade'.$slot->slot) {
                            $questiongradelist[] = sprintf('%.2f', $value);
                        }
                    }
                }

                $entry = [
                    'coursesummary'  => $summary->id,
                    'course'         => $summary->course,
                    'coursemodule'   => $cmid,
                    'question'       => $slot->questionid,
                    'questiontext'   => $slot->questiontext,
                    'questionprompt' => $this->build_prompt($summary, $slot->questiontext), 
                    'scoredistributionquery' => json_encode($questiongradelist),
                    'datecreated'    => time(),
                ];

                $params = [
                    'coursesummary' => $summary->id,
                    'course'        => $summary->course,
                    'coursemodule'  => $cmid,
                    'question'      => $slot->questionid,
                ];

                $exists = $DB->count_records(self::TABLE_QUESTION, $params);
                if (! $exists) {
                    $DB->insert_record(self::TABLE_QUESTION, $entry);
                }
            }

            $DB->update_record(self::TABLE_SUMMARY, ['id' => $summary->id, 'status' => self::STATUS_QUESTION_DEPLOYED]);
        }

        return true;
    }

    public function build_prompt($summary, string $text)
    {
        $prompt = '';
        $prompt .= $summary->questionpromptintro;
        $prompt .= PHP_EOL;
        $prompt .= $text;
    
        return $prompt;
    }
}