<?php  // 21_DispFullImages.php
  if(!isset($_SESSION)) {session_start();}
  $Lang = $_SESSION['Lang'] = empty($_SESSION['Lang']) ? 'E' : $_SESSION['Lang'];
  require_once 'ZZ_RS/21_DispFullImages_RS.php';
  require_once '09_MarnieM_Constants.inc';
  require_once '02_mySQLogin.php';
  require_once '10_MarnieM_Functions.php';

  $mysqli = new mysqli($db_hostname, $db_username, $db_password, $db_database);
  if ($mysqli->connect_error) {handleSQLProblem( "61_Admin_ShowMarnieMInfo#3|Connect Error", $mysqli->connect_errno, $mysqli->connect_error );}
  $mysqli->set_charset("utf8");

  $targMedium = 'All'; // Default
  if( isset($_GET['medium']) ) {  // targIDCode=BZ-0001
    if( safeval( $_GET['medium'], SV_ALPHA) ) {
      $targMedium = $_GET['medium'];
      $_SESSION['targMedium'] = $targMedium;
    }
  }
  $mediaArray = array();
  $mediaArray[] = $targMedium;
//echo "targIDCode=[$targIDCode]<br>";
  $galleryList = getCatalogItems( $mysqli, ['IDCode','ImgFilePath','Medium','Title','Height','Width'], $mediaArray );
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>M.Manhattan Artworks</title>
    <link rel='stylesheet' type='text/css' href='ZZ_css/MarnieM.css'>
    <script src="ZZ_js/jquery-3.1.1.min.js"></script>
    <script src="ZZ_js/commonFuncs.js"></script>
   </head>

  <body id='body'>
    <div id='HL-BtnsBox'>
      <span id='HL-ImgSeqNum'>seq</span>
      <br><input type='button' id='btnPrev' value='Previous'>
      <br><input type='button' id='btnPauseResume' value='Pause'>
      <br><input type='button' id='btnNext' value='Next'>
    </div>

    <div id='HL-Title'>Highlight Title</div>
      <div id='HighlightSlides'><!-- begin slide -->
        <div id='HL-ImgDiv'>
          <div id='HL-ImgCaption'></div>
         <!-- <div id='HL-ImgSeqNum'></div> -->
          <div><img id='HL-Img' src='ZZ_Imgs/HomePagePortraitImg.jpg'></div>
        </div>
      </div> <!-- end slide -->
    </div>

    <script> 'use strict';

    var MEDIUM = 0, IDCODE = 1, TITLE = 2, IMGFILEPATH = 3;
    var galList = [];

    <?php
      if( !empty($galleryList) ) {
        $iX = 0;
        foreach( $galleryList as $info ) {
          echo "\ngalList[$iX] = ['{$info['Medium']}','{$info['IDCode']}','{$info['Title']}','{$info['ImgFilePath']}'];";
          $iX++;
        }
      }
    ?>

    var gNumSlides = galList.length;
    var gCurAWIx, gMaxAWIx = gNumSlides - 1;
    var gSlideIndex = -1;
    var gTimeDelay = 5000;  // 5 seconds
    var gPaused = false;
    var gInertvalSet = false;
    var gTimerID;

    document.getElementById('btnPrev').addEventListener('click', function() { jump(-1, true);});
    document.getElementById('btnPauseResume').addEventListener('click', function() { pauseResume('toggle');});
    document.getElementById('btnNext').addEventListener('click', function() { jump(1, true);});

    function pauseResume( action ) {  //console.log( 'In pauseResume,  action = [' + action + ']' );
      var pauseResumeBtn = document.getElementById('btnPauseResume');
      if( gPaused && (action!=='pause') ) {
        gPaused = false;
        pauseResumeBtn.val( 'Pause' );
        pauseResumeBtn.css( 'background-color', 'lightGray' );
        gInertvalSet = false;
        carousel();
      } else {
        clearInterval(gTimerID);
        gPaused = true;
        gInertvalSet = true;
        //gSlideIndex--;
        pauseResumeBtn.val( 'Resume' );
        pauseResumeBtn.css( 'background-color', 'red' );
      }
    }

    function carousel() {
      if( !gPaused ) {
        jump( 1 , false );
        if( !gInertvalSet ) {
          gTimerID = setInterval(carousel, gTimeDelay); // Change image every 2 seconds
          gInertvalSet = true;
        }
      }
    }

    function jump( delta, pauseSlides ) {  //console.log( 'In jump, del=[' + delta + '], pauseSlides=[' + pauseSlides + '], gSlideIx=['+ gSlideIndex+ ']' );
      if( delta === -1 ) {
        gSlideIndex--;
        if ( gSlideIndex < 0 ) {
          gSlideIndex = gMaxAWIx;
        }
      } else { // delta === 1
        gSlideIndex++;
        if (gSlideIndex > gMaxAWIx ) {
          gSlideIndex = 0;
        }
      }
      if( pauseSlides ) {
        pauseResume('pause');
      }
      document.getElementById('HL-Title').innerHTML = galList[gSlideIndex][MEDIUM];
      document.getElementById('HL-ImgCaption').innerHTML = galList[gSlideIndex][TITLE];
      document.getElementById('HL-ImgSeqNum').innerHTML = (gSlideIndex+1)+'/'+gNumSlides;
      document.getElementById('HL-Img').setAttribute( 'src', galList[gSlideIndex][IMGFILEPATH] );
    }

    if( galList.length > 0 ) {
      carousel();
    } else {
      document.getElementById('body').innerHTML = '<h2>No <?php echo "$targMedium"; ?> images available at this time</h2>';
    }
</script>
  </body>
</html>