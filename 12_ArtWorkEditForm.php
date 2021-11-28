<?php  // 12_ArtWorkEditForm.php
  if(!isset($_SESSION)) {session_start();}

  require_once '09_MarnieM_Constants.inc';
  require_once '02_mySQLogin.php';
  require_once '10_MarnieM_Functions.php';

  // Connect to database
  // echo "Hostname=[$db_hostname] Username=[$db_username] PWD=[$db_password] DB=[$db_database]";
  $mysqli = new mysqli($db_hostname, $db_username, $db_password, $db_database);
  if ($mysqli->connect_error) {handleSQLProblem( "61_Admin_ShowMarnieMInfo#3|Connect Error", $mysqli->connect_errno, $mysqli->connect_error );}
  $mysqli->set_charset("utf8");

  $targIDCode = '';
  if( !empty($_POST) ) {
    $IDCode = safeval( $_POST['IDCode'], SV_IDCODE);
    $title = empty($_POST['title']) ? '-untitled-' : safeval( $_POST['title'], SV_STRING);
    $medium = empty($_POST['medium']) ? '' : safeval( $_POST['medium'], SV_ALPHA);
    $style = empty($_POST['style']) ? '' : safeval( $_POST['style'], SV_ALPHA);
    $themes = empty($_POST['themes']) ? '' : safeval( $_POST['themes'], SV_ALPHA);
    $keywords = empty($_POST['keywords']) ? '' : safeval( $_POST['keywords'], SV_STRINGLIST);

    $height = empty($_POST['height']) ? 0 : safeval( $_POST['height'], SV_INT);
    $width = empty($_POST['width']) ? 0 : safeval( $_POST['width'], SV_INT);
    $depth = empty($_POST['depth']) ? 0 : safeval( $_POST['depth'], SV_INT);
    $weight = empty($_POST['weight']) ? 0 : safeval( $_POST['weight'], SV_INT);

    $dateStart = empty($_POST['dateStart']) ? '' : safeval( $_POST['dateStart'], SV_DATE);
    $dateFinish = empty($_POST['dateFinish']) ? '' : safeval( $_POST['dateFinish'], SV_DATE);
    $rating = empty($_POST['rating']) ? 'm' : safeval( $_POST['rating'], SV_ALPHA);
    $price = empty($_POST['price']) ? 0 : safeval( $_POST['price'], SV_INT);

    $location = empty($_POST['location']) ? '' : safeval( $_POST['location'], SV_STRING);
    $frame = empty($_POST['frame']) ? 0 : safeval( $_POST['frame'], SV_INT);

    $caption = empty($_POST['caption']) ? '' : safeval( $_POST['caption'], SV_STRING);
    $history = empty($_POST['history']) ? '' : safeval( $_POST['history'], SV_STRING);
    $comments = empty($_POST['comments']) ? '' : safeval( $_POST['comments'], SV_STRING);
    $exhibited = empty($_POST['exhibited']) ? '' : safeval( $_POST['exhibited'], SV_STRING);
    $other = empty($_POST['other']) ? '' : safeval( $_POST['other'], SV_STRING);

    $s_query = "UPDATE catalog SET Title='$title', Medium='$medium', Style='$style', Themes='$themes', ".
    "Keywords='$keywords', Height=$height, Width=$width, Depth=$depth, Weight=$weight, Price=$price, Rating='$rating', ".
    "StartDate='$dateStart', FinishDate='$dateFinish', ".
    "Location='$location', ".
    "Caption='$caption', History='$history', Exhibited='$exhibited', Comments='$comments', ".
    "Frame=$frame, Other='$other' WHERE IDCode='$IDCode'";
 //echo "query(ForSaveNewVals)=[$s_query]<br>";
    $result = $mysqli->query( $s_query );
    if (!$result) {handleSQLProblem( '12_ArtWorkEditForm#2', $s_query, $mysqli->error );}

    if( !empty($_POST['returnIDCode']) ) {
      $returnIDCode = safeval( $_POST['returnIDCode'], SV_STRING);
      if( $returnIDCode != 'none' ) {
        if( $returnIDCode == 'infoTable' ) {
          echo "<script>window.location.href = 'showcat.php';</script>";
        } else {
          echo "<script>window.location.href = '12_ArtWorkEditForm.php' + '?targIDCode=' + '$returnIDCode';</script>";
        }
      }
    }

    $targIDCode = $IDCode;
  } //End of POST values have been set, everything is valid

  if( isset($_GET['targIDCode']) ) {  // targIDCode=BZ-0001
    if( strlen( safeval( $_GET['targIDCode'], SV_IDCODE) ) == 7) {
      $targIDCode = $_GET['targIDCode'];
      $_SESSION['targIDCode'] = $targIDCode;
    }
  }

//echo "targIDCode=[$targIDCode]<br>";

  $query = "SELECT * FROM catalog WHERE IDCode='$targIDCode'";
//echo "query=[$query]<br>";
    $result = $mysqli->query( $query );
    if (!$result) { handleSQLProblem( "12_ArtWorkEditForm#1", $query, $mysqli->error ); }
    $info = $result->fetch_assoc();
    $columnHeads = array_keys( $info );
    if( empty($info['Title']) ) {
      $info['Title'] = '-untitled-';
    }
    $medium3D = ($info['Medium']=='Bronze' || $info['Medium']=='Sculpture' || $info['Medium']=='Pottery');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel='stylesheet' type="text/css" href='ZZ_css/MarnieM.css'>
  <link rel='stylesheet' type="text/css" href='ZZ_css/ZZ_Admin.css'>
  <title>Edit Art Work Info</title>
</head>

<body class='aweBody'>
  <form name='EditAWInfo' id='EditAWInfo' method='POST' action="12_ArtWorkEditForm.php" >
      <input type='hidden' id='returnIDCode' name='returnIDCode' value='none'>
      <div class='content'>
      <H1>Info for <span class='uline'><?php echo "{$info['Title']}"; ?></span></h1>

      <div class='btnBox'>
        <div class='btnsGroupBox'>
          <input type='button' id='cancel' name='cancel' value='Cancel' onclick='cancelChanges()'>
          &nbsp;&nbsp;<input type='submit' id='save' name='save' value='Save Changes'>
          &nbsp;&nbsp;<input type='button' id='newAw' name='newAw' value='Add New' onclick='cancelChanges()'>
        </div>
        <div class='btnsGroupBox'>
          <input type='button' id='prevAw' name='prevAw' value='Prev'>
          &nbsp;&nbsp;<input type='button' id='retToInfoTable' name='retToInfoTable' value='Return to Info Table' onclick='askAboutSave()'>
          &nbsp;&nbsp;<input type='button' id='nextAw' name='nextAw' value='Next'>
        </div>
      </div> <!-- horFlexBox-Even -->


      <div class='infoAndImg'>
        <div class='basicInfo'>
          <div class='basicInfoLine'>
            <div>ID:<input class='input4em' type='text' readonly id='IDCode' name='IDCode' value='<?php echo "{$info['IDCode']}"; ?>'></input></div>  <div>Title:<input class='input12em' type='text' id='title' name='title' value='<?php echo "{$info['Title']}"; ?>'></div>
          </div> <!-- basicInfoLine -->

          <div class='basicInfoLine'> <!-- 'aweMedStyThemes'> -->
            <div>Medium:<input class='input6em' type='text' id='medium' name='medium' value='<?php echo "{$info['Medium']}"; ?>' readonly></div>
<!--
            <div>
              Medium:
              <select id='medium' name='medium' disabled>
              <?php
                $sel = '';
                $mediaList = array_keys( getMediaNamesToCodes() );
                foreach( $mediaList as $mediaName ){
                  $sel = ($mediaName==$info['Medium']) ? "selected='selected'" : '';
                  echo "\n<option value='$mediaName' $sel>$mediaName</option>";
                }
              ?>
              </select>
            </div>
-->
            <div>Style:<input class='input6em' type='text' id='style' name='style' value='<?php echo "{$info['Style']}"; ?>'></div>
          </div>
          <div class='basicInfoLine'>
            <div>Themes:<input class='input24em' type='text' id='themes' name='themes' value='<?php echo "{$info['Themes']}"; ?>'></div>
          </div> <!-- aweMedStyThemes -->

          <div class='basicInfoLine'>
            <div>
              Keywords:
              <br><input class='input24em' type='text' id='keywords' name='keywords' value='<?php echo "{$info['Keywords']}"; ?>'>
            </div> <!--  -->
          </div> <!-- basicInfoLine -->

          <div class='basicInfoLine'>
            <div>
              <div>
                Height (cm):<input class='input2em' type='number' id='height' name='height' value='<?php echo "{$info['Height']}"; ?>'>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Width (cm):<input class='input2em' type='number' id='width' name='width' value='<?php echo "{$info['Width']}"; ?>'>
              </div>
  <?php
    if( $medium3D ) {
    $depth = $info['Depth']; $weight = $info['Weight'];
  echo <<<DEPWT
              <div>
                Depth (cm):<input class='input2em' type='number' id='depth' name='depth' value='{$info['Depth']}'>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Weight (kg):<input class='input2em' type='number' id='weight' name='weight' value='{$info['Weight']}'>
              </div>
  DEPWT;
            }
  ?>
            </div>
          </div> <!-- basicInfoLine -->

          <div class='basicInfoLine'>
            <div>Started:<input class='input8em2mar' type='date' id='dateStart' name='dateStart' value='<?php echo "{$info['StartDate']}"; ?>'></div>
            <div>Finished:<input class='input8em2mar' type='date' id='dateFinish' name='dateFinish' value='<?php echo "{$info['FinishDate']}"; ?>'></div>
          </div> <!-- basicInfoLine -->

          <div class='basicInfoLine'>
            <div>Rating (a-z):<input class='input2em' type='text' id='rating' name='rating' value='<?php echo "{$info['Rating']}"; ?>'></div>
            <div>Price ($):<input class='input4em' type='number' id='price' name='price' value='<?php echo "{$info['Price']}"; ?>'></div>
          </div> <!-- basicInfoLine -->


          <div class='basicInfoLine'>
            <div>Location:   <input class='input16em' type='text' id='location' name='location' value='<?php echo "{$info['Location']}"; ?>'></div>
          </div> <!-- basicInfoLine -->

          <div class='basicInfoLine'>
            <div>Suggested Frame: <input class='input4em' type='number' id='frame' name='frame' value='<?php echo "{$info['Frame']}"; ?>'>  <input type='checkbox' id='chkShowFrame' name='chkShowFrame'>Show frame in image</div>
          </div> <!-- basicInfoLine -->

        </div> <!-- basicInfo (blue) -->


        <div class='imgAndCaption'>
          <div class='imageDiv'>
            <img class='aweImg' src='<?php echo "{$info['ImgFilePath']}"; ?>'>
          </div> <!-- image -->
          <div>
            <span class='F6pt'>Caption:</span>
            <br><input class='input16em' type='text' id='caption' name='caption' value='<?php echo "{$info['Caption']}"; ?>'>
          </div>
          <div>
            <span class='F6pt'>Path:</span>
            <?php echo "{$info['ImgFilePath']}"; ?>
          </div>
        </div> <!-- imgAndCaption (red) -->

      </div> <!-- infoAndImg (green) -->




      <fieldset><legend>History:</legend>
        <textarea class='aweTextArea' id='history' name='history'><?php echo "{$info['History']}"; ?></textarea>
      </fieldset>

      <fieldset><legend>Comments:</legend>
        <textarea class='aweTextArea' id='comments' name='comments'><?php echo "{$info['Comments']}"; ?></textarea>
      </fieldset>

      <fieldset><legend>Exhibited:</legend>
        <textarea class='aweTextArea' id='exhibited' name='exhibited'><?php echo "{$info['Exhibited']}"; ?></textarea>
      </fieldset>

      <fieldset><legend>Other:</legend>
        <textarea class='aweTextArea' id='other' name='other'><?php echo "{$info['Other']}"; ?></textarea>
      </fieldset>

    </div> <!-- content -->
  </form>
</body>
<script>
  var changeDetected = false;
  document.getElementById('cancel').addEventListener('click', function() {
    cancelChanges();}, false);
  document.getElementById('newAw').addEventListener('click', function() {
    loadAw('newAw', '<?php echo "{$info['IDCode']}"; ?>');}, false);

  document.getElementById('prevAw').addEventListener('click', function() {
    loadAw('prev', '<?php echo "{$info['IDCode']}"; ?>');}, false);
  document.getElementById('retToInfoTable').addEventListener('click', function() {
    cancelChanges();}, false);
  document.getElementById('nextAw').addEventListener('click', function() {
    loadAw('next', '<?php echo "{$info['IDCode']}"; ?>');}, false);

  var inputs = document.getElementsByTagName('input');
  for (var index = 0; index < inputs.length; ++index) {
    inputs[index].addEventListener('click', function() {changeDetected = true;}, false);
  }

  function loadAw(type, curIDCode) {
console.log( "In loadAw, about to confirm");
    if( changeDetected && confirm('Save changes?') ) {
      switch( type ){
        case 'prev':
          document.getElementById('returnIDCode').value = '<?php $newIDCode = getIDCode( $mysqli, 'prev', $info['IDCode']); echo $newIDCode;?>';
          break;
        case 'next':
          document.getElementById('returnIDCode').value = '<?php $newIDCode = getIDCode( $mysqli, 'next', $info['IDCode']); echo $newIDCode;?>';
          break;
        case 'newAw':
          document.getElementById('returnIDCode').value = 'new';
          break;
      }
      document.getElementById('EditAWInfo').submit();
      } else {
      switch( type ){
        case 'prev':
          window.location.href = '12_ArtWorkEditForm.php' + '?targIDCode=' + '<?php $newIDCode = getIDCode( $mysqli, 'prev', $info['IDCode']); echo $newIDCode;?>';
          break;
        case 'next':
          window.location.href = '12_ArtWorkEditForm.php' + '?targIDCode=' + '<?php $newIDCode = getIDCode( $mysqli, 'next', $info['IDCode']); echo $newIDCode;?>';
          break;
        case 'newAw':
          window.location.href = '12_ArtWorkEditForm.php' + '?targIDCode=' + 'new';
          break;
      }
    }
  }

  function askAboutSave() {
    console.log( "In askAboutSave, about to confirm");
    if( changeDetected && confirm('Save changes?') ) {
      document.getElementById('returnIDCode').value = 'infoTable';
      document.getElementById('EditAWInfo').submit();
    }
  }

  function cancelChanges()
  {
    window.location.href = 'showcat.php'; //12_ArtWorkEditForm.php';
  }


</script>
</html>