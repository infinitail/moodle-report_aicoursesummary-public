<?php

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/lib.php';
require_once __DIR__.'/classes/course_quiz.php';

$courseid = required_param('course', PARAM_INT);
$summaryid = required_param('summary', PARAM_INT);

// Check capability
require_course_login($courseid);
$course = get_course($courseid);
$coursecontext = context_course::instance($course->id);
require_capability('report/aicoursesummary:viewall', $coursecontext);

global $CFG, $DB;

//echo $html;

$html = '';

// Generate breadcrumbs
$html .= '<p>';
$html .= '<ol class="breadcrumb">';
$html .= '<li class="breadcrumb-item">';
$html .= html_writer::tag('a', $course->fullname, ['href' => new moodle_url('/report/aicoursesummary/course.php', ['id' => $course->id])]);
$html .= '</li>';
$html .= '<li class="breadcrumb-item">';
$html .= html_writer::tag('span', "Report ID: {$summaryid}");
$html .= '</li>';
$html .= '</ol>';
$html .= '</p>';

$course_summary = $DB->get_record('report_aicoursesummary_summaries', ['id'=> $summaryid, 'course' => $course->id]);

if ($course_summary === false) {
    die('Invalid summary');
}

$cmids = json_decode($course_summary->coursemodules);

$html .= html_writer::tag('h2', get_string('modulename', 'mod_quiz'));
$html .= '<table class="generaltable boxaligncenter" width="90%" cellspacing="1" cellpadding="5">';
$html .= '<tr>';
$html .= '<th class="header" scope="col">' . get_string('moduleid', 'report_aicoursesummary') . '</th>';
$html .= '<th class="header" scope="col">' . get_string('qname', 'quiz') . '</th>';
$html .= '<th class="header" scope="col">' . get_string('details') . '</th>';
$html .= '</tr>';

foreach ($cmids as $cmid) {
    //echo '<pre>'; var_dump($cmid); echo '</pre>'; 
    $cm = $DB->get_record('course_modules', ['id' => $cmid, 'course' => $course->id]);
    $quiz = $DB->get_record('quiz', ['id' => $cm->instance]);
    //echo '<pre>'; var_dump($quiz->name); echo '</pre>';
    
    $html .= '<tr>';
    $html .= '<td>' . $cm->id . '</td>';
    $html .= '<td><a href="' . new moodle_url('/mod/quiz/view.php', ['id' => $cmid]) . '">' . $quiz->name . '</a></td>';
    $html .= '<td><a href="' . new moodle_url('/report/aicoursesummary/qdetail.php', ['summary' => $summaryid, 'course' => $course->id, 'cm' => $cmid]) .'">Link</a></td>';
    $html .= '</tr>';
}
$html .= '</table>';


$html .= html_writer::tag('h2', get_string('summary'));
$html .= '<table class="generaltable boxaligncenter" width="90%" cellspacing="1" cellpadding="5">';

$userqueries = $DB->get_records('report_aicoursesummary_users', ['coursesummary' => $summaryid]);
foreach ($userqueries as $userquery) {
    //echo '<pre>'; var_dump($userquery->user); echo '</pre>';
    $user = $DB->get_record('user', ['id' => $userquery->user]);

    $fullname = strip_tags(core_user::get_fullname($user));
    //echo '<pre>'; var_dump($fullname); echo '</pre>';

    $html .= '<tr>';
    $html .= '<td>'
        . $fullname
        . '<a href="' . new moodle_url('/user/view.php',['id' => $user->id, 'course' => $course->id]) . '" style="margin-left:1em;display:inline-block;">['.get_string('grades').']</a>'
        . '<a href="' . new moodle_url('/report/outline/user.php',['mode' => 'complete', 'id' => $user->id, 'course' => $course->id]) . '" style="margin-left:1em;display:inline-block;">['.get_string('completereport').']</a>'
        . '<a href="' . new moodle_url('/report/aicoursesummary/udetail.php',['user' => $user->id, 'course' => $course->id, 'summary' => $summaryid]) . '" style="margin-left:1em;display:inline-block;">['.get_string('llmquery', 'report_aicoursesummary').']</a>'
        . '</td>';
    $html .= '</tr>';

    $html .= '<tr>';
    $html .= '<td>' . $userquery->airesponse . '</td>';
    //echo '<pre>'; var_dump($userquery->airesponse); echo '</pre>';
    $html .= '</tr>';
}

$html .= '</table>';


// Start page output
$PAGE->set_url(new moodle_url('/report/aicoursesummary/summary.php', ['course' => $course->id, 'summary' => $summaryid]));
$PAGE->set_title(get_string('pluginname', 'report_aicoursesummary'));
$PAGE->set_heading(get_string('pluginname', 'report_aicoursesummary'));
//$PAGE->navigation->override_active_url(new moodle_url('/report/view.php', ['courseid' => $course->id]));

echo $OUTPUT->header();
//\core\report_helper::print_report_selector(get_string('pluginname', 'report_aicoursesummary'));
echo $html;
echo $OUTPUT->footer();
