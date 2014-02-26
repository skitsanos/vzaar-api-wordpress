<?php

    function curPageURL() 
    {
        $pageURL = 'http';
        if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") 
        {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        
        $trimStart=strlen($pageURL)-stripos($pageURL,"&");
        
        if (stristr($pageURL,"&"))
        {
            $pageURL=substr($pageURL,0,strlen($pageURL)-$trimStart);
        }

        return $pageURL;
    }

    require_once(dirname (__FILE__) . '/vzaar/Vzaar.php');

    Vzaar::$token = get_option("vzaarAPItoken");
    Vzaar::$secret = get_option("vzaarAPIusername");
    
    $redirect_url = curPageURL();
		
    //Find API Signature
	$uploadSignature = Vzaar::getUploadSignature($redirect_url);
	$signature = $uploadSignature['vzaar-api'];
    
    $title = (isset($_REQUEST['title'])) ? addslashes($_REQUEST['title']) : '';
	$description = (isset($_REQUEST['description'])) ? addslashes($_REQUEST['description']) : '';
    
    if (isset($_GET['guid'])) {
	   $apireply = Vzaar::processVideo($_GET['guid'], $title, $description, 'Wordpress');
	}
?>

<div class="wrap">
    <h2 id="page_title">vzaar API Upload</h2>
    <div id="formContainer">
   <form action="https://<?php echo $signature['bucket'];?>.s3.amazonaws.com/" method="post" enctype="multipart/form-data" onsubmit="updateRedirect()">
            <!--
            <input name="content-type" type="hidden" value="binary/octet-stream" />
            -->
            <input type="hidden" name="acl" value="<?php echo $signature['acl']; ?>">
            <input type="hidden" name="bucket" value="<?php echo $signature['bucket']; ?>">
            <input type="hidden" name="policy" value="<?php echo $signature['policy']; ?>">
            <input type="hidden" name="AWSAccessKeyId" value="<?php echo $signature['accesskeyid']; ?>">
            <input type="hidden" name="signature" value="<?php echo $signature['signature']; ?>">
            <input type="hidden" name="success_action_status" value="201">
            <input type="hidden" id='redirectURLField' name="success_action_redirect" value="<?php echo $redirect_url; ?>&guid=<?php echo $signature['guid']; ?>">
            <input type="hidden" name="key" value="<?php echo $signature['key']; ?>">
            <table>
                <tr>
                    <td>File to upload to vzaar:</td>
                    <td><input name="file" id="uploadFileField" type="file" onchange="checkFileInput();"/></td>
                </tr>
                <tr>
                    <td>Video title:</td>
                    <td><input id='vidTitle' name="title" onchange="checkFileInput();" style="width:  300px;" type="text" /></td>
                </tr>
                <tr>
                    <td>Video description:</td>
                    <td><textarea id='vidDescr' name="description" onchange="checkFileInput();" style="width: 300px; height: 100px;"></textarea></td>
                </tr>
            </table>
            <br />
            <input class="button-primary" id='doUpload' type="submit" disabled='disabled' onclick="hideForm()" value="Upload File">
        </form>
    </div>
</div>   

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
<script src="<?php echo plugin_dir_url(__FILE__); ?>js/jquery.corner.js"></script>
<script type="text/javascript">
    function hideForm()
    {
        $("#formContainer").fadeOut("slow",function(){
            $("#formContainer").html("<img src='<?php echo(plugins_url('',__FILE__))?>/dialogs/ajax-loader.gif'/> Uploading video ... please wait, this might take a few minutes!").fadeIn("slow");
        });
    }
    
    function urlencode(str) {
        return escape(str).replace(/\+/g,'%2B').replace(/%20/g, '+').replace(/\*/g, '%2A').replace(/\//g, '%2F').replace(/@/g, '%40');
    }
    
    function checkFileInput()
    {
        formIsOk=1;
        
        if($('#uploadFileField').val()=='')
        {
            formIsOk=0;
        }
        if($('#vidTitle').val()=='')
        {
            formIsOk=0;
        }
        if($('#vidDescr').val()=='')
        {
            formIsOk=0;
        }
        
        if(formIsOk)
        {
            $('#doUpload').removeAttr('disabled');
        }
    }
    
    function updateRedirect()
    {
        description=$("#vidDescr").val();
        title=$("#vidTitle").val();
        
        title=urlencode(title);
        description=urlencode(description);
        
        $("#redirectURLField").val($("#redirectURLField").val()+"&title="+title+"&decription="+description);
    }
    
    function showUploadCompleteMSG()
    {
        $("#formContainer").fadeOut("slow",function(){
            var backButton="<input type='button' class='button-primary' onclick='backToList();' style='text-decoration: none;' value='Go to video list'/>";
            $("#formContainer").html("Uploading video complete. Video is now processing, it might take a few minutes before you can see your video!<br/><br/>"+backButton).fadeIn("slow");
        });
    }
    
    function backToList()
    {
        $("#formContainer").fadeOut("slow",function() {
           $("#formContainer").html("<img src='<?php echo(plugins_url('',__FILE__))?>/dialogs/ajax-loader.gif'/> Redirecting ... please wait!").fadeIn("slow"); 
        });
        window.location="<?php echo str_replace("vzaarAPI/upload","vzaarAPI/media",curPageURL()); ?>";
    }
    
    <?php
    
        if (isset($_GET['guid'])) 
        {
            ?>showUploadCompleteMSG();<?php
        }
    
    ?>
</script> 