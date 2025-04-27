<?php

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_aicoursesummary_extend_navigation_course($navigation, $course, $context) {
    //if (has_capability('report/aicoursesummary:viewall', $context)) {
        $url = new moodle_url('/report/aicoursesummary/course.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_aicoursesummary'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    //}
}

/**
 * This function extends the course navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $user
 * @param stdClass $course The course to object for the report
 */
function report_aicoursesummary_extend_navigation_user($navigation, $user, $course) {
    list($all, $today) = report_log_can_access_user_report($user, $course);

    if ($today) {
        $url = new moodle_url('/report/aicoursesummary/user.php', array('id'=>$user->id, 'course'=>$course->id, 'mode'=>'today'));
        $navigation->add(get_string('todaylogs'), $url);
    }
    if ($all) {
        $url = new moodle_url('/report/aicoursesummary/user.php', array('id'=>$user->id, 'course'=>$course->id, 'mode'=>'all'));
        $navigation->add(get_string('alllogs'), $url);
    }
}

/**
 * This function extends the module navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $cm
 */
function report_aicoursesummary_extend_navigation_module($navigation, $cm) {
    if (has_capability('report/aicoursesummary:viewall', context_course::instance($cm->course))) {
        $url = new moodle_url('/report/aicoursesummary/index.php', array('chooselog'=>'1','id'=>$cm->course,'modid'=>$cm->id));
        $navigation->add(get_string('aicoursesummary'), $url, navigation_node::TYPE_SETTING, null, 'logreport', new pix_icon('i/report', ''));
    }
}


/**
 * Callback to verify if the given instance of store is supported by this report or not.
 *
 * @param string $instance store instance.
 *
 * @return bool returns true if the store is supported by the report, false otherwise.
 */
function report_aicoursesummary_supports_logstore($instance) {
    return false;
}
