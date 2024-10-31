<?php
/*
Plugin Name: Pushup Social
Plugin URI: http://pushup.com/
Description: The easiest way to add a social network to your WordPress site. Simply create a new community from the panel, or link an existing community.
Version: 1.6.3
Author: Pushup Social
Author URI: http://pushup.com/
License: BSD 3 Clause
*/

/*
 * Copyright (c) 2014, 2015, Pushup Social
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * - Neither the name of Pushup Social nor the names of its contributors may be
 *   used to endorse or promote products derived from this software without
 *   specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 */

if (!defined('PUSHUP_TEST_RUNNER')) {
    define("PUSHUP_PLUGIN_URL", plugin_dir_url(__FILE__));
    define("PUSHUP_PLUGIN_DIRECTORY", dirname(__FILE__));
    define("PUSHUP_PLUGIN_BASENAME", plugin_basename(__FILE__));

    if(file_exists(PUSHUP_PLUGIN_DIRECTORY . "/local-config.php"))
        require_once(PUSHUP_PLUGIN_DIRECTORY . "/local-config.php");
    else
        require_once(PUSHUP_PLUGIN_DIRECTORY . "/config.php");

    if (!function_exists("add_action")) {
        echo "The easiest way to add a social network to your WordPress site. Simply create a new community from the panel, or link an existing community.";
        exit;
    }
}


/**
 * GLOBAL FUNCTIONS: Available throughout the application
 */

function pushup_get_option($name) {
    $opts = get_option(PUSHUP_OPTIONS_NAME);
    return isset($opts[$name]) ? $opts[$name] : null;
}


function pushup_get_previous_option($name) {
    $opts = get_option("pushupOptionsFields");
    return isset($opts[$name]) ? $opts[$name] : null;
}

function resolve_pushup_options(){

    $options_map = array(   "pushupOptions" => "pushup_options",
                             "pushupCommunityId" => "pushup_community_id",
                             "pushupConfigured" => "pushup_configured",
                             "pushupEnabled" => "pushup_enabled");

    foreach($options_map as $previous_id => $current_id){
        $previous_value = pushup_get_previous_option($previous_id);
        $current_value = pushup_get_option($current_id);
        if ($previous_value && !$current_value){
            pushup_set_option($current_id, $previous_value);
        }
    }

    pushup_set_option(PUSHUP_OPTION_CONFIG_RESOLVED, "yes");
}

function pushup_set_option($name, $value) {
    $opts = get_option(PUSHUP_OPTIONS_NAME);
    if($opts){
        $opts[$name] = $value;
        update_option(PUSHUP_OPTIONS_NAME, $opts, '', 'yes');
    } else {
        $opts[$name] = $value;
        add_option(PUSHUP_OPTIONS_NAME, $opts, '', 'yes');
    }
}

function pushup_boolean_yesno($name) {
    return (pushup_get_option($name) === "yes");
}

function pushup_yesno($bool) {
    return ($bool) ? "yes" : "no";
}

function pushup_validate_yesno($val) {
    return (($val === "yes") ? $val : "no");
}

function load_scripts () {
    wp_enqueue_script('jquery');
}


/**
 * Do not run application if in test
 */
if (defined('PUSHUP_TEST_RUNNER')) {
    return;
}

/**
 * Initialize the Pushup Application
 */
if(!pushup_boolean_yesno(PUSHUP_OPTION_CONFIG_RESOLVED))
    resolve_pushup_options();

require_once(PUSHUP_PLUGIN_DIRECTORY . "/Pushup-Common.php");

$pushupApp = new PushupSocial();
$pushup_configured = pushup_boolean_yesno(PUSHUP_OPTION_CONFIGURED);

add_action("admin_init", array('PushupSocial', 'adminInit'));

add_action('wp_head', array('PushupSocial', 'wpHeadAction'));
add_action('admin_enqueue_scripts', 'load_scripts');
if (is_admin())
    require_once(PUSHUP_PLUGIN_DIRECTORY . "/admin.php");

register_deactivation_hook(__FILE__, array('PushupSocial', 'registerDeactivationHook'));
