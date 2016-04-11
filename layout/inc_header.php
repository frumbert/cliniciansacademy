<?php if (FALSE) { ?><header role="banner" class="navbar navbar-fixed-top<?php echo $html->navbarclass ?> moodle-has-zindex">
    <nav role="navigation" class="navbar-inner">
        <div class="container-fluid">
            <a class="brand" href="<?php echo $CFG->wwwroot;?>"><?php echo
                format_string($SITE->shortname, true, array('context' => context_course::instance(SITEID)));
                ?></a>
            <?php echo $OUTPUT->navbar_button(); ?>
            <?php echo $OUTPUT->user_menu(); ?>
            <div class="nav-collapse collapse">
                <?php echo $OUTPUT->custom_menu(); ?>
                <ul class="nav pull-right">
                    <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
                </ul>
            </div>
        </div>
    </nav>
</header><?php } ?>

<header id="page-header" class="container-fluid">
    <div class="row-fluid">
        <div class="span6 text-left"><?= $html->heading; ?></div>
        <div class="span6 text-right">
<?php

        if (is_siteadmin()) {
            echo "<a href='/course/index.php?categoryid=5' class='course-home btn btn-default' title='Only admins see this button'><i class='fa fa-map'></i> Catalogue</a>";
        }

        if (isset($incourse) && $incourse === true) {
            if ( strpos( $_SERVER['REQUEST_URI'], 'mod/scorm/player.php' ) === false ) {
                echo html_writer::tag("a","<i class='fa fa-mortar-board'></i> Course Home", array("href"=>new moodle_url("/course/view.php", array("id" => $PAGE->course->id)), "class" => "course-home btn btn-default"));
            }
        }

        $page_heading_button = $OUTPUT->page_heading_button(); // the raw button (a , or div>form>div>input)
        $page_button_text = strtolower(preg_replace("/[^a-zA-Z0-9]/", "", strip_tags($page_heading_button))); // text for creating a unique classname for this button, if used
        $page_heading_button = str_replace("<a title=", "<a class='btn btn-default btn-" . $page_button_text ."' title=", $page_heading_button); // make it look like a button
        if (false !== strpos($page_heading_button, "Save & Exit")) {
            $page_heading_button = str_replace("Save & Exit", "<i class='fa fa-save'></i> Save & Exit", $page_heading_button); // save icon
            $page_heading_button = str_replace("view.php?id=", "view.php?closed=true&id=", $page_heading_button); // save icon
        }
        echo $page_heading_button;

        if (isloggedin() && is_siteadmin()) {
           echo html_writer::tag("a","<i class='fa fa-unlock-alt'></i> Logout", array("href"=>new moodle_url("/login/logout.php", array("sesskey" => $USER->sesskey)), "class" => "logout-link btn btn-default"));
        } else if (!isloggedin()) {
           echo html_writer::tag("a","<i class='fa fa-lock'></i> Login", array("href"=>new moodle_url("/login/index.php", array()), "class" => "logout-link btn btn-default"));
        }
?>
        </div>
    </div>
</header>

<?php if (is_siteadmin()) { ?>
<nav id="page-breadcrumbs" class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <?= $OUTPUT->navbar(); ?>
        </div>
    </div>
</nav>
<?php } else {
    echo "<p>&nbsp;</p>";
    }
?>