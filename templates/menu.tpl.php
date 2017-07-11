<div class='container'>
   <h2>dotstudioPRO API Key</h2>
   <form action='' method='POST' enctype='multipart/form-data'>
      <table class='form-table widefat'>
         <thead>
         </thead>
         <tbody>
            <tr>
               <td>
                  <b>API Key</b><br/><span class='description'>Your dotstudioPRO API Key</span>
               </td>
               <td>
                  <input type='text' id='dspdev_api_key' name='dspdev_api_key' value='<?php echo get_option("dspdev_auth_api_key") ?: "" ?>' />
               </td>
            </tr>
            <tr>
               <td>
                  <b>Reset API Token</b><br/><span class='description'>Reset the API token if you change your API key</span>
               </td>
               <td>
                  <input type='checkbox' id='dspdev_api_key_reset_token' name='dspdev_api_key_reset_token' value='1' />
               </td>
            </tr>
            <tr>
               <td>
                  <button id='dspdev_api_key' class='button button-primary'>Save</button>
               </td>
            </tr>
         </tbody>
         <tfoot>
         </tfoot>
      </table>
   </form>
    <h2>dotstudioPRO Video Shortcode Generator</h2>
   <form action='' method='POST' enctype='multipart/form-data'>
      <table class='form-table widefat'>
         <thead>
         </thead>
         <tbody>
            <tr>
               <td>
                  <div>
                     <h3>Search:</h3>
                     <input type='text' id='dspdev_video_search' /> <button id='dspdev_video_selector_button' type='button'>Search</button>
                  </div>
                  <div id='dspdev_video_choices'>
                     <h3>Video:</h3>
                     <select id='dspdev_video' class='widefat'></select><br/>
                     <h3>Player Options</h4>
                     <input type='checkbox' id='dspdev_video_autostart' value='1'> Autostart <br/>
                     <input type='checkbox' id='dspdev_video_loop' value='1'> Loop <br/>
                     <h3>Size Options</h4>
                     Width <input id='dspdev_video_width' value='640'> <br/>
                     Height <input id='dspdev_video_height' value='480'> <br/><br/>
                     <button id='dspdev_video_shortcode_generator' type='button' class='button button-secondary'>Generate</button><br/>
                     <br/>
                     <textarea id='dspdev_video_shortcode' rows='3' class='widefat'></textarea>
                  </div>
               </td>
            </tr>
         </tbody>
         <tfoot>
         </tfoot>
      </table>
      <input type='hidden' name='dspdev-save-admin-options' value='1' />
   </form>
</div>