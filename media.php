<?php
require_once(dirname(__FILE__) . '/vzaar/Vzaar.php');

function curPageURL()
{
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }

    $trimStart = strlen($pageURL) - stripos($pageURL, "&");

    if (stristr($pageURL, "&")) {
        $pageURL = substr($pageURL, 0, strlen($pageURL) - $trimStart);
    }

    return $pageURL;
}

function hasNextPage($page)
{
    $title = (isset($_REQUEST['vmt']) && $_REQUEST['vmt'] != 'Title') ? $_REQUEST['vmt'] : '';
    $labels = '';
    $count = isset($_REQUEST['spp']) ? $_REQUEST['spp'] : 20;
    $sort = 'desc';
    Vzaar::$secret = get_option("vzaarAPIusername");
    Vzaar::$token = get_option("vzaarAPItoken");
    $video_list = Vzaar::searchVideoList(get_option("vzaarAPIusername"), true, $title, $labels, $count, $page, $sort);
    return !empty($video_list);
}

$title = (isset($_REQUEST['vmt']) && $_REQUEST['vmt'] != 'Title') ? $_REQUEST['vmt'] : '';
$labels = '';
$count = isset($_REQUEST['spp']) ? $_REQUEST['spp'] : 20;
$count = $count > 100 ? 100 : $count;
$page = isset($_REQUEST['vmp']) ? $_REQUEST['vmp'] : 1;
$sort = 'desc';

$baseURL = curPageURL();

?>

<div class="wrap">
<h2 id="page_title">vzaar Hosted Videos</h2>

<div id='loading_filter'><img src='<?php echo(plugins_url('', __FILE__))?>/dialogs/ajax-loader.gif'/> Loading results
    ... please wait!
</div>
<table class="form-table">
    <tr>
        <td>
            <form id="vzaar_filter_form" action="" method="POST">
                Videos per page:
                <input type="text" style="width: 30px; text-align: center;" name="spp" value="<?php echo $count; ?>"/>
                <small>(Max 100)</small>
                &nbsp;&nbsp; Search for title:
                <input type="text" name="vmt" value="<?php echo $title ? $title : ""; ?>"/> &nbsp;&nbsp;
                <input class="button-primary" type="submit" onclick="fadeContent();" value="Filter"/>
                <input class="button-primary" type="button" onclick="deleteSelected();" value="Delete selected"/>
            </form>
        </td>
    </tr>
</table>
<form id="videos">
    <?php
    $baseURL .= "&spp=" . $count;
    $baseURL .= ($title != "" ? "&vmt=" . urlencode($title) : "");

    Vzaar::$secret = get_option("vzaarAPIusername");
    Vzaar::$token = get_option("vzaarAPItoken");
    $video_list = Vzaar::searchVideoList(get_option('vzaarAPIusername'), true, $title, $labels, $count, $page, $sort);

    if (!empty($video_list)) {
        foreach ($video_list as $i => $video) {
            try {
                Vzaar::$secret = get_option("vzaarAPIusername");
                Vzaar::$token = get_option("vzaarAPItoken");
                $video_detail = Vzaar::getVideoDetails($video->id, true);
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }
            $video_detail->description = $video_detail->description ? $video_detail->description : "none";
            echo "<div class='vid_container' style='float: left; border: 1px solid silver; width: 500px; height: 115px; overflow: hidden; margin: 5px; display: inline-block;'>
                    <div style='border-right: 1px solid silver; height: 115px; width: 20px; float: left; line-height: 115px; display: inline-block; text-align: center; float: left;'><input type='checkbox' name='vid' value='" . $video->id . "'/></div>
                    <div style='border-right: 1px solid silver; display: inline-block; height: 115px; text-align: center; line-height: 115px;'><img src='" . $video_detail->thumbnailUrl . "' onerror='imgError(this)' onclick='playerShow(\"" . urlencode($video->title) . "\",\"" . urlencode($video_detail->html) . "\");' style='cursor: pointer; margin: 7px 5px; height: 100px;'/></div>
                    <div style='display: inline-block; height: 115px; width: 327px; margin-right: 3px; position: relative; left: -2px; float: right;'>
                        <style>
                            td{text-align: left;} 
                            td.left{text-align: right;}
                            
                            div.vid_control {display: inline-block;}
                            
                            div.vid_control:hover
                            {
                                text-decoration: underline;
                                cursor: pointer;
                            }
                            
                            div.addToPostControler {visibility: hidden; display: inline-block;}
                            
                            div.addToPostControler:hover
                            {
                                text-decoration: underline;
                                cursor: pointer;
                            }
                            div.vid_delete
                            {
                                background: white;
                                color: red;
                                font-weight: bold;
                                display: inline-block;
                                padding: 0px 3px;
                            }
                            div.vid_delete:hover
                            {
                                background: red;
                                color: white;
                                cursor: pointer;
                            }
                                
                        </style>
                        
                        <table>
                            <tr>
                                <td class='left'>Title:</td>
                                <td>" . $video->title . "</td>
                            </tr>
                            <tr>
                                <td class='left'>Duration:</td>
                                <td>" . $video->duration . " seconds</td>
                            </tr>
                            <tr>
                                <td class='left'>Views:</td>
                                <td>" . $video->playCount . "</td>
                            </tr>
                            <tr>
                                <td class='left'>Media Id:</td>
                                <td>" . $video->id . "</td>
                            </tr>
                        </table>
                        
                        <div style='clear: both; float: right; position: relative; color: #21759B;'><div class='addToPostControler' onclick='addToPost(\"" . $video->id . "\",\"" . urlencode($video->title) . "\",\"" . urlencode($video_detail->description) . "\",\"" . urlencode($video->duration) . "\",\"" . urlencode($video_detail->height) . "\",\"" . urlencode($video_detail->width) . "\",\"" . urlencode($video_detail->html) . "\");'>Add to post | </div><div class='vid_control' onclick='playerShow(\"" . urlencode($video->title) . "\",\"" . urlencode($video_detail->html) . "\");'>View</div> | <div class='vid_control' onclick='editVideo(\"" . $video->id . "\",\"" . urlencode($video_detail->title) . "\",\"" . urlencode($video_detail->description) . "\")'>Edit</div> | <div class='vid_delete' onclick='deleteVideo(" . $video->id . ")'>Delete</div> </div>
                    </div>
                </div>";
        }
    } else {
        echo 'No data found';
    }
    echo "<div class='page_control' style='clear: both;'>";
    //PREVIOUS PAGE
    if ($page > 1) {
        echo "<input type='button' class=\"button-primary\" onclick='redirect(\"" . str_replace(" ", "", urlencode($baseURL . "&tab=vzaarAPIMedia&vmp=" . ($page - 1))) . "\");' style='text-decoration: none;' value='Previous page'/>";
    } else {
        echo "<input type='button' class=\"button-primary\" style='text-decoration: none;' value='Previous page' disabled='disabled'/>";
    }

    //NEXT PAGE BUTTON
    if (hasNextPage($page + 1)) {
        echo "<input type='button' class=\"button-primary\" onclick='redirect(\"" . str_replace(" ", "", urlencode($baseURL . "&tab=vzaarAPIMedia&vmp=" . ($page + 1))) . "\");' style='text-decoration: none;' value='Next page'/>";
    } else {
        echo "<input type='button' class=\"button-primary\" style='text-decoration: none;' value='Next page' disabled='disabled'/>";
    }
    echo "</div>";

    echo '<div id="player_holder" style="display: hidden;"></div>';
    ?>
</form>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
<script src="<?php echo plugin_dir_url(__FILE__); ?>js/jquery.corner.js"></script>
<script type="text/javascript">

    $(document).ready(function ()
    {
        $("#loading_filter").fadeOut("slow");
        $(".vid_container").corner();
        $(".vid_container").fadeIn("slow");
        $(".page_control").corner();
    });

    function imgError(obj)
    {
        obj.src = "<?php echo plugin_dir_url(__FILE__); ?>images/audio_only.png";
    }

    function fadeContent()
    {
        $(".vid_container").fadeOut("slow");
        $(".page_control").fadeOut("slow");
        $("#vzaar_filter_form").fadeOut("slow");
        $("#loading_filter").fadeIn("slow");
    }

    function deleteVideo(id)
    {
        if (confirm("Delete video?"))
        {
            $(".vid_container").fadeOut("slow");
            $(".page_control").fadeOut("slow");
            $("#vzaar_filter_form").fadeOut("slow");
            $("#loading_filter").html("<img src='<?php echo(plugins_url('', __FILE__))?>/dialogs/ajax-loader.gif'/> Deleting videos ... please wait!");
            $("#loading_filter").fadeIn("slow", function ()
            {
                var data = {
                    action:'deleteVzaarVideos',
                    token:"<?php echo get_option('vzaarAPItoken'); ?>",
                    secret:"<?php echo get_option('vzaarAPIusername'); ?>",
                    vids:"vid=" + id
                };

                jQuery.post(ajaxurl, data, function (response)
                {
                    response = response;
                    var backButton = "<input type='button' class='button-primary' onclick='backToList();' style='text-decoration: none;' value='Back to list'/>";
                    $("#loading_filter").html(response + backButton);
                });
            });
        }
    }

    function playerHide()
    {
        $("#page_title").fadeOut("slow");
        $("#player_holder").fadeOut("slow", function ()
        {
            $("#loading_filter").fadeIn("slow");
            $("#page_title").html("vzaar Hosted Media");

            $("#page_title").fadeIn("slow", function ()
            {
                $("#vzaar_filter_form").fadeIn("fast", function ()
                {
                    $(".vid_container").fadeIn("slow");
                    $(".page_control").fadeIn("slow", function ()
                    {
                        $("#loading_filter").fadeOut("slow");
                    });
                });
            });
        });

    }

    function playerShow(title, player, auxButton, pageTitle)
    {
        if (typeof auxButton == 'undefined') auxButton = '';
        if (typeof pageTitle == 'undefined') pageTitle = "Viewing now: " + decodeURIComponent((title + '').replace(/\+/g, '%20'));
        $(".vid_container").fadeOut("slow");
        $("#vzaar_filter_form").fadeOut("slow");
        $(".page_control").fadeOut("slow");
        $("#page_title").fadeOut("slow", function ()
        {
            $("#loading_filter").fadeIn("slow", function ()
            {
                $("#page_title").html(pageTitle).fadeIn("slow");
                var backButton = "<input type='button' class='button-primary' onclick='playerHide();' style='text-decoration: none;' value='Back to list'/>";
                $("#player_holder").html(decodeURIComponent((player + '').replace(/\+/g, '%20')) + "<br/>" + backButton + auxButton).fadeIn("slow");
                $(".back_link").corner();
                $("#player_holder").fadeIn("slow", function ()
                {
                    $("#loading_filter").fadeOut("slow");
                });
            });
        });
    }

    function editVideo(id, title, description)
    {
        title = decodeURIComponent((title + '').replace(/\+/g, '%20'));
        description = decodeURIComponent((description + '').replace(/\+/g, '%20'));

        content = "<form id='videoDetails'>";
        content += "Title: <input style='width: 200px;' type='text' name='title' id='title' value='" + title + "'/><br/><br/>";
        content += "Description: <br/><textarea name='description' rows='10' cols='100' id='description'>" + description + "</textarea><br/>";
        content += "<input hidden='hidden' type='text' name='vid' value='" + id + "'/>";
        content += "</form>";

        var saveButton = "<input type='button' class='button-primary' onclick='saveDetails();' style='text-decoration: none;' value='Save details'/>";

        playerShow(title, content, saveButton, "Editing: " + decodeURIComponent((title + '').replace(/\+/g, '%20')));
    }

    function saveDetails()
    {
        $(".vid_container").fadeOut("slow");
        $(".page_control").fadeOut("slow");
        $("#vzaar_filter_form").fadeOut("slow");
        $("#loading_filter").html("<img src='<?php echo(plugins_url('', __FILE__))?>/dialogs/ajax-loader.gif'/> Saving video details ... please wait!");
        $("#loading_filter").fadeIn("slow", function ()
        {
            var data = {
                action:'updateVzaarVideos',
                token:"<?php echo get_option('vzaarAPItoken'); ?>",
                secret:"<?php echo get_option('vzaarAPIusername'); ?>",
                details:$(document.getElementById("videoDetails")).serialize()
            };

            jQuery.post(ajaxurl, data, function (response)
            {
                $("#loading_filter").html(response + " <small>Redirecting back to list in 3 seconds.</small>");
                setTimeout("closeEditBox()", 3000);
            });
        });
    }

    function redirect(url)
    {
        fadeContent();
        url = decodeURIComponent(url);
        window.location = url;
    }

    function backToList()
    {
        $("#loading_filter").fadeOut("slow", function ()
        {
            $("#loading_filter").html("<img src='<?php echo(plugins_url('', __FILE__))?>/dialogs/ajax-loader.gif'/> Loading results ... please wait!").fadeIn("slow");
        });
        window.location.reload();
    }

    function closeEditBox()
    {
        $("#player_holder").fadeOut("slow", function ()
        {
            $("#page_title").fadeOut("slow");
            $("#loading_filter").fadeOut("slow", function ()
            {
                $("#loading_filter").html("<img src='<?php echo(plugins_url('', __FILE__))?>/dialogs/ajax-loader.gif'/> Redirecting ... please wait!").fadeIn("slow");
            });
        });
        window.location.reload();
    }

    function deleteSelected()
    {
        if (confirm("Delete videos?"))
        {
            $(".vid_container").fadeOut("slow");
            $(".page_control").fadeOut("slow");
            $("#vzaar_filter_form").fadeOut("slow");
            $("#loading_filter").html("<img src='<?php echo(plugins_url('', __FILE__))?>/dialogs/ajax-loader.gif'/> Deleting videos ... please wait!");
            $("#loading_filter").fadeIn("slow", function ()
            {
                var data = {
                    action:'deleteVzaarVideos',
                    token:"<?php echo get_option('vzaarAPItoken'); ?>",
                    secret:"<?php echo get_option('vzaarAPIusername'); ?>",
                    vids:$("#videos").serialize()
                };

                jQuery.post(ajaxurl, data, function (response)
                {
                    response = response;
                    var backButton = "<input type='button' class='button-primary' onclick='backToList();' style='text-decoration: none;' value='Back to list'/>";
                    $("#loading_filter").html(response + backButton);
                });
            });
        }
    }

</script>
</div>