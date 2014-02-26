<?php
/**
 * @package vzaarAPI
 * @version 1.3
 */

/*
    Plugin Name: vzaarAPI
    Plugin URI: http://vzaar.com
    Description: vzaar is the video hosting for business platform. With the official vzaar WordPress plugin you can manage your video from your WordPress admin section. This includes uploading, editing titles, deleting and previewing video. Logging into vzaar.com in order to copy and paste embed code to publish video on your site can be a time consuming process. The official vzaar WordPress plugin allows you to access your vzaar videos from within your WordPress admin and quickly insert a video into a post or page.
    Author: vzaar
    Version: 1.3.24072012
    Author URI: http://vzaar.com/

    Copyright 2012 vzaar (email : support@vzaar.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
    die('You are not allowed to call this resource directly.');
}

ini_set('display_errors', '0');
ini_set('error_reporting', 0);

class vzaarAPI
{
    var $version = '1.4';

    function __construct()
    {
        $this->plugin_name = plugin_basename(__FILE__);
        register_activation_hook($this->plugin_name, array(&$this, 'activate'));
        register_deactivation_hook($this->plugin_name, array(&$this, 'deactivate'));

        add_filter('media_upload_tabs', array(&$this, 'vzaarAPIMedia_tab'));
        add_action('media_upload_vzaarAPIMedia', array(&$this, 'vzaarAPI_tabContentLoad'));
        add_action('plugins_loaded', array(&$this, 'startPlugin'));
        add_shortcode('vzaarmedia', array(&$this, 'vzaarMediaTagExtractor'));
    }

    //Plugin Activation/Deactivation
    function activate()
    {
        //add_action('admin_notices', create_function('', 'echo \'<div id="message" class="info"><p><strong>vzaarAPI plugin has been installed</strong></p></div>\';'));
    }

    function deactivate()
    {
        //add_action('admin_notices', create_function('', 'echo \'<div id="message" class="info"><p><strong>vzaarAPI plugin has been uninstalled</strong></p></div>\';'));
    }

    function vzaarAPIMedia_tab($tabs)
    {
        $newtab = array('vzaarAPIMedia' => __('vzaarAPI', 'vzaarVideos'));
        return array_merge($tabs, $newtab);
    }

    function vzaarAPI_tabContentLoad()
    {
        return wp_iframe(array(&$this, 'vzaarAPI_tabContent'), $errors);
    }

    function hasNextPage($page)
    {
        $title = (isset($_REQUEST['vmt']) && $_REQUEST['vmt'] != 'Title') ? $_REQUEST['vmt'] : '';
        $labels = '';
        $count = isset($_REQUEST['spp']) ? $_REQUEST['spp'] : 20;
        $sort = 'desc';
        $video_list = Vzaar::searchVideoList(get_option("vzaarAPIusername"), 'true', $title, $labels, $count, $page, $sort);
        return !empty($video_list);
    }

    function vzaarAPI_tabContent()
    {
        wp_enqueue_style('thickbox');
        wp_enqueue_style('media');
        wp_enqueue_style('global');
        wp_enqueue_style('wp-admin');
        echo media_upload_header();

        require_once(dirname(__FILE__) . '/vzaar/Vzaar.php');

        include(dirname(__FILE__) . "/media.php");

        echo "<script>
            
            $('.wrap').css('padding','5px');
            
            $('.addToPostControler').css('visibility','visible');
            
            function addToPost(id,title,description,duration,height,width,code)
            {
                var addToPostButton=\"<input type='button' class='button-primary' onclick='addPostTags()' style='text-decoration: none;' value='Add to post'/>\";
                
                title=decodeURIComponent((title+\"\").replace(/\+/g, '%20'));
                description=decodeURIComponent((description+\"\").replace(/\+/g, '%20'));
                height=decodeURIComponent((height+\"\").replace(/\+/g, '%20'));
                width=decodeURIComponent((width+\"\").replace(/\+/g, '%20'));
                code=decodeURIComponent((code+\"\").replace(/\+/g, '%20'));
                
                playerColors=\"<select id='playerColor'>\";
                    playerColors+='<option value=\"black\">Black</option>';
				    playerColors+='<option value=\"blue\">Blue</option>';
				    playerColors+='<option value=\"red\">Red</option>';
				    playerColors+='<option value=\"green\">Green</option>';
				    playerColors+='<option value=\"yellow\">Yellow</option>';
				    playerColors+='<option value=\"pink\">Pink</option>';
				    playerColors+='<option value=\"orange\">Orange</option>';
				    playerColors+='<option value=\"brown\">Brown</option>';
                playerColors+=\"</select>\";
                
                content=\"<center>\"+code+\"</center><br/><br/><table>\";
                    content+=\"<tr><td>Media id:</td><td id='vid'>\"+id+\"</td></tr>\"; 
                    content+=\"<tr><td>Title:</td><td>\"+title+\"</td></tr>\";
                    content+=\"<tr><td>Description:</td><td><div style='border: 1px solid silver; width: 400px; height: 100px;'>\"+description+\"</div></td></tr>\";
                    content+=\"<tr><td>Duration:</td><td>\"+duration+\" seconds</td></tr>\";
                    content+=\"<tr><td>Player height:</td><td><input type='text' id='vidHeight' value='\"+height+\"'/></td></tr>\";
                    content+=\"<tr><td>Player width:</td><td><input type='text' id='vidWidth' value='\"+width+\"'/></td></tr>\";
                    content+=\"<tr><td>Player color:</td><td>\"+playerColors+\"</td></tr>\";
                    content+=\"<tr><td>Player embed code:</td><td><textarea style='width: 400px; height: 100px; border: 1px solid silver;'>\"+code+\"</textarea></td></tr>\";
                content+=\"</table>\";
                
                playerShow('',content,addToPostButton,\"Adding to post: \"+title);               
            }
            
            function addPostTags()
            {
                tags='[vzaarmedia';
                    tags+=' vid=\"'+$('#player_holder').find('#vid').html()+'\"';
                    tags+=' height=\"'+$('#player_holder').find('#vidHeight').val()+'\"';
                    tags+=' width=\"'+$('#player_holder').find('#vidWidth').val()+'\"';
                    tags+=' color=\"'+$('#player_holder').find('#playerColor').val()+'\"';
                tags+=']';
                
                var win=window.dialogArguments || opener || parent || top;
                win.send_to_editor(tags);
            }
        
        </script>";
    }

    function vzaarAPISettings()
    {
        include('dialogs/vzaarAPISettings.php');
    }

    function registerAdminActions()
    {
        //add_options_page('vzaar API Settings', 'vzaar API', 'manage_options', 'vzaarApiSettings', array(&$this, 'vzaarAPISettings'));

        add_menu_page('Vzaar media', 'vzaarAPI', 'manage_options', 'vzaarAPI/dialogs/vzaarAPISettings.php', '', plugins_url('vzaarAPI/images/icon.png'), 99);
        add_submenu_page('vzaarAPI/dialogs/vzaarAPISettings.php', 'vzaarAPI - Settings', 'Settings', 'manage_options', 'vzaarAPI/dialogs/vzaarAPISettings.php');
        add_submenu_page('vzaarAPI/dialogs/vzaarAPISettings.php', 'vzaarAPI -  Media', 'Videos', 'manage_options', 'vzaarAPI/media.php');
        add_submenu_page('vzaarAPI/dialogs/vzaarAPISettings.php', 'vzaarAPI -  Upload', 'Upload', 'manage_options', 'vzaarAPI/upload.php');

    }

    /**
     * Register Application Settings
     */
    function registerApplicationSettings()
    {
        register_setting('vzaarAPI-settings', 'vzaarAPIusername');
        register_setting('vzaarAPI-settings', 'vzaarAPItoken');
    }

    function registerStyles()
    {

    }

    /**
     * Called when plugin started
     */
    function startPlugin()
    {
        add_action('wp_print_styles', array(&$this, 'registerStyles'));

        // Check if we are in the admin area
        if (is_admin()) {
            add_action('admin_menu', array(&$this, 'registerAdminActions'));
            add_action('admin_init', array(&$this, 'registerApplicationSettings'));
            add_action('wp_ajax_checkToken', array(&$this, 'checkToken'));
            add_action('wp_ajax_saveSettings', array(&$this, 'saveSettings'));
            add_action('wp_ajax_deleteVzaarVideos', array(&$this, 'deleteVzaarVideos'));
            add_action('wp_ajax_updateVzaarVideos', array(&$this, 'updateVzaarVideos'));

        } else {
            add_action('wp_head', create_function('', 'echo "\n<meta name=\"vzaarAPI\" content=\"' . $this->version . '\" />\n";'));
        }
    }

    function checkToken()
    {
        $token_h = $_POST['token'];
        $secret_h = $_POST['secret'];

        require_once(dirname(__FILE__) . '/vzaar/Vzaar.php');

        Vzaar::$token = $token_h;
        Vzaar::$secret = $secret_h;

        $response = ((Vzaar::whoAmI()) == $secret_h) ? "Token check OK" : "Token check FAILED";

        echo $response;
    }

    function saveSettings()
    {
        if (!is_null($_POST['username']) && !is_null($_POST['token'])) {
            update_option('vzaarAPIusername', $_POST['username']);
            update_option('vzaarAPItoken', $_POST['token']);
            echo ("Settings saved!");
        } else {
            echo("No username and token were sent");
        }
    }

    function updateVzaarVideos()
    {
        $token_h = $_POST['token'];
        $secret_h = $_POST['secret'];

        require_once(dirname(__FILE__) . '/vzaar/Vzaar.php');

        Vzaar::$token = $token_h;
        Vzaar::$secret = $secret_h;

        if ($token_h && $secret_h) {
            $details = $_POST["details"];
            $details = explode("&", $details);
            foreach ($details as $row => $field) {
                $field = explode("=", $field);
                $dataArr[$field[0]] = $field[1];
            }

            Vzaar::editVideo($dataArr["vid"], $dataArr["title"], $dataArr["description"]);

            echo "Video details updated!";
        } else {
            echo "Wrong API settings! Check TOKEN and SECRET API values!";
        }

        die();
    }

    function deleteVzaarVideos()
    {
        $token_h = $_POST['token'];
        $secret_h = $_POST['secret'];

        require_once(dirname(__FILE__) . '/vzaar/Vzaar.php');

        Vzaar::$token = $token_h;
        Vzaar::$secret = $secret_h;

        $vids = $_POST["vids"];
        $vids = explode("&", $vids);
        foreach ($vids as $row => $vid) {
            $vid = explode("=", $vid);
            $vid = $vid[1];

            if ($vid) {
                $vidDetails = Vzaar::getVideoDetails($vid, true);
                $vidTitle = $vidDetails->title;
                $response = Vzaar::deleteVideo($vid);
                echo "Deleted video '<i>" . $vidTitle . "</i>' with ID:" . $vid . "<br/>";
            }
        }
        die();
    }

    function vzaarMediaTagExtractor($atts)
    {
        extract(shortcode_atts(array(
            'vid' => 'none',
            'height' => 'none',
            'width' => 'none',
            'color' => 'none',
        ), $atts));

        require_once(dirname(__FILE__) . '/vzaar/Vzaar.php');

        Vzaar::$token = get_option("vzaarAPItoken");
        Vzaar::$secret = get_option("vzaarAPIusername");

        $vidDetails = Vzaar::getVideoDetails($vid, true);

        $player = $vidDetails->html;
        $player = str_replace('height="' . $vidDetails->height . '"', 'height="' . $height . '"', $player);
        $player = str_replace('width="' . $vidDetails->width . '"', 'width="' . $width . '"', $player);
        $player = str_replace('name="flashvars" value="', 'name="flashvars" value="border=none&colourSet=' . $color . '&', $player);

        return $player;
    }
}

global $vzaarAPI;
$vzaarAPI = new vzaarAPI();
?>