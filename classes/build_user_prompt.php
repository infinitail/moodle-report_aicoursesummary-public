<?php

namespace report_aicoursesummary;

require_once __DIR__.'/variables_trait.php';

class build_user_prompt 
{
    use variables_trait;

    public function perform()
    {
        global $DB;
        
        $summary = $DB->get_record(self::TABLE_SUMMARY, ['status' => self::STATUS_USER_QUERY_DEPLOYING], '*', IGNORE_MULTIPLE);
        if ($summary === false) {
            return false;
        }

        $questions = $DB->get_records(self::TABLE_QUESTION, ['coursesummary' => $summary->id]);
        $users = $DB->get_records(self::TABLE_USER, ['coursesummary' => $summary->id]);

        $counter = 0;
        foreach ($users as $key => $user) {
            $counter += 1;
            
            mtrace('Building user prompt ID: ' . $counter . ' / ' . count($users));

            $qscores = json_decode($user->aiquery, true);
            $prompt = self::build_qprompt($summary, $qscores, $questions);

            $DB->update_record(self::TABLE_USER, ['id' => $user->id, 'aiprompt' =>$prompt]);
        }

        $DB->update_record(self::TABLE_SUMMARY, ['id' => $summary->id, 'status' => self::STATUS_USER_QUERYING]);
        
        return true;
    }

    public function build_qprompt($summary, array $qscores, array $questions)
    {
        global $DB;

        $prompt = '';

        $prompt .= $summary->userpromptintro;
        $prompt .= PHP_EOL;

        $qcounter = 0;
        $lastcmid = 0;
        foreach ($questions as $question) {
            if ($lastcmid !== $question->coursemodule) {
                $lastcmid = $question->coursemodule;
                
                $cm = $DB->get_record('course_modules', ['id' => $lastcmid]);
                $quiz = $DB->get_record('quiz', ['id' => $cm->instance]);
                $qcounter = 0;
            }
            
            $qcounter++;
            
            if (! empty($qscores[$question->coursemodule])) {
                $qscore = $qscores[$question->coursemodule]['qsgrade'.$qcounter];
                $qscore = sprintf('%.2f', $qscore);
            } else {
                $qscore = '-';
            }

            $params = new \stdClass;
            $params->qname             = $quiz->name;
            $params->qcounter          = $qcounter;
            $params->aiqresponse       = $question->questionunveil;
            $params->scoredistribution = $question->scoredistributionquery;
            $params->qscore            = $qscore;

            $loopprompt = $summary->userpromptloop;

            $a = (array) $params;
            $search = array();
            $replace = array();
            foreach ($a as $key => $value) {
                $search[] = '{$a->' . $key . '}';
                $replace[] = (string) $value;
            }
            if ($search) {
                $loopprompt = str_replace($search, $replace, $loopprompt);
            }

            $prompt .= $loopprompt;
            $prompt .= PHP_EOL;
            $prompt .= PHP_EOL;
        }

        return $prompt;
    }
}
