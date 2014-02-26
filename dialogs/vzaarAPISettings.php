<?php
settings_fields('vzaarAPI-settings');
?>
<div class="wrap">
    <h2>vzaar API Settings</h2>

    <table class="form-table">
        <tr>
            <td style="width: 120px;">vzaar username:</td>
            <td>
                <input id="vzaarUsername" type="text" style="width: 200px;" value="<?php echo get_option('vzaarAPIusername'); ?>"/>
            </td>
        </tr>

        <tr>
            <td>API token:</td>
            <td>
                <input id="vzaarToken" type="text" style="width: 200px;" value="<?php echo get_option('vzaarAPItoken'); ?>"/>
                &nbsp; <a id="verifyToken" href="#">Verify token</a> |
                <a href="http://vzaar.com/settings/api" target="_blank">Register/Change API token</a>
            </td>
        </tr>
    </table>

    <div class="submit">
        <input id="buttonSaveChanges" class="button-primary" type="button" name="updateoption" value="<?php _e('Save Changes'); ?>"/>
        <label id="status"></label>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($)
    {
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php

        var vzaar = {
            ui:{
                loader:'<img src="<?php echo(plugins_url('', __FILE__))?>/ajax-loader.gif"/>&nbsp;'
            }
        };

        $("#verifyToken").click(function ()
        {
            var data = {
                action:'checkToken',
                token:$("#vzaarToken").val(),
                secret:$("#vzaarUsername").val()
            };

            $("#verifyToken").html(vzaar.ui.loader + 'Checking ...');
            jQuery.post(ajaxurl, data, function (response)
            {
                $("#verifyToken").html(response.replace("0", "") + " - Click to check again!");
            });
        });

        $("#buttonSaveChanges").click(function ()
        {

            var data = {
                action:'storeSettings',
                token:$("#vzaarToken").val(),
                username:$("#vzaarUsername").val()
            };

            $('#buttonSaveChanges').attr('disabled', 'disabled');
            $('#status').html(vzaar.ui.loader + 'Saving...');
            $('#status').show();

            jQuery.post(ajaxurl, data, function (response)
            {
                $('#buttonSaveChanges').removeAttr('disabled');
                $('#status').html(response.replace("0", "")).fadeOut("slow");
            });

        });

    });
</script>