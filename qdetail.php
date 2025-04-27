<?php

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/lib.php';
require_once __DIR__.'/classes/course_quiz.php';

$courseid  = required_param('course', PARAM_INT);
$summaryid = required_param('summary', PARAM_INT);
$cmid      = required_param('cm', PARAM_INT);

// Check capability
require_course_login($courseid);
$course = get_course($courseid);
$coursecontext = context_course::instance($course->id);
require_capability('report/aicoursesummary:viewall', $coursecontext);

global $CFG, $DB;

$cm   = $DB->get_record('course_modules', ['id' => $cmid, 'course' => $courseid]);
$quiz = $DB->get_record('quiz', ['id' => $cm->instance]);
$questions = $DB->get_records('report_aicoursesummary_questions', ['course' => $course->id, 'coursesummary' => $summaryid, 'coursemodule' => $cmid]);

$html = '';

// Generate breadcrumbs
$html .= '<p>';
$html .= '<ol class="breadcrumb">';
$html .= '<li class="breadcrumb-item">';
$html .= html_writer::tag('a', $course->fullname, ['href' => new moodle_url('/report/aicoursesummary/course.php', ['id' => $course->id])]);
$html .= '</li>';
$html .= '<li class="breadcrumb-item">';
$html .= html_writer::tag('a', "Report ID: {$summaryid}", ['href' => new moodle_url('/report/aicoursesummary/summary.php', ['course' => $course->id, 'summary' => $summaryid])]);
$html .= '</li>';
$html .= '<li class="breadcrumb-item">';
$html .= html_writer::tag('span', "Question prompt: {$quiz->name}");
$html .= '</li>';
$html .= '</ol>';
$html .= '</p>';

$qcounter = 0;
foreach ($questions as $question) {
    $qcounter++;
 
    // Already processed?
    if ($question->qresponseid) {
        $transaction = $DB->get_record('ai_action_generate_text', ['responseid' => $question->qresponseid]);
        $prompt           = $transaction->prompt;
        $generatedcontent = $transaction->generatedcontent;
    } else {
        $prompt           = $question->questionprompt;
        $generatedcontent = '-';
    }

    $html .= '<h2>' . $qcounter . '</h2>';
    $html .= '<table class="generaltable boxaligncenter" name="table" width="90%" cellspacing="1" cellpadding="5">';
    $html .= '<tbody>';
    $html .= '<tr>';
    $html .= '<th>問題文</th>';
    $html .= '<td>' . $prompt . '</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<th>要素</th>';
    $html .= '<td>' . @nl2br($generatedcontent) . '</td>';
    $html .= '</tr>';
    $html .= '</tbody>';
    $html .= '</table>';
}

// Start page output
$PAGE->set_pagelayout('report');
$PAGE->set_url(new moodle_url('/report/aicoursesummary/qdetail.php', ['course' => $course->id, 'summary' => $summaryid, 'cm' => $cmid]));
$PAGE->set_title(get_string('pluginname', 'report_aicoursesummary'));
$PAGE->set_heading(get_string('pluginname', 'report_aicoursesummary') . ' - ' . get_string('questionprompt', 'report_aicoursesummary'));
//$PAGE->navigation->override_active_url(new moodle_url('/report/view.php', ['courseid' => $course->id]));

echo $OUTPUT->header();
//\core\report_helper::print_report_selector(get_string('pluginname', 'report_aicoursesummary'));
echo $html;
echo $OUTPUT->footer();
