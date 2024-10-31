<?php

$pushup_configured = pushup_boolean_yesno(PUSHUP_OPTION_CONFIGURED);

function pushup_unconfigured_action() {
	pushup_template('unconfigured');
}

function pushup_admin_head_plugins_action() {
	add_action('admin_notices', 'pushup_unconfigured_action');
}

function pushup_admin_head_index_action() {
	add_action('admin_notices', 'pushup_unconfigured_action');
}

function pushup_pushup_full_template() {
	pushup_full_template('editor');
}

function pushup_admin_menu_action() {
	add_plugins_page(
		__('Pushup Social'),
		__('Pushup Social'),
		'manage_options',
		PUSHUP_PAGESLUG_CONFIG,
		'pushup_pushup_full_template'
	);
}

function pushup_action_links_filter($links) {
    $links[] = '<a href="' . menu_page_url(PUSHUP_PAGESLUG_CONFIG, false) . '">Settings</a>';
    return $links;
}

if (!$pushup_configured) {
	add_action('admin_head-plugins.php', 'pushup_admin_head_plugins_action');
	add_action('admin_head-index.php', 'pushup_admin_head_index_action');
}

add_action('admin_menu', 'pushup_admin_menu_action');
add_filter('plugin_action_links_' . PUSHUP_PLUGIN_BASENAME, 'pushup_action_links_filter');

function pushup_full_template($name) {
    wp_enqueue_style("pushup.css", PUSHUP_PLUGIN_URL . '/css/pushup.css');
    get_screen_icon();
    pushup_template($name);
}

function pushup_template($name) {
    include(PUSHUP_PLUGIN_DIRECTORY . "/html/{$name}.phtml");
}
