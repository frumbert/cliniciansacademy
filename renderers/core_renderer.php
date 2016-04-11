<?php
/**
 * @author     Based on code originally written by Julian Ridden, G J Barnard, Mary Evans, Bas Brands, Stuart Lamour and David Scotson.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once($CFG->dirroot . "/course/renderer.php");

class theme_cliniciansacademy_core_course_renderer extends core_course_renderer {

   public function course_category($category) {

        global $CFG;
        require_once($CFG->libdir. '/coursecatlib.php');
        $coursecat = coursecat::get(is_object($category) ? $category->id : $category);
        $site = get_site();
        $output = '';

        if (can_edit_in_category($category)) {
            // Add 'Manage' button if user has permissions to edit this category.
            $managebutton = $this->single_button(new moodle_url('/course/management.php'), get_string('managecourses'), 'get');
            $this->page->set_button($managebutton);
        }

        $chelper = new coursecat_helper();

        if (!$coursecat->id) {
            if (coursecat::count_all() == 1) {
                // There exists only one category in the system, do not display link to it
                $coursecat = coursecat::get_default();
                $strfulllistofcourses = get_string('fulllistofcourses');
                $this->page->set_title("$site->shortname: $strfulllistofcourses");
            } else {
                $strcategories = get_string('categories');
                $this->page->set_title("$site->shortname: $strcategories");
            }
        } else {
            $this->page->set_title("$site->shortname: ". $coursecat->get_formatted_name());

            // Print the category selector
            $output .= html_writer::start_tag('div', array('class' => 'categorypicker'));
            $output .= html_writer::start_tag('ul', array('class' => 'course-category-selector'));

            foreach (coursecat::make_categories_list() as $key => $value) {
                $output .= html_writer::start_tag('li', array('class' => ($coursecat->id == $key) ? 'selected' : ''));
                $catinst = coursecat::get($key);
                $href = new moodle_url('/course/index.php', array('categoryid' => $key));
                $output .= html_writer::start_tag('a', array('href' => $href));
                if ($description = $chelper->get_category_formatted_description($catinst)) {
                    $output .= $description;
                } else {
                    $output .= $value;
                }
                $output .=  html_writer::end_tag('a');
                $output .=  html_writer::end_tag('li');
            }
            $output .=  html_writer::end_tag('ul');

            // $select = new single_select(new moodle_url('/course/index.php'), 'categoryid',
            //         coursecat::make_categories_list(), $coursecat->id, null, 'switchcategory');
            // $select->set_label(get_string('categories').':');
            // $output .= $this->render($select);
            $output .= html_writer::end_tag('div'); // .categorypicker

        }

        // Print current category description
        //$chelper = new coursecat_helper();
        //if ($description = $chelper->get_category_formatted_description($coursecat)) {
        //    $output .= $this->box($description, array('class' => 'generalbox info'));
        //}

        // Prepare parameters for courses and categories lists in the tree
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_AUTO)
                ->set_attributes(array('class' => 'category-browse category-browse-'.$coursecat->id));

        $coursedisplayoptions = array();
        $catdisplayoptions = array();
        $browse = optional_param('browse', null, PARAM_ALPHA);
        $perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $baseurl = new moodle_url('/course/index.php');
        if ($coursecat->id) {
            $baseurl->param('categoryid', $coursecat->id);
        }
        if ($perpage != $CFG->coursesperpage) {
            $baseurl->param('perpage', $perpage);
        }
        $coursedisplayoptions['limit'] = $perpage;
        $catdisplayoptions['limit'] = $perpage;
        if ($browse === 'courses' || !$coursecat->has_children()) {
            $coursedisplayoptions['offset'] = $page * $perpage;
            $coursedisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
            $catdisplayoptions['nodisplay'] = true;
            $catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
            $catdisplayoptions['viewmoretext'] = new lang_string('viewallsubcategories');
        } else if ($browse === 'categories' || !$coursecat->has_courses()) {
            $coursedisplayoptions['nodisplay'] = true;
            $catdisplayoptions['offset'] = $page * $perpage;
            $catdisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
            $coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
            $coursedisplayoptions['viewmoretext'] = new lang_string('viewallcourses');
        } else {
            // we have a category that has both subcategories and courses, display pagination separately
            $coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses', 'page' => 1));
            $catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories', 'page' => 1));
        }
        $chelper->set_courses_display_options($coursedisplayoptions)->set_categories_display_options($catdisplayoptions);
        // Add course search form.
        $output .= $this->course_search_form();

        // Display course category tree.
        $output .= $this->coursecat_tree($chelper, $coursecat);

        // Add action buttons
        $output .= $this->container_start('buttons');
        $context = get_category_or_system_context($coursecat->id);
        if (has_capability('moodle/course:create', $context)) {
            // Print link to create a new course, for the 1st available category.
            if ($coursecat->id) {
                $url = new moodle_url('/course/edit.php', array('category' => $coursecat->id, 'returnto' => 'category'));
            } else {
                $url = new moodle_url('/course/edit.php', array('category' => $CFG->defaultrequestcategory, 'returnto' => 'topcat'));
            }
            $output .= $this->single_button($url, get_string('addnewcourse'), 'get');
        }
        ob_start();
        if (coursecat::count_all() == 1) {
            print_course_request_buttons(context_system::instance());
        } else {
            print_course_request_buttons($context);
        }
        $output .= ob_get_contents();
        ob_end_clean();
        $output .= $this->container_end();

        return $output;
    }

   function course_search_form($value = '', $format = 'plain') {
        return "";
   }


}

 class theme_cliniciansacademy_core_renderer extends theme_bootstrapbase_core_renderer {

// <li> <span class="accesshide "><span class="arrow_text">/</span>&nbsp;</span><span class="arrow sep">▶︎</span> <a title="Quiz" href="http://cliniciansacademy.avide.server/mod/quiz/view.php?id=91">Quiz - Blood Gases (2)</a></li>
// <li><a title="Clinical Blood Gas Interpretation" href="http://cliniciansacademy.avide.server/course/view.php?id=20">Blood Gas</a> <span class="divider"> <span class="accesshide "><span class="arrow_text">/</span>&nbsp;</span><span class="arrow sep">▶︎</span> </span></li>
// <li><a href="http://cliniciansacademy.avide.server/">Home</a> <span class="divider"> <span class="accesshide "><span class="arrow_text">/</span>&nbsp;</span><span class="arrow sep">▶︎</span> </span></li>

 	public function navbar() {
        global $SESSION;

        $items = $this->page->navbar->get_items();
        $ismodule = (strpos($this->page->url, "/mod/") !== false);
        if (empty($items)) {
            return '';
        }

        $htmlblocks = array();
        $hascategory = false;
        // Iterate the navarray and display each node
        $itemcount = count($items);
        // $separator = get_separator(); // not good enough
        // same format for the 'clean' theme; keep as close to this as possible for upgradability
        $icon = "";
        $separator = ' <span class="divider"> <span class="accesshide "><span class="arrow_text">/</span>&nbsp;</span> <i class="fa fa-angle-right"></i> </span>';
        for ($i=0;$i < $itemcount;$i++) {
            $item = $items[$i];
            $icon = "";
            
            if ($i === 0) {
	            $icon = "<i class='fa fa-home fa-inverse'></i> ";
            }

            if ($item->key == "home" && isset($SESSION->coursehomeurl)) {
                $item->action = new moodle_url($SESSION->coursehomeurl);
            }

            // Skip "My Courses"
            if ($item->get_content() == get_string('mycourses'))
              continue;

            // Skip "Courses"
            if ($item->get_content() == get_string('courses'))
              continue;
              
			// skip last node if it's an activity - the page title will show the activity name anyway
			if ($i === ($itemcount - 1) && $ismodule)
				continue;
              
            // skip empty nodes
            if ($item->action === null)
              continue;

            // skip sub-category nodes (lists top level only)
            if ($item->type == 10) {
                if ($hascategory) continue;
                $hascategory = true;
                // but... we have a custom category renderer page and we want to hyperlink to the
                // first sub-category of this item ... which (hack, fortunately) is the next item in the items array
                if (!empty($items[$i+1])) { // only if there is a "next" item
                    if ($items[$i+1]->type == 10) { // and only if the "next" item is a category
                        // this should avoid a top-level category that has courses linking back to the first course in the breadcrumb; only do it where there are nested categories. otherwise it is as normal.
                        $item->action = $items[$i+1]->action;
                    }
                }
            }

            if ($i === ($itemcount-1)) { $separator = ""; }
            $item->hideicon = true;
            $htmlblocks[] = html_writer::tag('li', $icon . $this->render($item) . $separator);

        }

        $navbarcontent = html_writer::tag('ul', join('', $htmlblocks), array('class'=>'breadcrumb'));
        return $navbarcontent;
    }

 	/*
     * This renders a notification message.
     * Uses bootstrap compatible html.
     */
    public function notification($message, $classes = 'notifyproblem') {
        $message = clean_text($message);
        $type = '';

        if ($classes == 'notifyproblem') {
            $type = 'alert alert-error';
        }
        if ($classes == 'notifysuccess') {
            $type = 'alert alert-success';
        }
        if ($classes == 'notifymessage') {
            $type = 'alert alert-info';
        }
        if ($classes == 'redirectmessage') {
            $type = 'alert alert-block alert-info';
        }
        return "<div class=\"$type\">$message</div>";
    }


    protected function render_custom_menu(custom_menu $menu) {
    	/*
    	* This code replaces adds the current enrolled
    	* courses to the custommenu.
    	*/

    	$hasdisplaymycourses = (empty($this->page->theme->settings->displaymycourses)) ? false : $this->page->theme->settings->displaymycourses;
        if (isloggedin() && !isguestuser() && $hasdisplaymycourses) {
        	$mycoursetitle = $this->page->theme->settings->mycoursetitle;
            if ($mycoursetitle == 'module') {
				$branchtitle = get_string('mymodules', 'theme_gourmet');
			} else if ($mycoursetitle == 'unit') {
				$branchtitle = get_string('myunits', 'theme_gourmet');
			} else if ($mycoursetitle == 'class') {
				$branchtitle = get_string('myclasses', 'theme_gourmet');
			} else {
				$branchtitle = get_string('mycourses', 'theme_gourmet');
			}
			$branchlabel = '<i class="fa fa-briefcase"></i>'.$branchtitle;
            $branchurl   = new moodle_url('/my/index.php');
            $branchsort  = 10000;

            $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
 			if ($courses = enrol_get_my_courses(NULL, 'fullname ASC')) {
 				foreach ($courses as $course) {
 					if ($course->visible){
 						$branch->add(format_string($course->fullname), new moodle_url('/course/view.php?id='.$course->id), format_string($course->shortname));
 					}
 				}
 			} else {
                $noenrolments = get_string('noenrolments', 'theme_gourmet');
 				$branch->add('<em>'.$noenrolments.'</em>', new moodle_url('/'), $noenrolments);
 			}

        }

        /*
    	* This code replaces adds the My Dashboard
    	* functionality to the custommenu.
    	*/
        $hasdisplaymydashboard = (empty($this->page->theme->settings->displaymydashboard)) ? false : $this->page->theme->settings->displaymydashboard;
        if (isloggedin() && !isguestuser() && $hasdisplaymydashboard) {
            $branchlabel = '<i class="fa fa-dashboard"></i>'.get_string('mydashboard', 'theme_gourmet');
            $branchurl   = new moodle_url('/my/index.php');
            $branchtitle = get_string('mydashboard', 'theme_gourmet');
            $branchsort  = 10000;

            $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
            $branch->add('<em><i class="fa fa-home"></i>'.get_string('myhome').'</em>',new moodle_url('/my/index.php'),get_string('myhome'));
 			$branch->add('<em><i class="fa fa-user"></i>'.get_string('profile').'</em>',new moodle_url('/user/profile.php'),get_string('profile'));
 			$branch->add('<em><i class="fa fa-calendar"></i>'.get_string('pluginname', 'block_calendar_month').'</em>',new moodle_url('/calendar/view.php'),get_string('pluginname', 'block_calendar_month'));
 			$branch->add('<em><i class="fa fa-envelope"></i>'.get_string('pluginname', 'block_messages').'</em>',new moodle_url('/message/index.php'),get_string('pluginname', 'block_messages'));
 			$branch->add('<em><i class="fa fa-certificate"></i>'.get_string('badges').'</em>',new moodle_url('/badges/mybadges.php'),get_string('badges'));
 			$branch->add('<em><i class="fa fa-file"></i>'.get_string('privatefiles', 'block_private_files').'</em>',new moodle_url('/user/files.php'),get_string('privatefiles', 'block_private_files'));
 			$branch->add('<em><i class="fa fa-sign-out"></i>'.get_string('logout').'</em>',new moodle_url('/login/logout.php'),get_string('logout'));
        }

        return parent::render_custom_menu($menu);
    }


    /**
    * Get the HTML for blocks in the given region.
    *
    * @since 2.5.1 2.6
    * @param string $region The region to get HTML for.
    * @return string HTML.
    * Written by G J Barnard
    */

    public function cliniciansacademyblocks($region, $classes = array(), $tag = 'aside') {
        $classes = (array)$classes;
        $classes[] = 'block-region';
        $attributes = array(
            'id' => 'block-region-'.preg_replace('#[^a-zA-Z0-9_\-]+#', '-', $region),
            'class' => join(' ', $classes),
            'data-blockregion' => $region,
            'data-droptarget' => '1'
        );
        return html_writer::tag($tag, $this->blocks_for_region($region), $attributes);
    }

}

