<?php

/*** Get new API token ***/
if(!function_exists('dspdev_get_token')){
    function dspdev_get_token(){
	    $key = get_option('dspdev_auth_api_key');
	    if(!$key) return null;
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.myspotlight.tv/token",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"key\"\r\n\r\n$key\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          return false;
        } else {
          if(json_decode($response)->success){
            return json_decode($response)->token;
          } else {
            return "";
          }
        }
    }
}



function dspdev_grab_token() {
    $token = get_option('dspdev_auth_token');
    $exp = get_option('dspdev_auth_token_exp');

    $ttl = $exp ? $exp - time() : 0;

    // If we have less than a day to go before expiring, renew
    if(!$exp || $ttl < (60*60*24)){
        $token = dspdev_get_token();
        update_option('dspdev_auth_token', $token);
        // Expires in 30 days
        update_option('dspdev_auth_token_exp', time() + (60*60*24*30));
    }
    return $token;
}

/*** Set up search queries for video ids ***/

add_action( 'admin_footer', 'dspdev_custom_post_video_selector' );

function dspdev_custom_post_video_selector() {

    $token = dspdev_grab_token();

    if (!$token) {
        ?>
            <h3>We could not get a token to access our API. Please verify your API key.</h3>
        <?php
        return;
    }

    ?>
    <script type="text/javascript" >
    jQuery(document).ready(function($) {

        jQuery("#dspdev_video_selector_button").click(function(){
            if(jQuery("#dspdev_video_search").length < 1 || jQuery("#dspdev_video_search").val().length < 1) return;

            jQuery("#dspdev_video_selector_button").attr('disabled', true);
            jQuery("#dspdev_video_choices > select").attr('disabled', true);

            var q = jQuery("#dspdev_video_search").val();

            var current = jQuery("#dspdev_video_choices > select > option.current");

            $.ajax({
                url: "https://api.myspotlight.tv/search/videos?q=" + q,
                headers: {"x-access-token": "<?php echo $token; ?>"},
                success: function(response) {
                    jQuery("#dspdev_video_selector_button").attr('disabled', false);
                    jQuery("#dspdev_video_choices > select").attr('disabled', false);
                    if(response.success){
                        console.log(response.data.hits);
                        if(response.data.total > 0){
                            jQuery("#dspdev_video_choices > select").html('');
                            if(current.length > 0) jQuery("#dspdev_video_choices > select").prepend(current);
                            var title = "";
                            response.data.hits.forEach(function(hit){
                                title = hit._source.title;
                                if(current.attr('value') === hit._id+"||"+title) return;
                                jQuery("#dspdev_video_choices > select").append('<option value="'+hit._id+'">'+title+'</option>');
                            });
                        }
                    }
                }
            });

        });

        jQuery("#dspdev_video_shortcode_generator").click(function(){
        	if(jQuery("#dspdev_video").val()){
        		var autostart = jQuery("#dspdev_video_autostart:checked").length > 0 ? "autostart='true'" : "autostart='false'";
        		var loop = jQuery("#dspdev_video_loop:checked").length > 0 ? "loop='true'" : "loop='false'";
        		var width = jQuery("#dspdev_video_width").val() > 0 ? "width='" + jQuery("#dspdev_video_width").val() + "'" : "width='640'";
        		var height = jQuery("#dspdev_video_height").val() > 0 ? "height='" + jQuery("#dspdev_video_height").val() + "'" : "height='480'";
        		var shortcode = "[dspdev_video_shortcode video='" + jQuery("#dspdev_video").val() + "' " + autostart + " " + loop + " " + width + " " + height + " ]";
        		jQuery("#dspdev_video_shortcode").val(shortcode);
        	}
        });
    });
    </script>

    <?php
}

function dspdev_video_shortcode_generator( $atts, $content = null ) {
	extract(shortcode_atts(array(
        "autostart" => 'false',
        "loop" => 'false',
        "width" => '640',
        "height" => '480',
        "video" => '',
    ), $atts));
    return '<iframe width="' . $width . '" height="' . $height . '" src="http://wp.dotstudiopro.com/player/' . $video . '?skin=228b22&autostart=' . $autostart . '&loopplayback=' . $loop . '" scrolling="no" frameborder="0" allowfullscreen></iframe>';
}

add_shortcode("dspdev_video_shortcode", "dspdev_video_shortcode_generator");

function dspdev_save_api_key(){
	if(!empty($_POST['dspdev_api_key'])){
		update_option('dspdev_auth_api_key', $_POST['dspdev_api_key']);
	}
	if(!empty($_POST['dspdev_api_key_reset_token'])){
		$token = update_option('dspdev_auth_token', null);
    	$exp = update_option('dspdev_auth_token_exp', null);
	}

}

add_action("init", "dspdev_save_api_key");


add_action( 'admin_footer', 'dspdev_playlist_list' );

function dspdev_playlist_list(){
    $token = dspdev_grab_token();

    if (!$token) {
        ?>
            <h3>We could not get a token to access our API. Please verify your API key.</h3>
        <?php
        return;
    }

    ?>

    <script type="text/javascript" >
    jQuery(document).ready(function($) {

        jQuery("#dspdev_playlist_selector_button").click(function(){
            if(jQuery("#dspdev_playlist_search").length < 1 || jQuery("#dspdev_playlist_search").val().length < 1) return;

            jQuery("#dspdev_playlist_selector_button").attr('disabled', true);
            jQuery("#dspdev_playlist_choices > #dspdev_playlist").attr('disabled', true);

            var q = jQuery("#dspdev_playlist_search").val();

            var current = jQuery("#dspdev_playlist_choices > select > option.current");

            $.ajax({
                url: "https://api.myspotlight.tv/search?q=" + q,
                headers: {"x-access-token": "<?php echo $token; ?>"},
                success: function(response) {
                    jQuery("#dspdev_playlist_selector_button").attr('disabled', false);
                    jQuery("#dspdev_playlist_choices > select").attr('disabled', false);
                    if(response.success){
                        console.log(response.data.hits);
                        if(response.data.total > 0){
                            jQuery("#dspdev_playlist_choices > #dspdev_playlist").html('');
                            if(current.length > 0) jQuery("#dspdev_playlist_choices > #dspdev_playlist").prepend(current);
                            var title = "";
                            response.data.hits.forEach(function(hit){
                                title = hit._source.title;
                                if(current.attr('value') === hit.slug+"||"+title) return;
                                jQuery("#dspdev_playlist_choices > #dspdev_playlist").append('<option value="'+hit.slug+'">'+title+'</option>');
                            });
                        }
                    }
                }
            });

        });

        jQuery("#dspdev_playlist_shortcode_generator").click(function(){
            if(jQuery("#dspdev_playlist").val()){
                var video_css = jQuery("#dspdev_playlist_video_class").val().length > 0 ? "video_css='" + jQuery("#dspdev_playlist_video_class").val() + "'" : "";
                var show_air_date = parseInt(jQuery("#dspdev_playlist_show_air_date").val()) === 1 ? true : false;
                var air_date_css = jQuery("#dspdev_playlist_air_date_class").val().length > 0 && show_air_date ?  "air_date_css='" + jQuery("#dspdev_playlist_air_date_class").val() + "'" : "";
                var shortcode = "[dspdev_playlist_shortcode slug='" + jQuery("#dspdev_playlist").val() + "' " + video_css+ " " + air_date_css + " show_air_date='" + (show_air_date ? 'true' : 'false') + "' ]";
                jQuery("#dspdev_playlist_shortcode").val(shortcode);
            }
        });
    });
    </script>

    <?php

}

function dspdev_get_playlist_by_channel_slug() {
    return array();
}

function dspdev_playlist_shortcode_generator( $atts, $content = null ) {
    extract(shortcode_atts(array(
        "video_css" => '',
        "show_air_date" => 'false',
        "air_date_css" => '',
        "slug" => '',
    ), $atts));
    if(empty($slug)) return "";

    $video_css = $atts['video_css'];
    $air_date_css = $atts['air_date_css'];
    $show_air_date = json_decode($atts['show_air_date']);

    $template = "<div class='dspdev-playlist-container'>";

    foreach(dspdev_get_playlist_by_channel_slug() as $video) {
        $template .= "<div class='dspdev-playlist-item'>";

            $template .= "<div class='dspdev-playlist-item-video $video_css'>";

                $template .= "<h4>$video->title <i class='fa fa-video dspdev-show-video' data-video='$video->_id'></h4>";

            $template .= "</div>";

            if(!$show_air_date) continue;

            $template .= "<div class='dspdev-playlist-item-air-date $air_date_css'>";

                $template .= "<h5>Air Date: $video->air_date</h5>";

            $template .= "</div>";

        $template .= "</div>";
    }

    $template .= "</div>";

    return $template;
}
