<?php
include 'common.inc';

// load the secret key (if there is one)
$secret = '';
$keys = parse_ini_file('./settings/keys.ini', true);
if( $keys && isset($keys['server']) && isset($keys['server']['secret']) )
  $secret = trim($keys['server']['secret']);
    
$page_keywords = array('HAR Import','Webpagetest','Website Speed Test','Test');
$page_description = "Import test results.";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>WebPagetest - Traceroute diagnostic</title>
        <?php $gaTemplate = 'Traceroute'; include ('head.inc'); ?>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'Home';
            include 'header.inc';
            ?>
            <form name="urlEntry" action="/runtest.php" method="POST" enctype="multipart/form-data" onsubmit="return ValidateInput(this)">
            
            <input type="hidden" name="type" value="traceroute">
            <input type="hidden" name="vo" value="<?php echo $owner;?>">
            <?php
            if( strlen($secret) ){
              $hashStr = $secret;
              $hashStr .= $_SERVER['HTTP_USER_AGENT'];
              $hashStr .= $owner;
              
              $now = gmdate('c');
              echo "<input type=\"hidden\" name=\"vd\" value=\"$now\">\n";
              $hashStr .= $now;
              
              $hmac = sha1($hashStr);
              echo "<input type=\"hidden\" name=\"vh\" value=\"$hmac\">\n";
            }
            ?>

            <h2 class="cufon-dincond_black">Import a HAR file (experimental)....</h2>
            
            <div id="test_box-container">
                <ul class="ui-tabs-nav">
                    <li class="analytical_review"><a href="/">Analytical Review</a></li>
                    <li class="visual_comparison"><a href="/video/">Visual Comparison</a></li>
                    <?php
                    if( $settings['mobile'] )
                        echo '<li class="mobile_test"><a href="/mobile">Mobile</a></li>';
                    ?>
                    <li class="traceroute"><a href="/traceroute">Traceroute</a></li>
                    <li class="import ui-state-default ui-corner-top ui-tabs-selected ui-state-active"><a href="#">Import</a></li>
                </ul>
                <div id="analytical-review" class="test_box">
                    <ul class="input_fields">
                        <li><input type="text" name="url" id="url" value="Host Name/IP Address" class="text large" onfocus="if (this.value == this.defaultValue) {this.value = '';}" onblur="if (this.value == '') {this.value = this.defaultValue;}"></li>
                    </ul>
                </div>
            </div>

            <div id="start_test-container">
                <p><input type="submit" name="submit" value="" class="start_test"></p>
                <div id="sponsor">
                </div>
            </div>
            <div class="cleared"></div>

            </form>
            
            <?php include('footer.inc'); ?>
        </div>
    </body>
</html>
