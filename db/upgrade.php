<?php

function xmldb_report_aicoursesummary_upgrade($oldversion): bool {
    global $CFG, $DB;
    
    //$table = 'report_aicoursesummary_courses';

    $dbman = $DB->get_manager(); 

    if ($oldversion < 2024102717) {
        // https://moodledev.io/docs/4.5/apis/commonfiles#dbupgradephp
        $table = new xmldb_table('report_aicoursesummary_courses');
        $field = new xmldb_field('owner');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, 'status');
        $dbman->add_field($table, $field);

        $table = new xmldb_table('report_aicoursesummary_queries');
        $field = new xmldb_field('aiprompt');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, null, null, null, 'aiquery');
        $dbman->add_field($table, $field);

        $table = new xmldb_table('report_aicoursesummary_questions');
        $field = new xmldb_field('questionprompt');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, null, null, null, 'questiontext');
        $dbman->add_field($table, $field);
        
        //$field = new xmldb_field('qresponseid');
        //$field->set_attributes(XMLDB_TYPE_CHAR, 128, null, XMLDB_INDEX_NOTUNIQUE, null, null, 'questionunveil');
        //$dbman->add_field($table, $field);
        //$field = new xmldb_field('dateresponded');
        //$field->set_attributes(XMLDB_TYPE_INTEGER, 10, null, null, null, null, 'datecreated');
        //$dbman->add_field($table, $field);
    }
    
    if ($oldversion < 2024102721) {
        $table = new xmldb_table('report_aicoursesummary_courses');
        $field = new xmldb_field('questionpromptintro');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, null, null, null, 'owner');
        $dbman->add_field($table, $field);
        
        $table = new xmldb_table('report_aicoursesummary_courses');
        $field = new xmldb_field('userpromptintro');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, null, null, null, 'questionpromptintro');
        $dbman->add_field($table, $field);

        $table = new xmldb_table('report_aicoursesummary_courses');
        $field = new xmldb_field('userpromptloop');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, null, null, null, 'userpromptintro');
        $dbman->add_field($table, $field);
    }
    
    if ($oldversion < 2024102722) {
        // Rename table name
        $dbman->rename_table(new xmldb_table('report_aicoursesummary_courses'), 'report_aicoursesummary_summaries');
        $dbman->rename_table(new xmldb_table('report_aicoursesummary_queries'), 'report_aicoursesummary_users');
    }

    return true;
}
