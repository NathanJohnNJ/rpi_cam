<!DOCTYPE html>
<?php
   define('BASE_DIR', dirname(__FILE__));
   require_once(BASE_DIR.'/config.php');
   $config = array();
   $debugString = "";
   $macros = array('start_img','end_img','start_vid','end_vid','end_box','do_cmd','motion_event','startstop');
   $options_ro = array('No rotate' => '0', '90°' => '90', '180°' => '180', '270°' => '270');
   $options_fl = array('None' => '0', 'Horizontal' => '1', 'Vertical' => '2', 'Both' => '3');
   $options_vp = array('Off' => '0', 'On' => '1');
   $options_mx = array('Internal' => '0', 'External' => '1', 'Monitor' => '2');
   
   function user_buttons() {
    $buttonString = "";
	  $buttonCount = 0;
      if (file_exists("userbuttons")) {
		$lines = array();
		$data = file_get_contents("userbuttons");
		$lines = explode("\n", $data);
		foreach($lines as $line) {
			if (strlen($line) && (substr($line, 0, 1) != '#') && $buttonCount < 12) {
				$index = explode(",",$line);
				if ($index !== false) {
					$buttonName = $index[0];
					$macroName = $index[1];
					$className = $index[2];
					if ($className == false) {
						$className = "btn btn-primary";
					}
					if (count($index) > 3) {
						$otherAtt  = $index[3];
					} else {
						$otherAtt  = "";
					}
					$buttonString .= '<input id="' . $buttonName . '" type="button" value="' . $buttonName . '" onclick="send_cmd(' . "'sy " . $macroName . "'" . ')" class="' . $className . '" ' . $otherAtt . '>' . "\r\n";
					$buttonCount += 1;
				}
			}
		}
      }
	  if (strlen($buttonString)) {
		  echo '<div class="container-fluid text-center">' . $buttonString . "</div>\r\n";
	  }
   }

   function getExtraStyles() {
      $files = scandir('css');
      foreach($files as $file) {
         if(substr($file,0,3) == 'es_') {
            echo "<option value='$file'>" . substr($file,3, -4) . '</option>';
         }
      }
   }
   
  
   function makeOptions($options, $selKey) {
      global $config;
      switch ($selKey) {
         case 'flip': 
            $cvalue = (($config['vflip'] == 'true') || ($config['vflip'] == 1) ? 2:0);
            $cvalue += (($config['hflip'] == 'true') || ($config['hflip'] == 1) ? 1:0);
            break;
         default: $cvalue = $config[$selKey]; break;
      }
      if ($cvalue == 'false') $cvalue = 0;
      else if ($cvalue == 'true') $cvalue = 1;
      foreach($options as $name => $value) {
         if ($cvalue != $value) {
            $selected = '';
         } else {
            $selected = ' selected';
         }
         echo "<option value='$value'$selected>$name</option>";
      }
   }

   function makeInput($id, $size, $type='text') {
      global $config, $debugString;
      $value = $config[$id];
      echo "<input type='{$type}' size=$size id='$id' value='$value' style='width:{$size}em;'>";
   }

   function getImgWidth() {
      global $config;
      if($config['vector_preview'])
         return 'style="width:' . $config['width'] . 'px;"';
      else
         return '';
   }
   
   function getLoadClass() {
      global $config;
      if(array_key_exists('fullscreen', $config) && $config['fullscreen'] == 1)
         return 'class="fullscreen" ';
      else
         return '';
   }

   if (isset($_POST['extrastyle'])) {
	  $extra = $_POST['extrastyle'];
      if ((strpos($extra, '/') === false) && file_exists('css/' . $extra)) {
		 $fp = fopen(BASE_DIR . '/css/extrastyle.txt', "w");
		 fwrite($fp, $extra);
		 fclose($fp);
	  }
   }
  
   $mjpegmode = 0;
   $config = readConfig($config, CONFIG_FILE1);
   $config = readConfig($config, CONFIG_FILE2);
   $video_fps = $config['video_fps'];
   $divider = $config['divider'];
  ?>

<html>
  <head>
    <meta name="viewport" content="width=550, initial-scale=1">
    <title><?php echo CAM_STRING; echo BOTTOM_CAM_STRING; ?></title>
    <link rel="stylesheet" href="css/nj.css" />
    <link rel="stylesheet" href="css/style_minified.css" />
    <link rel="stylesheet" href="<?php echo getStyle(); ?>" />
    <script src="js/style_minified.js"></script>
    <script src="js/script.js"></script>
  </head>
  <body onload="setTimeout('init(<?php echo "$mjpegmode, $video_fps, $divider" ?>);', 100);">
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation" style="margin-bottom:20px;">
      <div class="container flex nav-main" style="display:flex">
        <div class="navbar-header" style="display:flex;flex-direction:column;margin-top:10px;margin-bottom:10px;" >
          <a class="navbar-brand" href="#" style="font-size:1.75vmax;margin-left:-10vw;margin-bottom:5px;padding:0px;padding-top:20px;width:max-content;text-shadow:-3px 3px 9px rgba(255,255,255,0.5);"><?php echo CAM_STRING; ?></a>
          <a class="navbar-brand" href="#" style="font-size:1.75vmax;margin-top:8px;text-shadow:-3px 3px 9px rgba(255,255,255,0.5);margin-left:-10vw;letter-spacing:0.19vw;padding:0px;padding-bottom:10px;width:max-content;"><?php echo BOTTOM_CAM_STRING; ?></a>
        </div>
        <div class="navbar-header">
          <a class="navbar-brand navbar-nj" style="margin-left:35vw;margin-top:3.5vh;font-size:3.75vmax;letter-spacing:0.25vw;text-shadow:-3px 3px 9px rgba(255,255,255,0.5);" href="https://njtd.xyz" target="_blank" referrer="no-referrer">NJTD</a>
        </div>
      </div>
    </div>
      <div class="container-fluid text-center liveimage">
        <div style="margin-top:10vh;">
          <img id="mjpeg_dest" <?php echo getLoadClass() . getImgWidth();?>
          <br>
        </div>
        <div id="main-buttons">
          <input id="video_button" type="button" class="btn">
          <input id="image_button" type="button" class="btn">
          <input id="md_button" type="button" class="btn">
          <input id="halt_button" type="button" class="btn btn-danger">
        </div>
      </div>
      <div id="secondary-buttons" class="container-fluid text-center">
        <?php user_buttons(); ?>
        <a href="preview.php" class="btn btn-default">Download Videos and Images</a>
        &nbsp;&nbsp;
        <?php  if($config['motion_external'] == '1'): ?><a href="motion.php" class="btn btn-default">Edit motion settings</a>&nbsp;&nbsp;<?php endif; ?>
        <a href="schedule.php" class="btn btn-default">Edit schedule settings</a>
      </div>
    
      <div class="container-fluid text-center">
        <div class="panel-group" id="accordion" >
          <div class="panel panel-default">
            <div class="panel-heading">
              <h2 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">Camera Settings</a>
              </h2>
            </div>
            <div id="collapseOne" class="panel-collapse collapse">
              <div class="panel-body">
                <table class="settingsTable">
                  <tr>
                    <td><b>Resolutions:</b></td>
                    <td><b>Custom Values:</b><br>
                      Video res: <?php makeInput('video_width', 4, 'number'); ?>x<?php makeInput('video_height', 4, 'number'); ?>px<br>
                      Video fps: <?php makeInput('video_fps', 3, 'number'); ?>recording, <?php makeInput('MP4Box_fps', 3, null, 'number'); ?>boxing<br>
                      FPS divider: <?php makeInput('fps_divider', 3, 'number'); ?><br>
                      Image res: <?php makeInput('image_width', 4, 'number'); ?>x<?php makeInput('image_height', 4, null, 'number'); ?>px<br>
                      <input type="button" value="OK" onclick="set_res();">
                    </td>
                  </tr>
                  <tr>
                    <td>Annotation (max 127 characters):</td>
                    <td>
                      Text: <?php makeInput('annotation', 20); ?><input type="button" value="OK" onclick="send_cmd('an ' + encodeURI(document.getElementById('annotation').value))">
                    </td>
                  </tr>
                  <tr>
                    <td>Annotation size(0-99):</td>
                    <td>
                      <?php makeInput('anno_text_size', 3, 'number'); ?><input type="button" value="OK" onclick="send_cmd('as ' + document.getElementById('anno_text_size').value)">
                    </td>
                  </tr>
                  <tr>
                    <td>Sharpness (-100...100), default 0:</td>
                    <td><?php makeInput('sharpness', 4, 'number'); ?><input type="button" value="OK" onclick="send_cmd('sh ' + document.getElementById('sharpness').value)"></td>
                  </tr>
                  <tr>
                    <td>Contrast (-100...100), default 0:</td>
                    <td><?php makeInput('contrast', 4, 'number'); ?><input type="button" value="OK" onclick="send_cmd('co ' + document.getElementById('contrast').value)">
                    </td>
                  </tr>
                  <tr>
                    <td>Brightness (0...100), default 50:</td>
                    <td><?php makeInput('brightness', 4, 'number'); ?><input type="button" value="OK" onclick="send_cmd('br ' + document.getElementById('brightness').value)"></td>
                  </tr>
                  <tr>
                    <td>Saturation (-100...100), default 0:</td>
                    <td><?php makeInput('saturation', 4, 'number'); ?><input type="button" value="OK" onclick="send_cmd('sa ' + document.getElementById('saturation').value)"></td>
                  </tr>
                  <tr>
                    <td>Rotation, default 0:</td>
                    <td><select onchange="send_cmd('ro ' + this.value)"><?php makeOptions($options_ro, 'rotation'); ?></select></td>
                  </tr>
                  <tr>
                    <td>Flip, default 'none':</td>
                    <td><select onchange="send_cmd('fl ' + this.value)"><?php makeOptions($options_fl, 'flip'); ?></select></td>
                  </tr>
                  <tr>
                    <td>Image quality (0...100), default 10:</td>
                    <td>
                      <?php makeInput('image_quality', 4, 'number'); ?><input type="button" value="OK" onclick="send_cmd('qu ' + document.getElementById('image_quality').value)">
                    </td>
                  </tr>
                  <tr>
                    <td>Preview quality (1...100), default 10:<br>Width (128...1024), default 512:<br>Divider (1-16), default 1:</td>
                    <td>
                      Quality: <?php makeInput('quality', 4); ?><br>
                      Width: <?php makeInput('width', 4); ?><br>
                      Divider: <?php makeInput('divider', 4); ?><br>
                      <input type="button" value="OK" onclick="set_preview();">
                    </td>
                  </tr>
                  <tr>
                    <td>Motion detect mode:</td>
                    <td><select onchange="send_cmd('mx ' + this.value);setTimeout(function(){location.reload(true);}, 1000);"><?php makeOptions($options_mx, 'motion_external'); ?></select></td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div class="panel panel-default" <?php  if($config['motion_external'] == '1') echo "style ='display:none;'"; ?>>
            <div class="panel-heading">
              <h2 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">Motion Settings</a>
              </h2>
            </div>
            <div id="collapseTwo" class="panel-collapse collapse">
              <div class="panel-body">
                <table class="settingsTable">
                  <tr>
                    <td>Noise level (1-255 / >1000):</td>
                    <td>
                      <?php makeInput('motion_noise', 5, 'number'); ?><input type="button" value="OK" onclick="send_cmd('mn ' + document.getElementById('motion_noise').value)">
                    </td>
                  </tr>
                  <tr>
                    <td>Threshold (1-32000):</td>
                    <td>
                      <?php makeInput('motion_threshold', 5, 'number'); ?><input type="button" value="OK" onclick="send_cmd('mt ' + document.getElementById('motion_threshold').value)">
                    </td>
                  </tr>
                  <tr>
                    <td>Delay Frames to detect:</td>
                    <td>
                      <?php makeInput('motion_initframes', 5, 'number'); ?><input type="button" value="OK" onclick="send_cmd('ms ' + document.getElementById('motion_initframes').value)">
                    </td>
                  </tr>
                  <tr>
                    <td>Change Frames to start:</td>
                    <td>
                      <?php makeInput('motion_startframes', 5, 'number'); ?><input type="button" value="OK" onclick="send_cmd('mb ' + document.getElementById('motion_startframes').value)">
                    </td>
                  </tr>
                  <tr>
                    <td>Still Frames to stop:</td>
                    <td>
                      <?php makeInput('motion_stopframes', 5, 'number'); ?><input type="button" value="OK" onclick="send_cmd('me ' + document.getElementById('motion_stopframes').value)">
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
          <div class="panel panel-default">
            <div class="panel-heading">
              <h2 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">Help</a>
              </h2>
            </div>
            <div id="collapseThree" class="panel-collapse collapse">
              <div class="panel-body">
              <b>RPi Cam Web App</b><br>
                <small>- My project in which I am reworking the front end of RPi Cam Web Interface with ReactJS for my own personal set up. -</small><br><br>
                <b>Github:</b> <a href="https://github.com/NathanJohnNJ/RPi-Cam-Web-App" target="_blank">https://github.com/NathanJohnNJ/RPi-Cam-Web-App</a><br><br>
                <b>RPi Cam Web Interface</b><br>
                <small>- The original inspiration behind my project -</small><br><br>
                <b>Github:</b> <a href="https://github.com/silvanmelchior/RPi_Cam_Web_Interface" target="_blank">https://github.com/silvanmelchior/RPi_Cam_Web_Interface</a><br>
                <b>Forum:</b> <a href="http://www.raspberrypi.org/forums/viewtopic.php?f=43&t=63276" target="_blank">http://www.raspberrypi.org/forums/viewtopic.php?f=43&t=63276</a><br>
                <b>Wiki:</b> <a href="http://elinux.org/RPi-Cam-Web-Interface" target="_blank">http://elinux.org/RPi-Cam-Web-Interface</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php if ($debugString != "") echo "$debugString<br>"; ?>
  </body>
</html>