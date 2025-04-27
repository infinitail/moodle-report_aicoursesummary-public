<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version info
 *
 * @package    report
 * @subpackage aicoursesummary
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version    = 2024102722;
$plugin->requires   = 2024100700;               // min version 4.5.0
$plugin->component  = 'report_aicoursesummary';
$plugin->maturity   = MATURITY_ALPHA;
$plugin->release    = 'alpha-version';

$plugin->dependencies = [
    //'aiprovider_azureai' => 2024100700,
    'aiprovider_openai'  => 2024100700,
];
