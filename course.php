<?php

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/lib.php';
require_once __DIR__.'/classes/course_quiz.php';
require_once __DIR__.'/classes/summary.php';

//use report_aicoursesummary\summary;

$courseid = required_param('id', PARAM_INT);

// Check capability
require_course_login($courseid);
$course = get_course($courseid);
$coursecontext = context_course::instance($course->id);
require_capability('report/aicoursesummary:viewall', $coursecontext);

// Check AI instance is configured
$provider = new \aiprovider_openai\provider;
if (! $provider->is_provider_configured()) {
    throw new moodle_exception('providernotconfigured', 'report_aicoursesummary',
        null, 'OpenAI', 'You should setup AI provider plugin module.');
}

$summary_class = new \report_aicoursesummary\summary;

$course_summaries = $summary_class->get_course_summary_list($course->id);

$html = '';

// Generate breadcrumbs
$html .= '<p>';
$html .= '<ol class="breadcrumb">';
$html .= '<li class="breadcrumb-item">';
$html .= html_writer::tag('span', $course->fullname);
$html .= '</li>';
$html .= '</ol>';
$html .= '</p>';

$html .= html_writer::tag('h1', get_string('reports'));

// Add config page link
if (has_capability('report/aicoursesummary:manage', $coursecontext)) {
    $settinglink = new moodle_url("/report/aicoursesummary/select_quizzes.php", ['id' => $courseid]);
    $html .= '<p style="text-align:right;">';
    $html .= '<a class="btn btn-primary" href="'.$settinglink.'">'.get_string('createnewsummary', 'report_aicoursesummary').'</a>';
    $html .= '</p>';
}

if ($course_summaries !== false) {
    // Build table
    $html .= '<table class="generaltable boxaligncenter" width="90%" cellspacing="1" cellpadding="5">';
    $html .= '<tr>';
    $html .= '<th class="header" scope="col">' . get_string('reportid', 'report_aicoursesummary') . '</th>';
    $html .= '<th class="header" scope="col">' . get_string('coursemodules', 'report_aicoursesummary') . '</th>';
    $html .= '<th class="header" scope="col">' . get_string('status') . '</th>';
    $html .= '<th class="header" scope="col">' . get_string('datecreated', 'report_aicoursesummary') . '</th>';
    $html .= '<th class="header" scope="col">' . get_string('action') . '</th>';
    $html .= '</tr>';

    ///    $html .= html_writer::tag('td', html_writer::link(new moodle_url("/report/aicoursesummary/quiz.php?cmid={$quiz->cmid}"), $quiz->name));

    foreach ($course_summaries as $course_summary) {
        $coursemodules = $course_summary->coursemodules;
        $coursemodules = json_decode($coursemodules);
        if (is_array($coursemodules)) {
            sort($coursemodules);
        }
        $coursemodules = json_encode($coursemodules);

        $status = sprintf('Phase: '.'%d/%d - %s %s',
            $summary_class->get_phase_id($course_summary->status),
            $summary_class->get_max_phase_id(),
            $summary_class->get_status_name($course_summary->status),
            $summary_class->get_progress($course_summary->id)
        );
        
        $html .= html_writer::start_tag('tr');
        $html .= html_writer::tag('td', $course_summary->id);
        $html .= html_writer::tag('td', $coursemodules);
        $html .= html_writer::tag('td', $status);
        $html .= html_writer::tag('td', userdate($course_summary->datecreated, get_string('strftimedatetimeshort', 'core_langconfig')));
        $html .= html_writer::tag('td', html_writer::link(new moodle_url("/report/aicoursesummary/summary.php?course={$course->id}&summary={$course_summary->id}"), 'View'));
        $html .= html_writer::end_tag('tr');
    }

    $html .= html_writer::end_tag('table');
} else {
    $html .= html_writer::tag('h2', get_string('nosummaryfound'));
}

// Start page output
$PAGE->set_url(new moodle_url('/report/aicoursesummary/course.php', ['id' => $course->id]));
$PAGE->set_title(get_string('pluginname', 'report_aicoursesummary'));
$PAGE->set_heading(get_string('pluginname', 'report_aicoursesummary'));
//$PAGE->navigation->override_active_url(new moodle_url('/report/view.php', ['courseid' => $courseid]));

echo $OUTPUT->header();
//\core\report_helper::print_report_selector(get_string('pluginname', 'report_aicoursesummary'));
echo $html;
echo $OUTPUT->footer();
