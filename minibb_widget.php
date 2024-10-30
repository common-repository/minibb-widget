<?php

/*
Plugin Name: miniBB Widget
Plugin URI:  http://kuopassa.net/minibb-widget
Description: Display your miniBB-powered forum's content, like recent and most popular topics, in your theme with Widgets.
Version:     0.3
Author:      Petri Ikonen
Author URI:  http://kuopassa.net/
License:     GPL2

{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {URI to Plugin License}.
*/

if (!defined('WPINC')) {
	die;
}

# BEGINS: LAUNCH THINGS
add_action('admin_menu','kuo_minibb_widget_page');
add_action('admin_init','kuo_minibb_widget_init');
add_action('widgets_init','kuo_register_minibb_widget');
add_filter('plugin_action_links_kuo_register_minibb_widget','kuo_minibb_widget_settings_link');
# ENDS: LAUNCH THINGS

# BEGINS: WHEN ATTEMPTING TO INSTALL
if (!function_exists('kuo_minibb_widget_install')) {
    register_activation_hook(__FILE__,'kuo_minibb_widget_install');

    function kuo_minibb_widget_install() {
        # SINGLE BLOG:
        add_option('kuo_minibb_widget_forum_url',NULL,NULL,'no');
        add_option('kuo_minibb_widget_db_host','localhost',NULL,'no');
        add_option('kuo_minibb_widget_db_database',NULL,NULL,'no');
        add_option('kuo_minibb_widget_db_username',NULL,NULL,'no');
        add_option('kuo_minibb_widget_db_password',NULL,NULL,'no');
        add_option('kuo_minibb_widget_table_forums',NULL,NULL,'no');
        add_option('kuo_minibb_widget_table_topics',NULL,NULL,'no');

        # WPMU:
        add_site_option('kuo_minibb_widget_forum_url',NULL);
        add_site_option('kuo_minibb_widget_db_host','localhost');
        add_site_option('kuo_minibb_widget_db_database',NULL);
        add_site_option('kuo_minibb_widget_db_username',NULL);
        add_site_option('kuo_minibb_widget_db_password',NULL);
        add_site_option('kuo_minibb_widget_table_forums',NULL);
        add_site_option('kuo_minibb_widget_table_topics',NULL);

        # THIS WILL HELP TO UNDERSTAND HOW URL'S SHOULD BE FORMED
        # 0 = MESSY URLS
        #     FORUM SECTION: /index.php?action=vtopic&forum=[FORUM_ID]&topic=1
        #     FORUM SECTION PAGE: /index.php?action=vtopic&forum=[FORUM_ID]&page=[PAGE]
        #     TOPIC: /index.php?action=vthread&forum=[FORUM_ID]&topic=[TOPIC_ID]
        #     TOPIC PAGE: /index.php?action=vthread&forum=[FORUM_ID]&topic=[TOPIC_ID]&page=[PAGE]
        #
        # 1 = URL SCHEME #1 WHEN:
        #     FORUM SECTION: [FORUM_TITLE]-f[FORUM_ID].html
        #     FORUM SECTION PAGE: [FORUM_TITLE]-f[FORUM_ID]-[PAGE].html
        #     TOPIC: [TOPIC_TITLE]-[FORUM_ID]-[TOPIC_ID].html
        #     TOPIC PAGE: [TOPIC_TITLE]-[FORUM_ID]-[TOPIC_ID]-[PAGE].html
        #
        # 2 = URL SCHEME #2 WHEN:
        #     FORUM SECTION: [FORUM_TITLE]-[FORUM_ID]/
        #     FORUM SECTION PAGE: [FORUM_TITLE]-[FORUM_ID]/index[PAGE].html
        #     TOPIC: [FORUM_TITLE]-[FORUM_ID]/[TOPIC_TITLE]-[TOPIC_ID].html
        #     TOPIC PAGE: [FORUM_TITLE]-[FORUM_ID]/[TOPIC_TITLE]-[TOPIC_ID]-[PAGE].html
        #
        # 3 = URL SCHEME #3 WHEN:
        #     FORUM SECTION: [FORUM_TITLE]-[FORUM_ID]/
        #     FORUM SECTION PAGE: [FORUM_TITLE]-[FORUM_ID]/[PAGE]/
        #     TOPIC: [FORUM_TITLE]-[FORUM_ID]/[TOPIC_TITLE]-[TOPIC_ID]/
        #     TOPIC PAGE: [FORUM_TITLE]-[FORUM_ID]/[TOPIC_TITLE]-[TOPIC_ID]/[PAGE]/
        # SINGLE BLOG:
        add_option('kuo_minibb_widget_url_scheme',0,NULL,'yes');
        # WPMU:
        add_site_option('kuo_minibb_widget_url_scheme',0,NULL);
    }
}
# ENDS: WHEN ATTEMPTING TO INSTALL

# BEGINS: WHEN ATTEMPTING TO UNINSTALL
if (!function_exists('kuo_minibb_widget_uninstall')) {
    register_uninstall_hook(__FILE__,'kuo_minibb_widget_uninstall');
    function kuo_minibb_widget_uninstall() {
        # SINGLE BLOG:
        delete_option('kuo_minibb_widget_forum_url');
        delete_option('kuo_minibb_widget_db_host');
        delete_option('kuo_minibb_widget_db_database');
        delete_option('kuo_minibb_widget_db_username');
        delete_option('kuo_minibb_widget_db_password');
        delete_option('kuo_minibb_widget_table_forums');
        delete_option('kuo_minibb_widget_table_topics');
        delete_option('kuo_minibb_widget_url_scheme');

        # WPMU:
        delete_site_option('kuo_minibb_widget_forum_url');
        delete_site_option('kuo_minibb_widget_db_host');
        delete_site_option('kuo_minibb_widget_db_database');
        delete_site_option('kuo_minibb_widget_db_username');
        delete_site_option('kuo_minibb_widget_db_password');
        delete_site_option('kuo_minibb_widget_table_forums');
        delete_site_option('kuo_minibb_widget_table_topics');
        delete_site_option('kuo_minibb_widget_url_scheme');

        unregister_setting('kuo_minibb_widget');
    }
}
# ENDS: WHEN ATTEMPTING TO UNINSTALL

# BEGINS: LAUNCH MINIBB CLASS FOR "WIDGETS" SECTION
function kuo_register_minibb_widget() {
    register_widget('miniBB_Widget');
}
# ENDS: LAUNCH MINIBB CLASS FOR "WIDGETS" SECTION

# BEGINS: PREPARE SECTION TO BE SHOWN IN THE SETTINGS PAGE
function kuo_minibb_widget_init() {
    # THIS WILL HANDLE SANITIZING AND SAVING
    register_setting('kuo_minibb_widget','kuo_minibb_widget_forum_url','kuo_minibb_widget_sanitize_url');
    register_setting('kuo_minibb_widget','kuo_minibb_widget_db_host');
    register_setting('kuo_minibb_widget','kuo_minibb_widget_db_database');
    register_setting('kuo_minibb_widget','kuo_minibb_widget_db_username');
    register_setting('kuo_minibb_widget','kuo_minibb_widget_db_password');
    register_setting('kuo_minibb_widget','kuo_minibb_widget_table_forums');
    register_setting('kuo_minibb_widget','kuo_minibb_widget_table_topics');
    register_setting('kuo_minibb_widget','kuo_minibb_widget_url_scheme','intval');

    # THIS WILL LAUNCH CERTAIN SETTINGS SECTION HTML
    add_settings_section('kuo_minibb_widget_option',NULL,'kuo_minibb_widget_setting_palette','kuo_minibb_widget');

    # BEGINS: CUSTOM FUNCTION TO CHECK THAT IT'S AN URL USER IS OFFERING
    function kuo_minibb_widget_sanitize_url($url) {
        if (filter_var($url,FILTER_VALIDATE_URL)) {
            return filter_var($url,FILTER_SANITIZE_URL);
        }
        else {
            return;
        }
    }
    # ENDS: CUSTOM FUNCTION TO CHECK THAT IT'S AN URL USER IS OFFERING
}
# ENDS: PREPARE SECTION TO BE SHOWN IN THE SETTINGS PAGE

# BEGINS: CREATE A LINK TO THE SETTINGS PAGE WHEN VIEWING "PLUGINS" PAGE
function kuo_minibb_widget_settings_link($links) {
	return array_merge(array('<a href="'.admin_url('tools.php?page=minibb_widget').'" rel="nofollow">'.__('Settings').'</a>'),$links);
}
# ENDS: CREATE A LINK TO THE SETTINGS PAGE WHEN VIEWING "PLUGINS" PAGE

# BEGINS: CREATE HTML FOR THE SETTINGS PAGE
function kuo_minibb_widget_setting_palette() {
    define('KUO_MINIBB_WIDGET_SELECTED_SELECTED',' selected="selected"');
    define('KUO_MINIBB_WIDGET_SEEMS_TO_BE_OK',' <strong class="notice notice-success">Could be OK</strong>');
    define('KUO_MINIBB_WIDGET_PLEASE_ENTER_VALUE',' <strong class="notice notice-info">Please enter value</strong>');

    # ERRORS AND SUCCESSES FOR THE FORM BELOW
    #
    # FIRST THE URL:
    if (filter_var(get_option('kuo_minibb_widget_forum_url'),FILTER_VALIDATE_URL)) {
        $kuo_minibb_widget_url_status = KUO_MINIBB_WIDGET_SEEMS_TO_BE_OK;
    }
    else {
        $kuo_minibb_widget_url_status = KUO_MINIBB_WIDGET_PLEASE_ENTER_VALUE;
    }
    #
    # THEN HOST:
    if (!empty(get_option('kuo_minibb_widget_db_host'))) {
        $kuo_minibb_widget_host_status = KUO_MINIBB_WIDGET_SEEMS_TO_BE_OK;
    }
    else {
        $kuo_minibb_widget_host_status = KUO_MINIBB_WIDGET_PLEASE_ENTER_VALUE;
    }
    #
    # THEN DATABASE NAME:
    if (!empty(get_option('kuo_minibb_widget_db_database'))) {
        $kuo_minibb_widget_database_status = KUO_MINIBB_WIDGET_SEEMS_TO_BE_OK;
    }
    else {
        $kuo_minibb_widget_database_status = KUO_MINIBB_WIDGET_PLEASE_ENTER_VALUE;
    }
    #
    # THEN DATABASE USERNAME:
    if (!empty(get_option('kuo_minibb_widget_db_username'))) {
        $kuo_minibb_widget_username_status = KUO_MINIBB_WIDGET_SEEMS_TO_BE_OK;
    }
    else {
        $kuo_minibb_widget_username_status = KUO_MINIBB_WIDGET_PLEASE_ENTER_VALUE;
    }
    #
    # THEN DATABASE PASSWORD:
    if (!empty(get_option('kuo_minibb_widget_db_password'))) {
        $kuo_minibb_widget_password_status = KUO_MINIBB_WIDGET_SEEMS_TO_BE_OK;
    }
    else {
        $kuo_minibb_widget_password_status = KUO_MINIBB_WIDGET_PLEASE_ENTER_VALUE;
    }
    #
    # THEN FORUMS TABLE NAME:
    if (!empty(get_option('kuo_minibb_widget_table_forums'))) {
        $kuo_minibb_widget_table1_status = KUO_MINIBB_WIDGET_SEEMS_TO_BE_OK;
    }
    else {
        $kuo_minibb_widget_table1_status = KUO_MINIBB_WIDGET_PLEASE_ENTER_VALUE;
    }
    #
    # THEN TOPICS TABLE NAME:
    if (!empty(get_option('kuo_minibb_widget_table_topics'))) {
        $kuo_minibb_widget_table2_status = KUO_MINIBB_WIDGET_SEEMS_TO_BE_OK;
    }
    else {
        $kuo_minibb_widget_table2_status = KUO_MINIBB_WIDGET_PLEASE_ENTER_VALUE;
    }

    # BEGINS: CHECK IF DATABASE CONNECTION CAN BE ESTABLISHED
    if (in_array(KUO_MINIBB_WIDGET_SEEMS_TO_BE_OK,array($kuo_minibb_widget_host_status,$kuo_minibb_widget_database_status,$kuo_minibb_widget_username_status,$kuo_minibb_widget_password_status))) {

        # BEGINS: ESTABLISH MYSQL CONNECTION TO MINIBB
        $kuo_minibb_widget_test_connection = mysqli_connect(
            get_option('kuo_minibb_widget_db_host'),
            get_option('kuo_minibb_widget_db_username'),
            get_option('kuo_minibb_widget_db_password'),
            get_option('kuo_minibb_widget_db_database')
        );
        # ENDS: ESTABLISH MYSQL CONNECTION TO MINIBB

        if ((!$kuo_minibb_widget_test_connection)
        or (empty(get_option('kuo_minibb_widget_db_host')))
        or (empty(get_option('kuo_minibb_widget_db_username')))
        or (empty(get_option('kuo_minibb_widget_db_password')))
        or (empty(get_option('kuo_minibb_widget_db_database')))) {
            $kuo_minibb_widget_test_connection_status = '<div class="notice notice-error"><p>Database connection to forum:</strong>
            not established. '.mysqli_connect_error().'</p></div>';
        }
        else {
            $kuo_minibb_widget_test_connection_status = '<div class="notice notice-success"><p><strong>Database connection to forum:</strong>
            established.</p></div>';
            mysqli_close($kuo_minibb_widget_test_connection);
        }

        unset($kuo_minibb_widget_test_connection);
    }
    else {
        $kuo_minibb_widget_test_connection_status = NULL;
    }
    # ENDS: CHECK IF DATABASE CONNECTION CAN BE ESTABLISHED

    echo '<p><label for="kuo_minibb_widget_forum_url">Full forum URL: </label><input type="url"
    name="kuo_minibb_widget_forum_url" id="kuo_minibb_widget_forum_url" size="75" maxlength="255"
        value="',esc_attr(get_option('kuo_minibb_widget_forum_url')),'" />',$kuo_minibb_widget_url_status,'</p>
        <hr />
        <p>These settings below are located in miniBB\'s root directory inside <code>setup_options.php</code> file.
        They are needed here so that content from that database can be fetched and presented.</p>
        <p><label for="kuo_minibb_widget_db_host"><code>&dollar;DBhost</code> value: </label><input type="text"
        name="kuo_minibb_widget_db_host" id="kuo_minibb_widget_db_host" size="35" maxlength="255"
        value="',esc_attr(get_option('kuo_minibb_widget_db_host')),'" placeholder="By default &quot;localhost&quot;" />
        (<em>meaning:</em> database host name)',$kuo_minibb_widget_host_status,'</p>
        <p><label for="kuo_minibb_widget_db_database"><code>&dollar;DBname</code> value: </label><input type="text"
        name="kuo_minibb_widget_db_database" id="kuo_minibb_widget_db_database" size="35" maxlength="255"
        value="',esc_attr(get_option('kuo_minibb_widget_db_database')),'" /> (<em>meaning:</em> name of the database)',$kuo_minibb_widget_database_status,'</p>
        <p><label for="kuo_minibb_widget_db_username"><code>&dollar;DBusr</code> value: </label><input type="text"
        name="kuo_minibb_widget_db_username" id="kuo_minibb_widget_db_username" size="35" maxlength="255"
        value="',esc_attr(get_option('kuo_minibb_widget_db_username')),'" /> (<em>meaning:</em> database username)',$kuo_minibb_widget_username_status,'</p>
        <p><label for="kuo_minibb_widget_db_password"><code>&dollar;DBpwd</code> value: </label><input type="text"
        name="kuo_minibb_widget_db_password" id="kuo_minibb_widget_db_password" size="35" maxlength="255"
        value="',esc_attr(get_option('kuo_minibb_widget_db_password')),'" /> (<em>meaning:</em> database password)',$kuo_minibb_widget_username_status,'</p>
        ',$kuo_minibb_widget_test_connection_status,'
        <hr />
        <p><label for="kuo_minibb_widget_table_forums"><code>&dollar;Tf</code> value: </label><input type="text"
        name="kuo_minibb_widget_table_forums" id="kuo_minibb_widget_table_forums" size="35" maxlength="255"
        value="',esc_attr(get_option('kuo_minibb_widget_table_forums')),'" placeholder="By default &quot;minibbtable_forums&quot;" />
        (<em>meaning:</em> name of forum\'s forums table)',$kuo_minibb_widget_table1_status,'</p>
        <p><label for="kuo_minibb_widget_table_topics"><code>&dollar;Tt</code> value: </label><input type="text"
        name="kuo_minibb_widget_table_topics" id="kuo_minibb_widget_table_topics" size="35" maxlength="255"
        value="',esc_attr(get_option('kuo_minibb_widget_table_topics')),'" placeholder="By default &quot;minibbtable_topics&quot;" />
        (<em>meaning: </em> name of forum\'s topics table)',$kuo_minibb_widget_table2_status,'</p>
        <hr />
        <p><label for="kuo_minibb_widget_url_scheme">URL scheme used in forum: </label><select
        name="kuo_minibb_widget_url_scheme" id="kuo_minibb_widget_url_scheme" required="required"><option value="0"';
            if (empty(get_option('kuo_minibb_widget_url_scheme'))) {
                echo KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }
        echo '>Default (&quot;messy&quot; URL\'s)</option><option value="1"';
            if (get_option('kuo_minibb_widget_url_scheme') == 1) {
                echo KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }
        echo '>addon_mod_rewrite_1.php</option><option value="2"';
            if (get_option('kuo_minibb_widget_url_scheme') == 2) {
                echo KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }
        echo '>addon_mod_rewrite_2.php</option><option value="3"';
            if (get_option('kuo_minibb_widget_url_scheme') == 3) {
                echo KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }
        echo '>addon_mod_rewrite_3.php</option></select> (<em>meaning:</em> what kind of structure links are using)</p><hr />';
}
# ENDS: CREATE HTML FOR THE SETTINGS PAGE

# BEGINS: CREATING A PLACE WHERE SETTINGS CAN BE UPDATED
function kuo_minibb_widget_page() {
    add_submenu_page('tools.php','miniBB Widget','miniBB Widget','manage_options','minibb_widget','kuo_minibb_widget_page_html');
}
# ENDS: CREATING A PLACE WHERE SETTINGS CAN BE UPDATED

# BEGINS: CREATING THE ACTUAL PAGE CONTENT WHERE SETTINGS ARE UPDATED
function kuo_minibb_widget_page_html() {
    # BEGINS: IF USER PRIVILEGES ARE NOT SUFFICIENT
    if (!current_user_can('manage_options')) {
        return;
    }
    # ENDS: USER PRIVILEGES ARE NOT SUFFICIENT
    # BEGINS: OTHERWISE CONTINUE CREATING HTML
    else {
        # PREPARING RESPONSES WHEN SETTINGS ARE SAVED OR ATTEMPTED TO
        if (isset($_GET['settings-updated'])) {
            add_settings_error('kuo_minibb_widget_messages','kuo_minibb_widget_message',__('Settings were saved.','kuo_minibb_widget'),'updated');
        }

        # THIS WILL SHOW MESSAGES IF THERE IS ANY
        settings_errors('kuo_minibb_widget_messages');

        # SURPRISINGLY WORDPRESS FORCES TO SPLIT ECHO TO ECHOES TO GET THINGS IN PROPER ORDER
        echo '<div class="wrap"><h1>Settings for miniBB Widget</h1><form action="options.php" method="post">
        <fieldset><legend>Please check these settings in order to get data out of your miniBB forum</legend>';
        echo settings_fields('kuo_minibb_widget'),do_settings_sections('kuo_minibb_widget'),submit_button(__('Save'),'primary','kuo_minibb_widget_save');
        echo '</fieldset></form></div>';
    }
    # ENDS: OTHERWISE CONTINUE CREATING HTML
}
# ENDS: CREATING THE ACTUAL PAGE CONTENT WHERE SETTINGS ARE UPDATED

# BEGINS: IF MINIBB_WIDGET CLASS EXISTS
if (!class_exists('miniBB_Widget')) {
    # BEGINS: MINIBB_WIDGET CLASS
    class miniBB_Widget extends WP_Widget {

        # BEGINS: PHP CONSTRUCTOR
	    function __construct() {
		    parent::__construct(

                # FIRST SET WIDGET BASE CSS ID
	    	    'minibb_widget',

                # THEN SET WIDGET NAME
    			esc_html__('miniBB Widget','kuo_minibb_widget'),

                # AFTER THAT LETS'S SET CSS CLASS AND WIDGET DESCRIPTION
		    	array('classname'=>'kuo_minibb_widget','description'=>esc_html__('Fetch topics from from miniBB','kuo_minibb_widget'))
		    );
	    }
        # ENDS: PHP CONSTRUCTOR

        # BEGINS: FRONT-END WIDGET FUNCTIONALITY
	    public function widget($args,$instance) {

            # CONTAINER FOR THE WIDGET CONTENT
            $kuo_minibb_widget_html = NULL;

            # FIRST CHECK THE TITLE
		    if ((isset($instance['title'])) && (!empty($instance['title']))) {
			    $kuo_minibb_widget_html .= $args['before_title'].apply_filters('widget_title',$instance['title']).$args['after_title'];
		    }

            # BEGINS: ESTABLISH DATABASE CONNECTION
            $kuo_minibb_widget_db_link = mysqli_connect(
                get_option('kuo_minibb_widget_db_host'),
                get_option('kuo_minibb_widget_db_username'),
                get_option('kuo_minibb_widget_db_password'),
                get_option('kuo_minibb_widget_db_database')
            );
            # ENDS: ESTABLISH DATABASE CONNECTION

            if (($kuo_minibb_widget_db_link)
            && (!empty(get_option('kuo_minibb_widget_db_host')))
            && (!empty(get_option('kuo_minibb_widget_db_username')))
            && (!empty(get_option('kuo_minibb_widget_db_password')))
            && (!empty(get_option('kuo_minibb_widget_db_database')))) {

                # NAMING DATABASE TABLES
                $kuo_minibb_widget_table_forums = get_option('kuo_minibb_widget_table_forums');
                $kuo_minibb_widget_table_topics = get_option('kuo_minibb_widget_table_topics');

                # BEGINS: HOW MANY RESULTS AT MAX.
                if ((isset($instance['count'])) && (is_numeric($instance['count'])) && (!empty($instance['count'])) && ($kuo_minibb_widget_count <= 1000)) {
                    $kuo_minibb_widget_count = intval($instance['count']);
                }
                else {
                    $kuo_minibb_widget_count = 5;
                }
                # ENDS: HOW MANY RESULTS AT MAX.

                # BEGINS: SHOULD STICKY TOPICS BE INCLUDED OR EXLUDED
                # 0 = DON'T ALLOW
                if (empty($instance['ignore_sticky'])) {
                    $kuo_minibb_widget_sticky_topics_sql = ' AND '.$kuo_minibb_widget_table_topics.'.sticky = 0';
                }
                # 1 = ALLOW
                else {
                    $kuo_minibb_widget_sticky_topics_sql = NULL;
                }
                # ENDS: SHOULD STICKY TOPICS BE INCLUDED OR EXLUDED

                # BEGINS: IF SORT: MOST RECENT
                if ($instance['sort'] == 1) {
                    $kuo_minibb_widget_sort_sql = ' ORDER BY '.$kuo_minibb_widget_table_topics.'.topic_last_post_time DESC';
                }
                # ENDS: IF SORT: MOST RECENT
                # BEGINS: IF SORT: MOST POPULAR BY POSTS
                elseif ($instance['sort'] == 2) {
                    $kuo_minibb_widget_sort_sql = ' ORDER BY '.$kuo_minibb_widget_table_topics.'.posts_count DESC';
                }
                # ENDS: IF SORT: MOST POPULAR BY POSTS
                # BEGINS: IF SORT: MOST POPULAR BY VIEW COUNT
                else {
                    $kuo_minibb_widget_sort_sql = ' ORDER BY '.$kuo_minibb_widget_table_topics.'.topic_views DESC';
                }

                # BEGINS: SHOULD CLOSED TOPICS BE INCLUDED OR EXCLUDED
                # 0 = DON'T ALLOW
                if (empty($instance['ignore_closed'])) {
                    $kuo_minibb_widget_ignore_closed_sql = ' AND '.$kuo_minibb_widget_table_topics.'.topic_status = 0';
                }
                # 1 = ALLOW
                else {
                    $kuo_minibb_widget_ignore_closed_sql = NULL;
                }
                # ENDS: SHOULD CLOSED TOPICS BE INCLUDED OR EXCLUDED

                # BEGINS: IF SOME FORUM ID'S (SECTIONS / AREAS) EXCLUDED FROM RESULTS
                if (empty($instance['exclude_forums'])) {
                    $kuo_minibb_widget_exlude_forums_sql = NULL;
                }
                else {
                    $kuo_minibb_widget_exlude_forums_sql = filter_var_array(explode(',',$instance['exclude_forums']),FILTER_SANITIZE_NUMBER_INT);
                    if (empty($kuo_minibb_widget_exlude_forums_sql)) {
                        $kuo_minibb_widget_exlude_forums_sql = NULL;
                    }
                    else {
                        $kuo_minibb_widget_exlude_forums_sql = ' AND '.$kuo_minibb_widget_table_topics.'.forum_id NOT IN ('.implode(',',$kuo_minibb_widget_exlude_forums_sql).')';
                    }
                }
                # ENDS: IF SOME FORUM ID'S (SECTIONS / AREAS) EXCLUDED FROM RESULTS

                # BEGINS: ACTUAL SQL QUERY
                $kuo_minibb_widget_sql_content = mysqli_query($kuo_minibb_widget_db_link,"
                SELECT
                    ".$kuo_minibb_widget_table_topics.".topic_id AS `topic_id`,
                    ".$kuo_minibb_widget_table_topics.".topic_title AS `topic_title`,
                    ".$kuo_minibb_widget_table_topics.".forum_id AS `forum_id`,
                    ".$kuo_minibb_widget_table_topics.".topic_last_post_id AS `topic_last_post_id`,
                    ".$kuo_minibb_widget_table_topics.".topic_time AS `topic_time`,
                    ".$kuo_minibb_widget_table_forums.".forum_name AS `forum_name`
                FROM
                    `".$kuo_minibb_widget_table_topics."`
                INNER JOIN
                    ".$kuo_minibb_widget_table_forums."
                ON
                    ".$kuo_minibb_widget_table_topics.".forum_id = ".$kuo_minibb_widget_table_forums.".forum_id
                WHERE
                    ".$kuo_minibb_widget_table_topics.".topic_last_poster != ''
                AND
                    ".$kuo_minibb_widget_table_topics.".forum_id != ''
                ".$kuo_minibb_widget_sticky_topics_sql.$kuo_minibb_widget_ignore_closed_sql.$kuo_minibb_widget_exlude_forums_sql.$kuo_minibb_widget_sort_sql."
                LIMIT 0, ".$kuo_minibb_widget_count);
                # ENDS: ACTUAL SQL QUERY

                # CLEANING HOUSE
                unset(
                    $kuo_minibb_widget_table_topics,
                    $kuo_minibb_widget_table_forums,
                    $kuo_minibb_widget_count,
                    $kuo_minibb_widget_sticky_topics_sql,
                    $kuo_minibb_widget_ignore_closed_sql,
                    $kuo_minibb_widget_sort_sql,
                    $kuo_minibb_widget_exlude_forums_sql
                );

                # FORUM MAIN URL
                $kuo_minibb_widget_forum_url = get_option('kuo_minibb_widget_forum_url');

                # BEGINS: CHECK IF SQL QUERY AND FORUM URL ARE OK
                if (($kuo_minibb_widget_sql_content)
                && (mysqli_num_rows($kuo_minibb_widget_sql_content) != 0)
                && (filter_var($kuo_minibb_widget_forum_url,FILTER_VALIDATE_URL))) {

                    # BEGINS: SIMILAR FUNCTION AS OFFERED BY MINIBB TO CREATE SIMILAR URL SLUG AS MINIBB DOES
                    # FOR THIS FUNCTION THANKS GOES TO ***PAUL OF MINIBB***
                    function convertTitle($topicTitle) {
                        $remove_words = array('and','or','i','a','the','in','s','m','t','d');

                        $topicTitle = preg_replace("/\.{2,}/",'',$topicTitle);
                        $topicTitle = str_replace(array('&amp;','+','/','-'),' ',strtolower($topicTitle));
                        $topicTitle = preg_replace("/&#*[0-9a-z]+;/i",'',$topicTitle);
                        $topicTitle = preg_replace('#[^a-z0-9._ ]#','',$topicTitle);
                        $topicTitle = preg_replace("#[ ]{2,}#",' ',$topicTitle);
                        $topicTitle = str_replace(' ','-',trim($topicTitle));

                        foreach($remove_words as $w) {
                            $topicTitle = preg_replace("#^(".$w.")[-](.*?)$#i",'\\2',$topicTitle);
                            $topicTitle = preg_replace("#^(.+?)[-](".$w.")$#i",'\\1',$topicTitle);
                        }

                        $w2 = array();

                        foreach ($remove_words as $w) {
                            $w2[] = '-'.$w.'-';
                        }

                        $topicTitle = str_replace($w2,'-',$topicTitle);

                        if (strlen($topicTitle) > 70) {
                            $newtopic = explode('-',$topicTitle);
                            while (strlen($topicTitle) > 70) {
                                array_pop($newtopic);
                                $topicTitle = implode('-',$newtopic);
                            }
                        }

                        $topicTitle = preg_replace("#^(.*?)-([0-9-]+)$#",'\\1',$topicTitle);

                        if (strlen($topicTitle) < 3) {
                            $topicTitle = 'topic';
                        }

                        return $topicTitle;
                    }
                    # ENDS: SIMILAR FUNCTION AS OFFERED BY MINIBB TO CREATE SIMILAR URL SLUG AS MINIBB DOES

                    $kuo_minibb_widget_html .= '<ul>';

                    while ($kuo_minibb_widget_item = mysqli_fetch_assoc($kuo_minibb_widget_sql_content)) {
                        # BEGINS: IF TIMESTAMP SHOULD BE SHOWN
                        if (!empty($instance['show_timestamp'])) {
                            $kuo_minibb_widget_timestamp = ' ('.date(get_option('date_format').' '.get_option('time_format'),strtotime($kuo_minibb_widget_item['topic_time'])).')';
                        }
                        else {
                            $kuo_minibb_widget_timestamp = NULL;
                        }

                        # CLEANING TOPIC NAME
                        $kuo_minibb_widget_topic = $kuo_minibb_widget_item['topic_title'];
                        if (is_numeric($kuo_minibb_widget_topic)) {
                            $kuo_minibb_widget_topic = filter_var($kuo_minibb_widget_topic,FILTER_SANITIZE_NUMBER_INT);
                        }
                        else {
                            $kuo_minibb_widget_topic = filter_var($kuo_minibb_widget_topic,FILTER_SANITIZE_STRING);
                        }

                        # BUILDING URLS
                        # 0 = MESSY URLS
                        if (get_option('kuo_minibb_widget_url_scheme') == 0) {
                            $kuo_minibb_widget_url =
                            'index.php?action=vthread&amp;forum='.intval($kuo_minibb_widget_item['forum_id']).'&amp;topic='.
                            intval($kuo_minibb_widget_item['topic_id']).'#msg'.
                            intval($kuo_minibb_widget_item['topic_last_post_id']);
                        }
                        # 1 = CLEAN URLS IN STYLE OF addon_mod_rewrite_1.php
                        elseif (get_option('kuo_minibb_widget_url_scheme') == 1) {
                            # [TOPIC_TITLE]-[FORUM_ID]-[TOPIC_ID].html
                            $kuo_minibb_widget_url =
                            convertTitle($kuo_minibb_widget_item['topic_title']).'-'.intval($kuo_minibb_widget_item['forum_id']).'-'.
                            intval($kuo_minibb_widget_item['topic_id']).'.html';
                        }
                        # 2 = CLEAN URLS IN STYLE OF addon_mod_rewrite_2.php
                        elseif (get_option('kuo_minibb_widget_url_scheme') == 2) {
                            # [FORUM_TITLE]-[FORUM_ID]/[TOPIC_TITLE]-[TOPIC_ID].html
                            $kuo_minibb_widget_url =
                            convertTitle($kuo_minibb_widget_item['forum_name']).'-'.intval($kuo_minibb_widget_item['forum_id']).'/'.
                            convertTitle($kuo_minibb_widget_item['topic_title']).'-'.
                            intval($kuo_minibb_widget_item['topic_id']).'.html';
                        }
                        # 3 = CLEAN URLS IN STYLE OF addon_mod_rewrite_3.php
                        elseif (get_option('kuo_minibb_widget_url_scheme') == 3) {
                            # [FORUM_TITLE]-[FORUM_ID]/[TOPIC_TITLE]-[TOPIC_ID]/
                            $kuo_minibb_widget_url =
                            convertTitle($kuo_minibb_widget_item['forum_name']).'-'.intval($kuo_minibb_widget_item['forum_id']).'/'.
                            convertTitle($kuo_minibb_widget_item['topic_title']).'-'.
                            intval($kuo_minibb_widget_item['topic_id']).'/';
                        }
                        else {
                            $kuo_minibb_widget_url = NULL;
                        }

                        # BUILDING THE ACTUAL LINK
                        $kuo_minibb_widget_html .= '<li><a href="'.$kuo_minibb_widget_forum_url.$kuo_minibb_widget_url.'"
                        rel="bookmark">'.esc_html(utf8_encode($kuo_minibb_widget_topic)).'</a>'.
                        $kuo_minibb_widget_timestamp.'</li>';
                    }
                    # WHILE ENDS

                    $kuo_minibb_widget_html .= '</ul>';

                    # CLEANING HOUSE
                    mysqli_free_result($kuo_minibb_widget_sql_content);
                    unset($kuo_minibb_widget_sql_content);

                }
                # ENDS: CHECK IF SQL QUERY AND FORUM URL ARE OK
                # BEGINS: AN ERROR OCCURRED
                else {
                    $kuo_minibb_widget_html .= _e('Forum content isn\'t yet available.','kuo_minibb_widget');
                }
                # ENDS: AN ERROR OCCURRED
            }
            else {
                $kuo_minibb_widget_html .= _e('Unable to connect to the miniBB forum.','kuo_minibb_widget');
            }

            echo $args['before_widget'],$kuo_minibb_widget_html,$args['after_widget'];

            unset($kuo_minibb_widget_html);
	    }
        # ENDS: FRONT-END WIDGET FUNCTIONALITY

        # BEGINS: ADMIN-SIDE WIDGET FUNCTIONALITY
	    public function form($instance) {

            # BEGINS: HANDLE TITLE
		    if (isset($instance['title'])) {
			    $title = $instance['title'];
		    }
		    else {
			    $title = NULL;
		    }
            # ENDS: HANDLE TITLE

            # BEGINS: HANDLE SORTING TOPICS
		    if ((isset($instance['sort'])) && (in_array($instance['sort'],array(1,2,3)))) {
			    $sort = intval($instance['sort']);
		    }
		    else {
			    $sort = 1;
		    }
            # BEGINS: HANDLE SORTING TOPICS

            # BEGINS: IF STICKY TOPICS ARE IGNORED
		    if ((isset($instance['ignore_sticky'])) && (!empty($instance['ignore_sticky']))) {
			    $ignore_sticky = 0;
		    }
		    else {
			    $ignore_sticky = 1;
		    }
            # BEGINS: IF STICKY TOPICS ARE IGNORED

            # BEGINS: IF CLOSED TOPICS ARE IGNORED
		    if ((isset($instance['ignore_closed'])) && (!empty($instance['ignore_closed']))) {
			    $ignore_closed = 0;
		    }
		    else {
			    $ignore_closed = 1;
		    }
            # ENDS: IF CLOSED TOPICS ARE IGNORED

            # BEGINS: HANDLE POST/TOPIC COUNT
            # CONTENT COUNT CAN NOT BE ZERO
		    if ((isset($instance['count'])) && (!empty($instance['count']))) {
			    $count = intval($instance['count']);
		    }
		    else {
			    $count = 5;
		    }
            # ENDS: HANDLE POST/TOPIC COUNT

            # BEGINS: HANDLE EXCLUDED FORUMS ID'S
		    if ((isset($instance['exclude_forums'])) && (!empty($instance['exclude_forums']))) {
			    $exclude_forums = filter_var($instance['exclude_forums'],FILTER_SANITIZE_STRING);
		    }
		    else {
			    $exclude_forums = NULL;
		    }
            # ENDS: HANDLE EXCLUDED FORUMS ID'S

            # BEGINS: HANDLE TIMESTAMP
		    if ((isset($instance['show_timestamp'])) && (!empty($instance['show_timestamp']))) {
			    $show_timestamp = 0;
		    }
		    else {
			    $show_timestamp = 1;
		    }
            # ENDS: HANDLE TIMESTAMP

            if (!defined('KUO_MINIBB_WIDGET_SELECTED_SELECTED')) {
                define('KUO_MINIBB_WIDGET_SELECTED_SELECTED',' selected="selected"');
            }

            $kuo_minibb_widget_form = '<p><strong>'.esc_attr('Please select what kind of topics you want to show from the forum, and how many.').'</strong></p>
            <hr />
            <p><label for="'.$this->get_field_id('title').'">'.esc_attr('Title above the content: ').'</label><input class="widefat"
            id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'"
            type="text" value="'.esc_attr($title).'" /></p>
            <p><label for="'.$this->get_field_id('sort').'">'.esc_attr('Show: ').'</label><select id="'.$this->get_field_id('sort').'"
            name="'.$this->get_field_name('sort').'"><option value="1"';

            if (selected($sort,1,0)) {
                $kuo_minibb_widget_form .= KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }

            $kuo_minibb_widget_form .= '>Most recent topics</option><option value="2"';

            if (selected($sort,2,0)) {
                $kuo_minibb_widget_form .= KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }

            $kuo_minibb_widget_form .= '>Most popular topics by message count</option><option value="3"';

            if (selected($sort,3,0)) {
                $kuo_minibb_widget_form .= KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }

            $kuo_minibb_widget_form .= '>Most popular topics by view count</option></select></p>
            <p><label for="'.$this->get_field_id('ignore_sticky').'">'.esc_attr('How about sticky topics: ').'</label><select
            id="'.$this->get_field_id('ignore_sticky').'" name="'.$this->get_field_name('ignore_sticky').'">
            <option value="1"';

            if (selected($ignore_sticky,1,0)) {
                $kuo_minibb_widget_form .= KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }

            $kuo_minibb_widget_form .= '>Exclude sticky topics</option><option value="0"';

            if (selected($ignore_sticky,0,0)) {
                $kuo_minibb_widget_form .= KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }

            $kuo_minibb_widget_form .= '>Include sticky topics as well</option></select></p>
            <p><label for="'.$this->get_field_id('ignore_closed').'">'.esc_attr('How about closed topics: ').'</label><select
            id="'.$this->get_field_id('ignore_closed').'" name="'.$this->get_field_name('ignore_closed').'">
            <option value="1"';

            if (selected($ignore_closed,1,0)) {
                $kuo_minibb_widget_form .= KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }

            $kuo_minibb_widget_form .= '>Exclude closed topics</option><option value="0"';

            if (selected($ignore_closed,0,0)) {
                $kuo_minibb_widget_form .= KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }

            $kuo_minibb_widget_form .= '>Include closed topics as well</option></select></p>
            <p><label for="'.$this->get_field_id('count').'">'.esc_attr('Limit: ').'</label><input class="tiny-text"
            id="'.$this->get_field_id('count').'" name="'.$this->get_field_name('count').'"
            type="text" value="'.esc_attr($count).'" minlength="1" maxlength="4" /></p>
            <p><label for="'.$this->get_field_id('exclude_forums').'">'.esc_attr('Exclude forums (numbers separated with commas): ').'</label><input class="widefat"
            id="'.$this->get_field_id('exclude_forums').'" name="'.$this->get_field_name('exclude_forums').'" type="text"
            value="'.esc_attr($exclude_forums).'" minlength="0" maxlength="255" /><br />
            <label for="'.$this->get_field_id('exclude_forums').'">Example: <code>3,5,6</code></label></p>
            <p><label for="'.$this->get_field_id('show_timestamp').'">'.esc_attr('Show timestamp when topic was created: ').'</label>
            <select id="'.$this->get_field_id('show_timestamp').'" name="'.$this->get_field_name('show_timestamp').'">
                <option value="1"';

            if (selected($show_timestamp,1,0)) {
                $kuo_minibb_widget_form .= KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }

            $kuo_minibb_widget_form .= '>No</option><option value="0"';

            if (selected($show_timestamp,0,0)) {
                $kuo_minibb_widget_form .= KUO_MINIBB_WIDGET_SELECTED_SELECTED;
            }

            $kuo_minibb_widget_form .= '>Yes</option></select><br />
            <label for="'.$this->get_field_id('show_timestamp').'">In format: <code>'.date(get_option('date_format').' '.get_option('time_format')).'</code></label></p>';

            echo $kuo_minibb_widget_form;

            unset($kuo_minibb_widget_form);
	    }
        # ENDS: ADMIN-SIDE WIDGET FUNCTIONALITY

        # BEGINS: UPDATING DATA IN WIDGET
	    public function update($new_instance,$old_instance) {
		    $instance = array();

            # TITLE
            if (!empty($new_instance['title'])) {
		        $instance['title'] = strip_tags($new_instance['title']);
            }
            else {
                $instance['title'] = NULL;
            }

            # ORDER (CAN'T BE EMPTY)
            if (!empty($new_instance['sort'])) {
		        $instance['sort'] = intval(filter_var($new_instance['sort'],FILTER_SANITIZE_NUMBER_INT));
            }
            else {
                $instance['sort'] = 1;
            }

            # IGNORE STICKY TOPICS (CAN'T BE EMPTY)
            if (!empty($new_instance['ignore_sticky'])) {
		        $instance['ignore_sticky'] = 0;
            }
            else {
                $instance['ignore_sticky'] = 1;
            }

            # IGNORE CLOSED TOPICS (CAN'T BE EMPTY)
            if (!empty($new_instance['ignore_closed'])) {
		        $instance['ignore_closed'] = 0;
            }
            else {
                $instance['ignore_closed'] = 1;
            }

            # COUNT (CAN'T BE EMPTY)
            if (!empty($new_instance['count'])) {
		        $instance['count'] = intval(filter_var($new_instance['count'],FILTER_SANITIZE_NUMBER_INT));
            }
            else {
                $instance['count'] = 5;
            }

            # EXCLUDE FORUMS (A COMMA SEPARATED LIST)
            # CAN BE EMPTY
            if (!empty($new_instance['exclude_forums'])) {
		        $instance['exclude_forums'] = filter_var($new_instance['exclude_forums'],FILTER_SANITIZE_STRING);
            }
            else {
                $instance['exclude_forums'] = NULL;
            }

            # SHOW TIMESTAMP YES OR NO (CAN'T BE EMPTY)
            if (!empty($new_instance['show_timestamp'])) {
		        $instance['show_timestamp'] = 0;
            }
            else {
                $instance['show_timestamp'] = 1;
            }

		    return $instance;
	    }
        # ENDS: UPDATING DATA IN WIDGET
    }
    # ENDS: MINIBB_WIDGET CLASS
}
# ENDS: IF MINIBB_WIDGET CLASS EXISTS
