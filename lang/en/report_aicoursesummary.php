<?php
$string['courseoverview:view'] = 'View course overview report';
$string['pluginname'] = 'AI Course Summary';
$string['privacy:metadata'] = 'The Course overview plugin does not store any personal data.';
$string['providernotconfigured'] = 'AI provider {$a} is not configured.';
$string['reporttarget'] = 'Report Target';
$string['deploytask'] = 'Deploy Task';
$string['querytask'] = 'Query Task';
$string['selecttarget'] = 'Select report target';
$string['reportid'] = 'Report ID';
$string['createnewsummary'] = 'Create new summary';
$string['datecreated'] = 'Creation date';
$string['coursemodules'] = 'Course modules';
$string['moduleid'] = 'Module ID';
$string['llmquery'] = 'LLM Query';
$string['questionprompt'] = 'Question prompt';
$string['userprompt'] = 'User prompt';
$string['anotherprocessexist'] = 'Another process is currently running on the same course. Please wait until it finishes before creating a new report.';
$string['reporttaskcreated'] = 'The task for the new summary report has been successfully created. Please wait until the report is generated.';
$string['statuspredeploy'] = 'Pre deploy';
$string['statusquestiondeployed'] = 'Question deployed';
$string['statususerdeployed'] = 'User deployed';
$string['statusquestionquerying'] = 'Question querying';
$string['statusconfirmwaiting'] = 'Confirmation waiting';
$string['statususerquerydeploying'] = 'User query deploying';
$string['statususerquerying'] = 'User querying';
$string['statusquerycompleted'] = 'Query completed';
$string['statuspublished'] = 'Published';
$string['statuscancelled'] = 'Cancelled';
$string['statusaborted'] = 'Aborted';
$string['questionprompt'] = 'Question Prompt';
$string['introduction'] = 'Introduction';
$string['questionpromptintrodefaultvalue'] = 'Please list the main types of knowledge required to solve the following question.'
    .'Provide a simple list of the key concepts or areas needed.';
$string['studentprompt'] = 'Student Prompt';
$string['studentpromptintrodefaultvalue'] = 'Based on the quiz performance of the following student in class, please write a feedback comment of approximately 500 characters directed toward the student.'
    ." Make sure to address the student\'s strengths and weaknesses in a way that encourages future improvement."
    .' When referring to the student, always use the second person (e.g., "you").'
    .' Note that all scores are expressed as a score ratio between 0 and 1. A score of "-" indicates that the student did not take the quiz.'
    .' In addition to the raw score, consider how the student performed in comparison to their peers.';
$string['loop'] = 'Loop';
$string['studentpromptloopdefaultvalue'] = 'This is Question {$a->qcounter} of {$a->qname}. '
    .' This question assesses your understanding of the following: '.PHP_EOL
    .'{$a->aiqresponse}'.PHP_EOL
    .'The average score for all students in this class was {$a->scoredistribution}.'.PHP_EOL
    .'This student’s score was {$a->qscore}.';
