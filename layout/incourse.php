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
 * Moodle's Clean theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_cliniciansacademy
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $SESSION, $CFG;

$incourse = true;

// Get the HTML for the settings bits.
$html = theme_cliniciansacademy_get_html_for_settings($OUTPUT, $PAGE);

// Set default (LTR) layout mark-up for a three column page.
$regionmainbox = 'span9';
$regionmain = 'span8 pull-right';
$sidepre = 'span4 desktop-first-column';
$sidepost = 'span3 pull-right';
// Reset layout mark-up for RTL languages.
if (right_to_left()) {
    $regionmainbox = 'span9 pull-right';
    $regionmain = 'span8';
    $sidepre = 'span4 pull-right';
    $sidepost = 'span3 desktop-first-column';
}

$extracss = array();
$extracss[] = is_siteadmin() ? "is-site-admin" : "";

// tim: we are injecting closed=true onto the Save & Exit button for scorm.
// we want to let scorm close then come back to this course homepage, but then redirect back to the catalogue/portal page.
// and this only happens if you are in a course, hence only during incourse.php
$closed = optional_param("closed", false, PARAM_BOOL);
$CourseHome = $SESSION->coursehome;

if ($closed && isset($CourseHome)) {

    // run the Regular Completion cron.
    if ($CFG->enablecompletion) {
        require_once($CFG->dirroot.'/completion/cron.php');
        completion_cron_criteria();
        completion_cron_completions();
    }

    // TODO: if the course activities are are now complete, do the redirect
    // this way, the course can have multiple activities and redirect after the last one

    redirect ($CourseHome);
    die(); // prevent further loading
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body <?php echo $OUTPUT->body_attributes($extracss); ?>><div class="body-wrapper"><div class="skin-wrapper">

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php include("inc_header.php"); ?>

<div id="page" class="container-fluid">

    <div id="page-content" class="row-fluid">
        <div id="region-main-box" class="<?php echo $regionmainbox; ?>">
            <div class="row-fluid">
                <section id="region-main" class="<?php echo $regionmain; ?>">
                    <?php
                    echo $OUTPUT->course_content_header();
                    echo $OUTPUT->main_content();
                    echo $OUTPUT->course_content_footer();
                    ?>
                </section>
                <?php echo $OUTPUT->blocks('side-pre', $sidepre); ?>
            </div>
        </div>
        <?php echo $OUTPUT->blocks('side-post', $sidepost); ?>
    </div>

	<?php include("inc_footer.php"); ?>

    <?php echo $OUTPUT->standard_end_of_body_html() ?>

</div></div></div>
</body>
</html>
