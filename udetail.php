<?php

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/lib.php';
require_once __DIR__.'/classes/course_quiz.php';

$courseid  = required_param('course', PARAM_INT);
$summaryid = required_param('summary', PARAM_INT);
$userid    = required_param('user', PARAM_INT);

// Check capability
require_course_login($courseid);
$course = get_course($courseid);
$coursecontext = context_course::instance($course->id);

$user = $DB->get_record('user', ['id' => $userid]);
$userfullname = core_user::get_fullname($user);
require_capability('report/aicoursesummary:viewall', $coursecontext);

global $CFG, $DB;

$query = $DB->get_records('report_aicoursesummary_users', ['coursesummary' => $summaryid, 'user' => $userid]);

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
$html .= html_writer::tag('span', "User prompt: {$userfullname}");
$html .= '</li>';
$html .= '</ol>';
$html .= '</p>';

foreach ($query as $value) {
    // Already processed?
    if ($value->responseid) {
        $transaction = $DB->get_record('ai_action_generate_text', ['responseid' => $value->responseid]);
        $prompt           = $transaction->prompt;
        $generatedcontent = $transaction->generatedcontent;
    } else if ($value->aiprompt) {
        $prompt           = $value->aiprompt;
        $generatedcontent = '-';
    } else {
        $prompt           = '&lt;undefined&gt;';
        $generatedcontent = '-';
    }
    
    $html .= '<table class="generaltable boxaligncenter" name="table" width="90%" cellspacing="1" cellpadding="5" style="word-break:break-word;">';
    $html .= '<tbody>';
    $html .= '<tr>';
    $html .= '<th>プロンプト</th>';
    $html .= '<td>' . nl2br($prompt) . '</td>';
    $html .= '</tr>';
    $html .= '<tr>';
    $html .= '<th>レスポンス</th>';
    $html .= '<td>' . nl2br($generatedcontent) . '</td>';
    $html .= '</tr>';
    $html .= '</tbody>';
    $html .= '</table>';
}

// Start page output
$PAGE->set_pagelayout('report');
$PAGE->set_url(new moodle_url('/report/aicoursesummary/udetail.php', ['course' => $course->id, 'summary' => $summaryid, 'user' => $userid]));
$PAGE->set_title(get_string('pluginname', 'report_aicoursesummary'));
$PAGE->set_heading(get_string('pluginname', 'report_aicoursesummary') . ' - ' . get_string('userprompt', 'report_aicoursesummary'));
//$PAGE->navigation->override_active_url(new moodle_url('/report/view.php', ['course' => $course->id]));

//$PAGE->navigation->extend_for_user($user);
/*
$navigationnode = array(
    'url' => new moodle_url('/report/outline/user.php', array('id' => $user->id, 'course' => $course->id, 'mode' => 'complete'))
);
$PAGE->add_report_nodes($user->id, $navigationnode);
*/

echo $OUTPUT->header();
//\core\report_helper::print_report_selector(get_string('pluginname', 'report_aicoursesummary'));
echo $html;
echo $OUTPUT->footer();
