<?php

require_once __DIR__.'/../../config.php';

$courseid = required_param('id', PARAM_INT);
$cmids = required_param_array('targetcmid', PARAM_INT);

// Check capability
require_course_login($courseid);
$course = get_course($courseid);
$coursecontext = context_course::instance($course->id);
require_capability('report/aicoursesummary:manage', $coursecontext);

$cmidjson = json_encode($cmids);
$cmidbase64 = base64_encode($cmidjson);

$html = '';

// Generate breadcrumbs
$html .= '<p>';
$html .= '<ol class="breadcrumb">';
$html .= '<li class="breadcrumb-item">';
$html .= html_writer::tag('a', $course->fullname, ['href' => new moodle_url('/report/aicoursesummary/course.php', ['id' => $course->id])]);
$html .= '</li>';
$html .= '<li class="breadcrumb-item">';
$html .= html_writer::tag('span', "Create new summary");
$html .= '</li>';
$html .= '</ol>';
$html .= '</p>';

$settinglink = new moodle_url('/report/aicoursesummary/create_task.php', ['id' => $courseid]);
$html .= '<form method="POST" action="'.$settinglink.'">';
$html .= '<input type="hidden" name="id" value="'.$courseid.'">';
$html .= '<input type="hidden" name="cmidbase64" value="'.$cmidbase64.'">';

$html .= html_writer::tag('h2', get_string('questionprompt', 'report_aicoursesummary'));
$html .= html_writer::tag('h3', get_string('introduction', 'report_aicoursesummary'));
$html .= '<p>';
$html .= '<textarea name="questionpromptintro" rows="8" cols="80">'
    .get_string('questionpromptintrodefaultvalue', 'report_aicoursesummary')
    .'</textarea>';
$html .= '</p>';

$html .= html_writer::tag('h2', get_string('studentprompt', 'report_aicoursesummary'));
$html .= html_writer::tag('h3', get_string('introduction', 'report_aicoursesummary'));
$html .= '<p>';
$html .= '<textarea name="userpromptintro" rows="8" cols="80">'
    .get_string('studentpromptintrodefaultvalue', 'report_aicoursesummary')
    .'</textarea>';
$html .= '</p>';
$html .= html_writer::tag('h3', get_string('loop', 'report_aicoursesummary'));
$html .= '<p>';
$html .= '<textarea name="userpromptloop" rows="8" cols="80">'
    .get_string('studentpromptloopdefaultvalue', 'report_aicoursesummary')
    .'</textarea>';
$html .= '</p>';

$html .= '<p>';
$html .= '<a class="btn btn-secondary" href="'.new moodle_url('/report/aicoursesummary/course.php', ['id' => $course->id]).'">'.get_string('cancel').'</a> ';
$html .= '<button class="btn btn-primary" href="'.$settinglink.'">'.get_string('create').'</button>';
$html .= '</p>';
$html .= '</form>';

// Start page output
$PAGE->set_url(new moodle_url('/report/aicoursesummary/summary_setting.php', ['id' => $course->id]));
$PAGE->set_title(get_string('pluginname', 'report_aicoursesummary'));
$PAGE->set_heading(get_string('pluginname', 'report_aicoursesummary'));
//$PAGE->navigation->override_active_url(new moodle_url('/report/view.php', ['courseid' => $courseid]));

echo $OUTPUT->header();
//\core\report_helper::print_report_selector(get_string('pluginname', 'report_aicoursesummary'));
echo $html;
echo $OUTPUT->footer();