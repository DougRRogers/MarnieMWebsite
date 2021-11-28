<?php  // 12_ArtWorkEditForm.php
  if(!isset($_SESSION)) {session_start();}

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
  $catItemsInfoArray = getCatalogItems( $mysqli, ['IDCode','Title','ImgFilePath','Height','Width'], $mediaArray );
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel='stylesheet' type="text/css" href='ZZ_css/MarnieM.css'>
  <link rel='stylesheet' type="text/css" href='ZZ_css/ZZ_Admin.css'>
  <title>Marnie Manhattan - Art Works</title>
</head>
<body>

<div class='gridWrapper4col'>
<?php
//  $catItemsInfoArray = getCatalogItems( $mysqli, ['IDCode','Title','ImgFilePath','Height','Width'], ['All'] );
  if( !empty($catItemsInfoArray) ) {
    $squareAry = array();
    $vertAry = array();
    $horAry = array();
    $veryVertAry = array();
    $veryHorAry = array();

    foreach( $catItemsInfoArray as $itemInfo ) {
      list($width, $height) = getimagesize( $itemInfo['ImgFilePath'] );
      if( ($width > (0.8 * $height)) && ($width < (1.2 * $height)) ) {
        $squareAry[] = $itemInfo;
      } else if( $width > 2*$height ) {
        $veryHorAry[] = $itemInfo;
      } else if( $width > $height ) {
        $horAry[] = $itemInfo;
      } else if( $width < 0.5 * $height ) {
        $veryVertAry[] = $itemInfo;
      } else {
        $vertAry[] = $itemInfo;
      }

    }

    foreach( $squareAry as $itemInfo ) {
      echo "\n<div class='gridCellDiv'><div class='gridImgDiv'>";
      echo "\n<img class='gridImg' src='{$itemInfo['ImgFilePath']}'>";
      echo "\n<br>{$itemInfo['IDCode']}";
      echo "\n</div></div>";
    }

    foreach( $horAry as $itemInfo ) {
      echo "\n<div class='gridCellDiv'><div class='gridImgDiv'>";
      echo "\n<img class='gridImg' src='{$itemInfo['ImgFilePath']}'>";
      echo "\n<br>{$itemInfo['IDCode']}";
      echo "\n</div></div>";
    }

    foreach( $vertAry as $itemInfo ) {
      echo "\n<div class='gridCellDiv'><div class='gridImgDiv'>";
      echo "\n<img class='gridImg' src='{$itemInfo['ImgFilePath']}'>";
      echo "\n<br>{$itemInfo['IDCode']}";
      echo "\n</div></div>";
    }

    foreach( $veryHorAry as $itemInfo ) {
      echo "\n<div class='gridCellDiv'><div class='gridImgDiv'>";
      echo "\n<img class='gridImg' src='{$itemInfo['ImgFilePath']}'>";
      echo "\n<br>{$itemInfo['IDCode']}";
      echo "\n</div></div>";
    }

    foreach( $veryVertAry as $itemInfo ) {
      echo "\n<div class='gridCellDiv'><div class='gridImgDiv'>";
      echo "\n<img class='gridImg' src='{$itemInfo['ImgFilePath']}'>";
      echo "\n<br>{$itemInfo['IDCode']}";
      echo "\n</div></div>";
    }
  } else {
    echo "<h2>No $targMedium images available at this time</h2>";
  }
?>
</div> <!-- gridWrapper4col -->

</body>
</html>