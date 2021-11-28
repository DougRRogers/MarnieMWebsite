<?php  // showcat.php
  if(!isset($_SESSION)) {session_start();}

  require_once '09_MarnieM_Constants.inc';
  require_once '02_mySQLogin.php';
  require_once '10_MarnieM_Functions.php';

  // Connect to database
  // echo "Hostname=[$db_hostname] Username=[$db_username] PWD=[$db_password] DB=[$db_database]";
  $mysqli = new mysqli($db_hostname, $db_username, $db_password, $db_database);
  if ($mysqli->connect_error) {handleSQLProblem( "61_Admin_ShowMarnieMInfo#3|Connect Error", $mysqli->connect_errno, $mysqli->connect_error );}
  $mysqli->set_charset("utf8");

  $selImgSize = isset($_SESSION['ImgSize'])?$_SESSION['ImgSize']:50;
  if( isset($_GET['ImgSize']) ) {  // ImgSize=50
    $_SESSION['ImgSize'] = $selImgSize = safeval( $_GET['ImgSize'], SV_INT);
  }

  $selInfo = isset($_SESSION['selInfo'])?$_SESSION['selInfo']:'All';
  if( isset($_GET['Info']) ) {  // Info=IDCode,Caption,Height
    $_SESSION['selInfo'] = $selInfo = safeval( $_GET['Info'], SV_STRINGLIST);
  }
  // echo "console.log( 'selInfo=[$selInfo]' );";

  $selMedia = isset($_SESSION['selMedia'])?$_SESSION['selMedia']:'All';
  if( isset($_GET['Media']) ) { // Media=Bronze,Neon
    $_SESSION['selMedia'] = $selMedia = safeval( $_GET['Media'], SV_STRINGLIST);
  }
  //  echo "console.log( 'selMedia=[$selMedia]' );";


// ==================================================================
function createMediaCheckboxes( $mysqli, $selMedia ) {
  $mediaValsAndCounts = getPropertyValuesAndCounts( $mysqli, 'Medium' );

  $prefChecked = (strpos( $selMedia, 'All' ) !== false) ? 'checked':'';
  echo "<fieldset class='selectionFieldSet'><legend>Media to Show:</legend>\n";
  echo "<input type='checkbox' class='mediaCB' id='Media_All' name='Media_All' value='All' $prefChecked>  <span class='bold'>All</span> or\n";

  echo "<table class='selectionTable'>\n";
  $numCols = 4;
  $colNum = 0;

  foreach ($mediaValsAndCounts as $medium => $count) {
    $CB_id = 'Media_'.$medium;

    if( $colNum == 0 ) {
      echo "<tr>\n";
    }

    $prefChecked = (strpos( $selMedia, $medium ) !== false) ? 'checked':'';
    echo "  <td><input type='checkbox' class='mediaCB' id='$CB_id' name='$CB_id' value='$medium' $prefChecked> $medium ($count)</td>\n";

    if( $colNum == $numCols ) {
      echo "</tr>\n";
      $colNum = 0;
    } else {
      $colNum++;
    }

  }
  echo "</table>\n\n";
  echo "<button id=btn_mediaUpdateTable type='button'  onclick='updateTable()'>Update Table</button>\n";

  echo "</fieldset>\n\n";
  return $mediaValsAndCounts;
}


function createInfoToDisplayCheckboxes( $mysqli, $selInfo ) {
  $catalogColumns = getColumnNames( $mysqli );

  echo "<fieldset class='selectionFieldSet'><legend>Info to Show:</legend>\n";
  $prefChecked = (strpos( $selInfo, 'All' ) !== false) ? 'checked':'';
  echo "<input type='checkbox' class='infoCB' id='Info_All' name='Info_All' value='All' $prefChecked> <span class='bold'>All</span> or\n";
  $prefChecked = (strpos( $selInfo, 'Basic' ) !== false) ? 'checked':'';
  echo "<input type='checkbox' class='infoCB' id='Info_Basic' name='Info_Basic' value='Basic' $prefChecked> <span class='bold'>Basic Info</span> (ID, Title, Medium, Comments)\n";
  echo "<br>or select info details:\n";

  echo "<table class='selectionTable'>\n";
  $numCols = 4;
  $colNum = 0;

  foreach ($catalogColumns as $colName ) {
    $CB_id = 'Info_'.$colName;

    if( $colNum == 0 ) {
      echo "<tr>";
    }


    $prefChecked = (strpos( $selInfo, $colName ) !== false) ? 'checked':'';
    echo "  <td><input type='checkbox' class='infoCB' id='$CB_id' name='$CB_id' value='$colName' $prefChecked> $colName</td>\n";

    if( $colNum == $numCols ) {
      echo "</tr>\n";
      $colNum = 0;
    } else {
      $colNum++;
    }

  }
  echo "</table>\n\n";
  echo "<button  id=btn_infoUpdateTable type='button'  onclick='updateTable()'>Update Table</button>\n";


  echo "</fieldset>\n\n";
  return $catalogColumns;
}


function createMMInfoTable( $mysqli, $imgSize, $colsToShow, $mediaToShow, $maxNumRows = 10000000  ) {
  $OrigColsToShow = implode( ',', $colsToShow );
  $bShowFilePath = ($OrigColsToShow=='All') || strpos( $OrigColsToShow, 'ImgFilePath') !== false;

  $colNames = '*';  // Default
  if( $colsToShow[0] == 'Basic') {
    $colNames = 'IDCode, ImgFilePath, Title, Medium, Comments';
  } else if( $colsToShow[0] != 'All') {
    array_unshift( $colsToShow, 'ImgFilePath' );  //Make sure this is part of the SELECT, put at front of array
    array_unshift( $colsToShow, 'IDCode' );       //Make sure this is part of the SELECT, put at new front so it's the first
    $colsToShow = array_unique($colsToShow);
    $colNames = implode( ',', $colsToShow );
  }

  $mediaNames = '';
  if( $mediaToShow[0] != 'All') {
    $mediaNames = 'WHERE (';

    foreach( $mediaToShow as $mediumName ) {
      $mediaNames .= "Medium = '$mediumName' OR ";
    }
    $mediaNames = substr($mediaNames, 0, -3); // Remove final ' OR'
    $mediaNames .= ')';
  }


    $query = "SELECT $colNames FROM catalog $mediaNames";
    $result = $mysqli->query( $query );
    if (!$result) { handleSQLProblem( "61_Admin_ShowMarnieMInfo#5", $query, $mysqli->error ); }
    $numInfo = $result->num_rows;
    if( $numInfo == 0 )
      { return; }
    $infoData = array();
    while( $row = $result->fetch_assoc() ) {
      $infoData[] = $row;
    }
    $columnHeads = array_keys( $infoData[0] );

    echo "<table class='infoTable'>\n";
      echo "<tr>\n";
      echo "<th>IDCode</th>";
      echo "<th>Image</th>";
      foreach( $columnHeads AS $cHead ) {
        if( ($cHead=='IDCode') || (!$bShowFilePath && ($cHead=='ImgFilePath')) )
          continue;
        echo "<th>$cHead</th>";
      }
      echo "</tr>\n";

      $rowNum = 0;
      foreach( $infoData AS $iData ) {
        echo "<tr>\n";
        echo "<td>{$iData['IDCode']}<br><button  type='button' onclick='editInfo(\"{$iData['IDCode']}\")'>Edit</button></td>\n";
        echo "<td><img style='max-height:{$imgSize}px;max-width:{$imgSize}px;border: 1px solid black;' src='{$iData['ImgFilePath']}'></td>";
        foreach( $columnHeads AS $cHead ) {
          if($cHead=='IDCode')
            continue;
          $dataStr = $iData[$cHead];
          if( $cHead == 'ImgFilePath' ) {
            if( !$bShowFilePath )
              continue;
            $iXFileName = strrpos( $dataStr, '/' );
            $dataStr = implode(' ', str_split($dataStr, $iXFileName + 1));
          }
          $colData = strlen($dataStr) > 100 ? substr($dataStr,0,100).'...' : $dataStr;
          echo "<td>$colData</td>";
          }

      echo "</tr>\n";
      $rowNum++;
      if( $rowNum >= $maxNumRows )
        { break; }
      }

    echo "</table>\n";
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Marnie Manhattan Catalog Info</title>
    <link rel="shortcut icon" href="/favicon.ico?v=2"/>  
    <link rel='stylesheet' type="text/css" href='ZZ_css/MarnieM.css'>
    <link rel='stylesheet' type="text/css" href='ZZ_css/ZZ_Admin.css'>
    <style> table { font-size: 8pt; }  </style>
  </head>

<body class="TanBody">

<div class="showCatInnerContent">

<h1>Marnie Manhattan Art Catalog</h1>

<div class='horFlexBox'>
  <div>
    <?php $catalogColumns = createInfoToDisplayCheckboxes( $mysqli, $selInfo ); ?>
  </div>
  <div class='vertFlexBox'>
    <div>
    <?php $mediaArray = createMediaCheckboxes( $mysqli, $selMedia ); ?>
    </div>
    <div>
    <fieldset><legend>Image Size:</legend>
    <input type="number" id="selImgSize" name="selImgSize" min='20' max='250' step='10' value='<?php echo "$selImgSize";?>''> pixels &nbsp&nbsp&nbsp<button id=btn_infoUpdateTable type='button' onclick='updateTable()'>Update Table</button>
    </div>
  </div>
</div>

<div id='infoTable'>
<?php
  //$imgSize = 100;
  $selInfoList = explode( ',', $selInfo ); //  ['IDCode', 'Title', 'Medium']
  $selMediaList = explode( ',', $selMedia ); // ['Bronze','Paper']
  createMMInfoTable( $mysqli, $selImgSize, $selInfoList, $selMediaList, 200 );  //200
?>
</div> <!-- infoTable -->
</div> <!-- InnerContent -->


<script>
  const infoCBList = document.querySelectorAll(".infoCB");
  for ( const chkBx of infoCBList ){
    chkBx.addEventListener( 'change', function(e){updateCheckboxes(e);});
  }

  const mediaCBList = document.querySelectorAll(".mediaCB");
  for ( const chkBx of mediaCBList ){
    chkBx.addEventListener( 'change', function(e){updateCheckboxes(e);});
  }

  function updateCheckboxes( event ) {
    var cbID = event.target.id;
    console.log( cbID + ' checkbox changed, is now: ' + (document.getElementById(cbID).checked ? 'checked':'not checked') );

    if( cbID.startsWith('Info_')){
      if( cbID == 'Info_All') {
        //document.getElementById('Info_Basic').checked = (document.getElementById('Info_All').checked ? 'false':'true');
        //console.log( 'Info_Basic should be changed to ' + (document.getElementById('Info_All').checked ? 'false':'true') + '; is now: ' + (document.getElementById('Info_Basic').checked ? 'checked':'not checked') );
      } else if( cbID == 'Info_Basic') {
       // document.getElementById('Info_All').checked = (document.getElementById('Info_Basic').checked ? 'false':'true');
       // console.log( 'Info_All should be changed to ' + (document.getElementById('Info_Basic').checked ? 'false':'true') + '; is now: ' + (document.getElementById('Info_All').checked ? 'checked':'not checked') );
      } else {
        document.getElementById("Info_All").checked = false;
        document.getElementById("Info_Basic").checked = false;
      }

    } else if( cbID.startsWith('Media_')){
      if( cbID != 'Media_All') {
        document.getElementById("Media_All").checked = false;
      }
    } else return;

  }


  function getCheckboxes( category ){
    var checkedList = [];
    if( category=='info') {
      if (document.getElementById('Info_All').checked) {
          checkedList = ['All'];
        } else if (document.getElementById('Info_Basic').checked) {
          checkedList = ['Basic']; //'IDCode', 'Title', 'Medium', 'Comments'];
        } else {
          var infoTypes = [  // ImgFilePath	IDCode	Title	ImgFilePath	Medium	Style	Themes	Caption	Comments	Keywords	Rating	StartDate	FinishDate	Height	Width	Depth	Weight	Price	Location	History	Exhibited	Frame	Other
            <?php
              $catalogColumns = getColumnNames( $mysqli );
              foreach ($catalogColumns as $colName ) {
                echo "'$colName',";
              }
            ?>  ];

          var infoTypeID;
          for ( var infoType of infoTypes ){
            infoTypeID = 'Info_' + infoType;
            //console.log( 'testing checkbox: ' + infoTypeID);
            if (document.getElementById(infoTypeID).checked) {
              checkedList.push(infoType);
            }
          }
        }
        if( checkedList.length == 0 ){  // default, in case nothing checked
          checkedList = ['All'];
        }
      //console.log( 'Info list = [' + checkedList.toString() + ']' )
      return checkedList;

    } else if ( category=='media'){

      if (document.getElementById('Media_All').checked) {
          checkedList = ['All'];
        } else {

          var mediaTypes = [  // ImgFilePath	IDCode	Title	ImgFilePath	Medium	Style	Themes	Caption	Comments	Keywords	Rating	StartDate	FinishDate	Height	Width	Depth	Weight	Price	Location	History	Exhibited	Frame	Other
            <?php
              $mediaValsAndCounts = getPropertyValuesAndCounts( $mysqli, 'Medium' );
              foreach ($mediaValsAndCounts as $medium => $counts) {
                echo "'$medium',";
              }
            ?>  ];

          var mediumID;
          for ( var mediumType in mediaTypes ){
            mediumID = 'Media_' + mediaTypes[mediumType];
            //console.log( 'testing checkbox: ' + mediumID);
            if (document.getElementById(mediumID).checked) {
              checkedList.push(mediaTypes[mediumType]);
            }
          //console.log( 'checkedList = [' + checkedList.toString() + ']');
          }
          if( checkedList.length == 0 ){  // default, in case nothing checked
            checkedList = ['All'];  // default, in case nothing checked
          }
       }
       //console.log( 'Info list = [' + checkedList.toString() + ']' )

        return checkedList;
    }
  }

  function updateTable() {
     //    alert( 'Button pushed for updateTable' );

    var infoString = getCheckboxes( 'info' ).toString();
    var mediaString = getCheckboxes( 'media' ).toString();
    var imgSize = document.getElementById('selImgSize').value;
    //alert( 'In updateTable, URL=[' + 'showcat.php?ImgSize='+imgSize+'&Info='+ infoString +'&Media='+mediaString +']');

      window.location.href = 'showcat.php?ImgSize='+imgSize+'&Info='+ infoString +'&Media='+mediaString;

  }

  function editInfo( IDCode ) {
    window.location.href = '12_ArtWorkEditForm.php?targIDCode=' + IDCode;
  }
</script>

</body>
</html>
