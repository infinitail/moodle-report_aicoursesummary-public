<?php

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/lib.php';
require_once __DIR__.'/classes/course_quiz.php';

$courseid = required_param('id', PARAM_INT);

// Check capability
require_course_login($courseid);
$course = get_course($courseid);
$coursecontext = context_course::instance($course->id);
require_capability('report/aicoursesummary:manage', $coursecontext);

// Check AI instance is configured
/*
$provider = new \aiprovider_openai\provider;
if (! $provider->is_provider_configured()) {
    throw new moodle_exception('providernotconfigured', 'report_aicoursesummary',
        null, 'OpenAI', 'You should setup AI provider plugin module.');
}
*/

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

$html .= html_writer::tag('h1', get_string('selecttarget', 'report_aicoursesummary'));

// Add config page link
$settinglink = new moodle_url("/report/aicoursesummary/summary_setting.php", ['id' => $courseid]);
$html .= '<form method="POST" action="'.$settinglink.'">';
$html .= '<input type="hidden" name="id" value="'.$courseid.'">';
$html .= '<p style="text-align:right;">';
$html .= '<button class="btn btn-primary" href="'.$settinglink.'">'.get_string('next').'</button>';
$html .= '</p>';

// Get quiz list
$quizzes = \report\aicoursesummary\course_quiz::get_list($course->id);
//echo '<pre>'; var_dump($quizzes); echo '</pre>'; 

// Build table
$html .= '<table class="generaltable boxaligncenter" name="table" width="90%" cellspacing="1" cellpadding="5">';
$html .= '<tr>';
$html .= '<th class="header" scope="col">'
    . html_writer::checkbox('changeall', '', true, '', ['id' => 'change-all'])
    .' '
    . get_string('reporttarget', 'report_aicoursesummary')
    . '</th>';
$html .= '<th class="header" scope="col">' . get_string('pluginname', 'quiz') . '</th>';
$html .= '<th class="header" scope="col">' . get_string('questions', 'quiz') . '</th>';
$html .= '<th class="header" scope="col">' . get_string('numberofgrades', 'grades') . '</th>';
$html .= '</tr>';

foreach ($quizzes as $quiz) {
    $html .= html_writer::start_tag('tr');
    $html .= html_writer::tag('td', html_writer::checkbox('targetcmid[]', $quiz->cmid));
    $html .= html_writer::tag('td', html_writer::link(new moodle_url("/report/aicoursesummary/quiz.php?cmid={$quiz->cmid}"), $quiz->name));
    $html .= html_writer::tag('td', $quiz->questions);
    $html .= html_writer::tag('td', $quiz->numberofgrades);
    $html .= html_writer::end_tag('tr');
}

$html .= html_writer::end_tag('table');
$html .= '</form>';

// Start page output
$PAGE->set_url(new moodle_url('/report/aiocoursesummary/select_quizzes.php', ['id' => $course->id]));
$PAGE->set_title(get_string('pluginname', 'report_aicoursesummary'));
$PAGE->set_heading(get_string('pluginname', 'report_aicoursesummary'));
//$PAGE->navigation->override_active_url(new moodle_url('/report/view.php', ['courseid' => $courseid]));

echo $OUTPUT->header();

//\core\report_helper::print_report_selector(get_string('pluginname', 'report_aicoursesummary'));

echo $html;

echo <<< __SCRIPT__
<script>
    const changeall = document.getElementById('change-all');
    changeall.addEventListener('click', (event) => {
        checkboxes = document.getElementsByName('targetcmid[]');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = changeall.checked;
        });
    });
</script>
__SCRIPT__;

echo $OUTPUT->footer();
