<?php

require_once(dirname (__FILE__) . '/vzaar/Vzaar.php');
    
    //$video_list = Vzaar::getVideoList(get_option("vzaarAPIusername"), false, 20);
    $video_list = Vzaar::searchVideoList(get_option("vzaarAPIusername"), 'true', $title, $labels, $count, $page, $sort);
    
    if( !empty($video_list) ) {
		foreach ($video_list as $i => $video){ 
			try{
				$video_detail = Vzaar::getVideoDetails($video->id, $auth); 
			}catch (Exception $e) {
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
            echo "<div class='vid_container' style='border: 1px solid silver; width: 500px; height: 110px; overflow: hidden; margin: 5px; display: inline-block;'>";
                echo "<div style='height: 150px;'>
                    <div style='border-right: 1px solid silver; height: 110px; width: 20px; float: left; line-height: 110px; display: inline-block; text-align: center; float: left;'><input type='checkbox' value='".$video->id."'/></div>
                    <div style='border-right: 1px solid silver; display: inline-block; height: 110px; text-align: center; line-height: 110px;'><img src='".$video_detail->thumbnailUrl."' onerror='imgError(this)' style='margin: 5px; height: 100px;'/></div>
                    <div style='display: inline-block; height: 110px; width: 327px; margin-right: 3px; position: relative; left: -2px; float: right;'>
                        ";
                        print_r($video);
                        echo "
                    </div>
                </div>";
            echo "</div>";		  
		}
	}else{
		echo 'No data found';
	}

?>