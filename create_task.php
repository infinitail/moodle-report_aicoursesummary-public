<?php

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/lib.php';
require_once __DIR__.'/../../lib/modinfolib.php';
require_once __DIR__.'/../../mod/quiz/classes/quiz_settings.php';
require_once __DIR__.'/../../mod/quiz/report/statistics/report.php';
require_once __DIR__.'/lib/quiz_attempts_report_acs.php';

$courseid = required_param('id', PARAM_INT);

// Check capability
require_course_login($courseid);
$course = get_course($courseid);
$coursecontext = context_course::instance($course->id);
require_capability('report/aicoursesummary:manage', $coursecontext);

// Get target quiz list
$cmidbase64 = required_param('cmidbase64', PARAM_RAW);
$cmids = json_decode(base64_decode($cmidbase64));
// TODO: Add cmids access privilege check
// TODO: Add cmid type check

$questionpromptintro = required_param('questionpromptintro', PARAM_RAW);
$userpromptintro     = required_param('userpromptintro', PARAM_RAW);
$userpromptloop      = required_param('userpromptloop', PARAM_RAW);

global $DB, $USER;

$summaries = $DB->get_records('report_aicoursesummary_summaries', ['course' => $courseid]);

$html = '';

if (count($summaries) > 0) {
    $inprogress = false;
    foreach ($summaries as $summary) {
        if ($summary->status < 90) {     // "status >= 90" は完了、放棄、キャンセルなどで処理対象外であることを意味。
            $inprogress = true;
        }
    }

    if ($inprogress) {
        $html .= html_writer::tag('p', get_string('anotherprocessexist', 'report_aicoursesummary'));
        $html .= html_writer::tag('a', get_string('back'), ['href' => new moodle_url('/report/aicoursesummary/course.php', ['id' => $summary->course]), 'class' => 'btn btn-secondary']);
        goto OUTPUT;
    }
}

// DBに登録
//sort($cmids);
$params = [
    'course'              => $courseid,
    'coursemodules'       => json_encode($cmids),
    'status'              => 1,   // PRE_DEPLOY
    'owner'               => $USER->id,
    'questionpromptintro' => $questionpromptintro,
    'userpromptintro'     => $userpromptintro,
    'userpromptloop'      => $userpromptloop,
    'datecreated'         => time(),
];

if (! $summaryid = $DB->insert_record('report_aicoursesummary_summaries', $params)) {
    $html .= '<p>DB record insert error!</p>';
    goto OUTPUT;
}

$html .= html_writer::tag('p', get_string('reporttaskcreated', 'report_aicoursesummary'));
$html .= html_writer::tag('a', get_string('back'), ['href' => new moodle_url('/report/aicoursesummary/course.php', ['id' => $courseid]), 'class' => 'btn btn-primary']);

// Deploy in background task
$deploytask = new report_aicoursesummary\task\deploy;
$deploytask->set_custom_data(['id' => $summaryid]);
\core\task\manager::queue_adhoc_task($deploytask);

// Start page output
OUTPUT:
$PAGE->set_url(new moodle_url('/report/aicoursesummary/course_setting.php', ['id' => $course->id]));
$PAGE->set_title(get_string('pluginname', 'report_aicoursesummary'));
$PAGE->set_heading(get_string('pluginname', 'report_aicoursesummary'));
$PAGE->navigation->override_active_url(new moodle_url('/report/view.php', ['courseid' => $courseid]));

echo $OUTPUT->header();
//\core\report_helper::print_report_selector(get_string('pluginname', 'report_aicoursesummary'));
echo $html;
echo $OUTPUT->footer();
