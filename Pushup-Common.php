<?php
/**
 * PushupSocial is a catch all for plugin functionality
 *
 * @package Pushup
 * @see https://github.com/PushupSocial/pushup-wordpress
 */
class PushupSocial {

    /**
     * @var string
     */
    protected $adminUrl;

    public function __construct()
    {
        if (defined('PUSHUP_OPTIONS_ADMIN_URL')) {
            $this->adminUrl = PUSHUP_OPTIONS_ADMIN_URL;
        }
        if (defined('PUSHUP_PLUGIN_DIRECTORY')) {
            $this->pluginDirectory = PUSHUP_PLUGIN_DIRECTORY;
        }
    }

	/**
     * This checks if the Community ID it is passed is valid (optionally for a given domain)
     *
     * @param string $community_id Network ID of community
     * @return bool
     */
    public function validateCommunityID($community_id) {
        $url = PUSHUP_OPTIONS_API_URL . "/network/communities/?network_id={$community_id}";
        $result = wp_remote_get($url);

        $response = json_decode($result["body"], true);
        return (isset($response["network_id"]));
    }

    /**
     * This checks if the Site ID it is passed is valid (optionally for a given domain)
     *
     * @param string $site_id
     * @return bool
     */
    public function getSiteCommunityID($site_id) {
        $url = PUSHUP_OPTIONS_API_URL . "/network/communities/?third_party_name=wordpress&third_party_id={$site_id}";
        $result = wp_remote_get($url);

        $response = json_decode($result["body"], true);
        return isset($response["network_id"]) ? $response['network_id'] : null;
    }

    /**
     * Returns a new url from id and url
     *
     * @param string $url
     * @param string $queryString ex: ?somevalue=1&othervalue=ten
     * @param string $id
     * @return string
     */
    public function getNetworkIDRoute($url, $queryString, $id = '') {
        return rtrim($url, '/') . '/#/network/' . $id . $queryString;
    }

    /**
     * @return string
     */
    public function getAdminUrl() {
        return $this->adminUrl;
    }

    /**
     * @param string $url url for the admin panel
     * @return void
     */
    public function setAdminUrl($url) {
        $this->adminUrl = $url;
    }

    /**
     * @return string
     */
    public function getPluginDirectory() {
        return $this->pluginDirectory;
    }

    /**
     * @param string $directory
     * @return void
     */
    public function setPluginDirectory($directory) {
        $this->pluginDirectory = $directory;
    }

    public static function registerDeactivationHook() {
        if (! current_user_can( 'activate_plugins' )) {
            return;
        }

        delete_option(PUSHUP_OPTIONS_NAME);
        unregister_setting(PUSHUP_OPTIONS_GROUP, PUSHUP_OPTIONS_NAME);
    }

    public static function wpHeadAction() {
        $pushupSocial = new PushupSocial();
        $pushupConfigured = pushup_boolean_yesno(PUSHUP_OPTION_CONFIGURED);
        if ($pushupConfigured) {
            include_once($pushupSocial->getPluginDirectory() . '/html/snippet.phtml');
        }
    }

    public static function adminInit() {
        register_setting(
            PUSHUP_OPTIONS_GROUP,
            PUSHUP_OPTIONS_NAME,
            array('PushupSocial', 'registerSetting')
        );
    }

    public static function registerSetting($input) {
        $pushupApp = new PushupSocial();
        $configured = isset($input[PUSHUP_OPTION_CONFIGURED])
                          ? $input[PUSHUP_OPTION_CONFIGURED]
                          : null;
        $savedCommunity = isset($input[PUSHUP_OPTION_SAVED_COMMUNITY])
                              ? $input[PUSHUP_OPTION_SAVED_COMMUNITY]
                              : null;
        $siteId = isset($input[PUSHUP_OPTIONS_SITE_ID])
                      ? $input[PUSHUP_OPTIONS_SITE_ID]
                      : null;
        $output = get_option(PUSHUP_OPTIONS_GROUP);
        $output[PUSHUP_OPTION_CONFIGURED] = $configured;
        $output[PUSHUP_OPTION_SAVED_COMMUNITY] = $savedCommunity;
        if($savedCommunity){
            if($pushupApp->validateCommunityID($savedCommunity)){
                $output[PUSHUP_OPTION_CONFIGURED] = "yes";
                $output[PUSHUP_OPTION_COMMUNITY] = $savedCommunity;
            }
            else {
                $output[PUSHUP_OPTION_CONFIGURED] = "no";
                $output[PUSHUP_OPTION_COMMUNITY] = "";
            }
        } else if($siteId){
            $community_id = $pushupApp->getSiteCommunityID($siteId);
            if($community_id != ""){
                $output[PUSHUP_OPTION_CONFIGURED] = "yes";
                $output[PUSHUP_OPTION_COMMUNITY] = $community_id;
                $output[PUSHUP_OPTION_SAVED_COMMUNITY] = $community_id;
            }
            else {
                $output[PUSHUP_OPTION_CONFIGURED] = "no";
                $output[PUSHUP_OPTION_COMMUNITY] = "";
            }
            $output[PUSHUP_OPTIONS_SITE_ID] = $siteId;
        }

        return $output;
    }
}
