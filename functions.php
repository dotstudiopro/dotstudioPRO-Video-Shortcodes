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

            var current = jQuery("#dspdev_playlist_choices > #dspdev_playlist > option.current");

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
                                if(current.attr('value') === hit._id) return;
                                jQuery("#dspdev_playlist_choices > #dspdev_playlist").append('<option value="'+hit._id+'">'+title+'</option>');
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
                var shortcode = "[dspdev_playlist_shortcode id='" + jQuery("#dspdev_playlist").val() + "' " + video_css+ " " + air_date_css + " show_air_date='" + (show_air_date ? 'true' : 'false') + "' ]";
                jQuery("#dspdev_playlist_shortcode").val(shortcode);
            }
        });
    });
    </script>

    <?php

}

function dspdev_run_curl_command($curl_url, $curl_request_type, $curl_post_fields, $curl_header)
{
    // Simplify the cURL execution for various API commands within the curl commands class
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $curl_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $curl_request_type,
        CURLOPT_POSTFIELDS => $curl_post_fields,
        CURLOPT_HTTPHEADER => $curl_header
    ));

    $response = curl_exec($curl);
    $err      = curl_error($curl);

    curl_close($curl);
    return (object) compact('response', 'err');
}

function dspdev_get_country_by_ip(){
    /** DEV MODE **/
    // $dev_check = get_option("ds_development_check");
    // $dev_country = get_option("ds_development_country");

    // if($dev_check){
    //     $this->country = $dev_country;
    //     return $this->country;
    // }
    /** END DEV MODE **/

    $token = dspdev_grab_token();

    if(!$token) return false;

    return "US";

    $result = dspdev_run_curl_command("http://api.myspotlight.tv/country",
        "POST", "-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"ip\"\r\n\r\n".dspdev_get_ip()."\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"token\"\r\n\r\n\r\n-----011000010111000001101001--",
        array(
            "cache-control: no-cache",
            "content-type: multipart/form-data; boundary=---011000010111000001101001",
            "x-access-token:".$token
        ));



    if ($result->err) {
        $error = "cURL Error: $err";
    } else {
        $r = json_decode($result->response);
        if($r->success){
            $this->country = $r->data->countryCode;
            return $this->country;
        } else {
            // Maybe log this somewhere?
            return false;
        }
    }
}

function dspdev_get_ip(){

    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
    //check ip from share internet
    $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
    //to check ip is pass from proxy
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
    $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;

}

function dspdev_get_playlist_by_channel_id($id) {
    $country = dspdev_get_country_by_ip();
    if(!$country) return array();
    $token = dspdev_grab_token();
    if(!$token) return array();

    $url = "http://api.myspotlight.tv/channel/".$country."/id/".$id."?detail=partial";

    $result = dspdev_run_curl_command($url,
            "GET", "-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"ip\"\r\n\r\n".dspdev_get_ip()."\r\n-----011000010111000001101001--",
            array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=---011000010111000001101001",
                "postman-token: a917610f-ab5b-ef69-72a7-dacdc00581ee",
                "x-access-token:".$token
            ));
    if ($result->err) {
        $error = "cURL Error: $err";
    } else {
        $r = json_decode($result->response);
        if($r->success){
            return $r->data;
        } else {
            // Maybe log this somewhere?
            return false;
        }
    }
    return array();
}

function dspdev_playlist_shortcode_generator( $atts, $content = null ) {
    extract(shortcode_atts(array(
        "video_css" => '',
        "show_air_date" => 'false',
        "air_date_css" => '',
        "id" => '',
    ), $atts));
    if(empty($atts['id'])) return "";

    $video_css = !empty($atts['video_css']) ? $atts['video_css'] : "";
    $air_date_css = !empty($atts['air_date_css']) ? $atts['air_date_css'] : "";
    $show_air_date = !empty($atts['show_air_date']) ? json_decode($atts['show_air_date']) : "";

    $template = "<div class='dspdev-playlist-container'>";

    $channel = dspdev_get_playlist_by_channel_id($id);

    if(!$channel) return "";



    foreach($channel->playlist as $video) {
        $template .= "<div class='dspdev-playlist-item'>";

            $template .= "<div class='dspdev-playlist-item-video $video_css'>";

                $template .= "<h4>$video->title <img src='" . plugin_dir_url(__FILE__) . "/assets/images/play.png' class='dspdev-show-video' data-title='" . str_replace("'", "&#39;", $video->title) . "' data-video='$video->_id' /></h4>";

            $template .= "</div>";

            if(!$show_air_date) continue;

            $air_date = date('F jS Y', strtotime($video->created_at));

            $template .= "<div class='dspdev-playlist-item-air-date $air_date_css'>";

                $template .= "<h5>Air Date: $air_date</h5>";

            $template .= "</div>";

        $template .= "</div>";
    }

    $template .= "</div>";

    return $template;
}

add_shortcode("dspdev_playlist_shortcode", "dspdev_playlist_shortcode_generator");

add_action('wp_head', 'dspdev_add_iframe_bg_loader');

function dspdev_add_iframe_bg_loader(){
    echo "<style>.dspdev-show-video-modal-iframe {
        background-image: url('" . plugin_dir_url(__FILE__) . "/assets/images/loader.svg');
        background-repeat: no-repeat;
        /* background-attachment: fixed; */
        background-position: center;
    };</style>";
}
add_action('admin_head', 'dspdev_add_token_to_head');

function dspdev_add_token_to_head(){
    $token = dspdev_grab_token();

    if (!$token) return;

    echo "<script>window.dspdev_token = '$token';</script>";
}