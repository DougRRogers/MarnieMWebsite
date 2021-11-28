<?php // 10_MarnieM_Functions.php
  if(!isset($_SESSION)) { session_start(); }
  $Lang = $_SESSION['Lang'] = empty($_SESSION['Lang']) ? 'E' : $_SESSION['Lang'];
  require_once '09_MarnieM_Constants.inc';

function getMediaCodesToNames() {
  return [ 'AL' => 'Aluminum', 'BZ' => 'Bronze',      'ET' => 'Etchings',
           'GF' => 'Graffiti', 'JW' => 'Jewelry',     'MN' => 'Miniatures',
           'NE' => 'Neon',     'OI' => 'Oil',         'PH' => 'Photographs',
           'PP' => 'Paper',    'PX' => 'Plexiglass',  'PY' => 'Pottery',
           'SC' => 'Sculpture','SS' => 'Silkscreen',  'SL' => 'Silver',
           'WC' => 'Woodcuts' ];
  }

function getCatalogItems( $mysqli, $propsArray, $mediaArray ) {
  $csvPropsList = implode( ',', $propsArray );
  $catItemsInfoArray = array();

  $mediaNames = '';
  if( $mediaArray[0] != 'All') {
    $mediaNames = 'WHERE (';

    foreach( $mediaArray as $mediumName ) {
      $mediaNames .= "Medium = '$mediumName' OR ";
    }
    $mediaNames = substr($mediaNames, 0, -3); // Remove final ' OR'
    $mediaNames .= ')';
  }

  $query = "SELECT $csvPropsList FROM catalog $mediaNames";
  $result = $mysqli->query( $query );
//echo "In getCatalogItems query=[$query]";
  if (!$result) { handleSQLProblem( "10_MarnieM_Functions#5", $query, $mysqli->error ); }
  $numInfo = $result->num_rows;
  if( $numInfo == 0 )
    { return $catItemsInfoArray; }

  while( $row = $result->fetch_assoc() ) {
    if( array_key_exists('Title', $row) && empty($row['Title']) ) {
      $row['Title'] = '-untitled-';
    }
    $catItemsInfoArray[] = $row;
  }
  return $catItemsInfoArray;
}

function getMediaNamesToCodes() {
  return array_flip( getMediaCodesToNames() );
}

function getMediaNameFromCode( $mediaCode ){
  $mediaCodesToNames =  getMediaCodesToNames();
  return $mediaCodesToNames[$mediaCode];
}

function getMediaCodeFromName( $mediaName ){
  $mediaNamesToCodes =  getMediaNamesToCodes();
  return $mediaNamesToCodes[$mediaName];
}

function getCatalogedMediaCode( $type, $curMediaCode, $mediaTypesCataloged ){
  $curMediaName = getMediaNameFromCode($curMediaCode);
  $mediaIx = 0;
  $lastMediaIx = count($mediaTypesCataloged) - 1;
  foreach ( $mediaTypesCataloged as $val ) {
    if( $curMediaName == $val ) {
      break;
    }
    $mediaIx++;
  }

  if( $type == 'prev') {
    $mediaIx = ( $mediaIx > 0 ) ? $mediaIx - 1 : $lastMediaIx;
  } else if( $type == 'next') {
    $mediaIx = ( $mediaIx < $lastMediaIx ) ? $mediaIx + 1 : 0;
  }

  return getMediaCodeFromName( $mediaTypesCataloged[$mediaIx] );
}

// ==================================================================
function getIDCode( $mysqli, $type, $curIDCode ){
  $mediaValsAndCounts = getPropertyValuesAndCounts( $mysqli, 'Medium' );
  $mediaTypesCataloged = array_keys( $mediaValsAndCounts );
  $curMediaCode = substr($curIDCode, 0, 2);
  $curIndex = intval( substr($curIDCode, 3, 4) );

  if( $type == 'prev') {
    if( $curIndex > 1 ) {
      return $curMediaCode . '-' . str_pad(($curIndex - 1), 4, '0', STR_PAD_LEFT);
    } else {
      $prevMediaCode = getCatalogedMediaCode( $type, $curMediaCode, $mediaTypesCataloged );
      $num = $mediaValsAndCounts[ getMediaNameFromCode($prevMediaCode) ];
      return $prevMediaCode . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

  } else if( $type == 'next') {
    $numMediaType = $mediaValsAndCounts[ getMediaNameFromCode($curMediaCode) ];
    //$numMediaType = 3;
    if( $curIndex < $numMediaType ) {
      return $curMediaCode . '-' . str_pad(($curIndex + 1), 4, '0', STR_PAD_LEFT);
    } else {
      $nextMediaCode = getCatalogedMediaCode( $type, $curMediaCode, $mediaTypesCataloged );
      $num = 1;
      return $nextMediaCode . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

  } else if( $type == ' new') {
    $numMediaType = $mediaValsAndCounts[ getMediaNameFromCode($curMediaCode) ];
    return $curMediaCode . '-' . str_pad(($numMediaType + 1), 4, '0', STR_PAD_LEFT);
  } else {
    return $curIDCode;
  }
}

// ==================================================================
function getPropertyValuesAndCounts( $mysqli, $propertyType ) {
  $s_query = "SELECT $propertyType FROM catalog"; // ORDER BY $propertyType";
// echo "\n <!-- in getPropertyValuesAndCounts s_query=[$s_query] -->";
        $result = $mysqli->query( $s_query );
  if (!$result) {handleSQLProblem( "MM_Funcs|getPropertyValuesAndCounts", $s_query, $mysqli->error );}

  $propertyCounts = array();
  while( $row = $result->fetch_array() ) {
    if( array_key_exists($row[0], $propertyCounts) ) {
      $propertyCounts[$row[0]]++;
    } else {
      $propertyCounts[$row[0]] = 1;
    }
  }
  ksort( $propertyCounts ); // Sort by keys
  // foreach( $propertyCounts as $key=>$value)
  //   echo "\n <!-- in getPropertyValuesAndCounts [$key]=[$value] -->";

  return $propertyCounts;
}

// ==================================================================
function getColumnNames( $mysqli ) {
  $colNames = array();
  $s_query = "SHOW COLUMNS FROM catalog";
  $result = $mysqli->query( $s_query );
  while($row = mysqli_fetch_array($result)){
    $colNames[] = $row['Field'];
  }
  return $colNames;
}










// ==================================================================
function htmlspecialchars_NotAmp( $string ) {
  return str_replace(array('"', "'", "<", ">"), array('&quot;', '&#039;', "&lt;", "&gt;"), $string);
     //  Not done:  & (ampersand)	&amp;
}

// ==================================================================
function stripHTTPPrefix( $string ) {
  return str_replace(array("http//:", "http//", "http/", "http"), "", $string);
}

// ==================================================================
function replNLwBR( $string ) {
  return str_replace(array("\r\n", "\r", "\n"), "<br>", $string);
}

function replBRwNL( $string ) {
  return str_replace("<br>", "\n", $string);
}

// ==================================================================
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

// ==================================================================
function getRandHexColor( $colorRange ) {
  do {
    $R1 = mt_rand(0,15);
    $R2 = mt_rand(0,15);
    $G1 = mt_rand(0,15);
    $G2 = mt_rand(0,15);
    $B1 = mt_rand(0,15);
    $B2 = mt_rand(0,15);

    $Y = 0.375 * ($R1*16 + $R2) + 0.5 * ($G1*16 + $G2) + 0.125 * ($B1*16 + $B2); // Lumina = 0.375 R + 0.5 G + 0.125 B

    if( $colorRange == 'bright' ) {
      $result = ($Y > 128);
    } else if( $colorRange == 'pale' ) {
      $result = ($Y < 128);
    }
  } while( !$result );


  $availClrChars = ['0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F'];
  return '#'.$availClrChars[$R1].$availClrChars[$R2].$availClrChars[$G1].$availClrChars[$G2].$availClrChars[$B1].$availClrChars[$B2];
}

// ==================================================================
// ==================================================================
function newlineToHTMLbr( $string ) {
  $string = str_replace(array("\r\n", "\r", "\n"), '<br>', $string);
  return $string;
}

function newlineTo3Spaces( $string ) {
  $string = str_replace(array("\r\n", "\r", "\n"), '   ', $string);
  return $string;
}

// ==================================================================
function formatPhoneNum( $rawPhoneNum ) {
  if( empty($rawPhoneNum) ) return $rawPhoneNum;
  $numLen = strlen( $rawPhoneNum );
  if( $rawPhoneNum[0] == '0' ) {
    $rawPhoneNum = substr( $rawPhoneNum, 1 );
  }
  if( $numLen == 10 ) {
    $formattedPhoneNum = '0' . substr( $rawPhoneNum, 0, 3 ) . ' ' . substr( $rawPhoneNum, 3, 3 ) . ' ' . substr( $rawPhoneNum, 6 );
  } else if( $numLen == 9 ) {
    $formattedPhoneNum = '0' . substr( $rawPhoneNum, 0, 2 ) . ' ' . substr( $rawPhoneNum, 2, 3 ) . ' ' . substr( $rawPhoneNum, 5 );
  } else {
    $formattedPhoneNum = '0' . $rawPhoneNum;
  }
  return $formattedPhoneNum;
}

// ==================================================================
function strippedForNums( $formattedPhoneNum, $stopAfterEndOfFirstNums = false ) {
//echo "\n <br> strippedForNums: formattedPhoneNum = [$formattedPhoneNum] \n";
  $strippedNum = '';
  $digits = '0123456789';
  $len = strlen($formattedPhoneNum);
  $firstNumFound = false;
  for( $iX = 0; $iX < $len; $iX++ ) {
    $char = $formattedPhoneNum[$iX];
//echo "\n <br> char = [$char] strippedForNums: strippedNum = [$strippedNum] \n";
    if( ord($char) >= 48 &&  ord($char) <= 57 ) {    // '0'=48  '9'=57
      $strippedNum .= $char;
      $firstNumFound = true;
    } else if( $stopAfterEndOfFirstNums && $firstNumFound ) {
      break;
    }
  }
//echo "\n <br> strippedForNums: strippedNum = [$strippedNum]\n";
  if( !empty($strippedNum) && $strippedNum[0] == '0' ) {
    $strippedNum = substr( $strippedNum, 1 );
  }
  return $strippedNum;
}

// ==================================================================
function strippedOfSpaces( $textStr ) {
//echo "\n <br> strippedOfSpaces: textStr = [$textStr] \n";
  $strippedText = '';
  $len = strlen($textStr);
  for( $iX = 0; $iX < $len; $iX++ ) {
    $char = $textStr[$iX];
    if( ord($char)!=32 ) {    // '0'=48  '9'=57  space=32
      $strippedText .= $char;
    }
  }
//echo "\n <br> strippedOfSpaces: strippedText = [$strippedText] \n";
  return $strippedText;
}

// ==================================================================
function getQueryListStrFromListStr( $listStr, $delim ) {
  $queryListString = str_replace( $delim, "','", $listStr ); // eg. Convert  CD,CDF,ZDE ...   to   CD','CDF','ZDE
  $queryListString = "'" . $queryListString . "'";           //     Put quotes on each end to create  'CD','CDF','ZDE'
  return $queryListString;
}


// ==================================================================
function getQueryListStrFromList( $list ) {
  $queryListString = implode("','", $list);
  $queryListString = $queryListString . "'";
  if( $queryListString[1]==",") {
    $queryListString = substr( $queryListString, 2 );
  }
  if( $queryListString[0]!="'") {
    $queryListString = "'" . $queryListString;
  }
  return $queryListString;
}

// ==================================================================
// Handy one liner to parse a CSV file into an array: $csv = array_map('str_getcsv', file('data.csv'));
function MM_csv_to_array( $filename='', $delimiter=',' )
{
//echo "DB: MM_csv_to_array: filename=[$filename], locale='vi_VN.UTF-8'<br>\n";
  setlocale( LC_CTYPE, 'vi_VN.UTF-8' );
  if( !is_readable($filename) )
    { echo "DB: filename=[$filename] is not readable<br>\n"; return FALSE; }

  $header = NULL;
  $data = array();
  if( ($handle = fopen($filename, 'r')) !== FALSE ) {
    while (($row = fgetcsv($handle, 5000, $delimiter)) !== FALSE)
    {
      if( !$header ) {
        $header = $row;
//echo "DB: MM_csv_to_array#5: header[0]=[{$header[0]}]<br>\n";
//echo "DB: MM_csv_to_array#5: header[1]=[{$header[1]}]<br>\n";
//echo "DB: MM_csv_to_array#5: header[2]=[{$header[2]}]<br>\n";
      } else {
        $data[] = array_combine($header, $row);
//echo "DB: MM_csv_to_array#7: row[0]=[{$row[0]}]<br>\n";
      }
    }
    fclose($handle);
  }
  return $data;
}


// ==================================================================
function getNextImgIndex( $bsnsID, $imgType )
{  // $imgType  =  Bsns_  or Prod_
//echo "DB:getNextImgIndex: BsnsID=['$bsnsID'], imgType=[$imgType]<br>\n";
  $bsnsWebDirPath = $bsnsID;  //'cuahang'.$bsnsID;
  $existingBsnsImgsList = glob( $bsnsWebDirPath."/$imgType*.jpg" );
  if( empty($existingBsnsImgsList) )
    { return 1; }
  $maxIx = 0;
  foreach( $existingBsnsImgsList AS $existingBsnsImg ) {
    $ixImgTypeText = strrpos ( $existingBsnsImg, $imgType );
    $imgNumAndJPG = substr( $existingBsnsImg, $ixImgTypeText + strlen($imgType) );
    $ixDot = strrpos( $imgNumAndJPG, '.' );
    $imgNum = substr( $imgNumAndJPG, 0, $ixDot );
    $curLastIndex = intval($imgNum);
    if( $curLastIndex > $maxIx )
      { $maxIx = $curLastIndex; }
//echo "DB:getNextImgIndex: lastImgFileName=[$lastImgFileName], ixImgTypeText=[$ixImgTypeText], imgNumAndJPG=[$imgNumAndJPG],  ixDot=[$ixDot],  imgNum=[$imgNum],  curLastIndex=[$curLastIndex]<br>\n";
  }
//echo "DB:getNextImgIndex: maxIx=[$maxIx]<br>\n";
  return $maxIx + 1;
}

// =======================================================================
function padLeadingZeros( $input, $numZeros = 4 )
{
  $lenInput = strlen( $input );
  $numZerosNeeded = $numZeros - $lenInput;
//echo "DB:getNextImgIndex: input=[$input], lenInput=[$lenInput], numZerosNeeded=[$numZerosNeeded]<br>\n";
  if( $numZerosNeeded <= 0 )
    return $input;
  $zerosNeeded = substr('0000000000', 0, $numZerosNeeded );
  return $zerosNeeded.$input;
}

// ===================================================
function clearLoggedInSessionVals( $LoginType )
{
  if( $LoginType == 'Bsns' ) {
    unset( $_SESSION['BsnsID']         );
    unset( $_SESSION['Bsns_Name']        );
    }
}


// =======================================================================
function saveUploadedImage( $bsnsWebDir, $fieldName, $picName, &$newUploadedImgFileName )
{
  $result = 'OK';
  $mapImage = ($_FILES[$fieldName]['name']=='Map.jpg' || $_FILES[$fieldName]['name']=='Map.png' );
  $DB = false;
if($DB) echo "DB:In saveUploadedImage#1, bsnsWebDir=['$bsnsWebDir'], fieldName=[$fieldName], picName=[$picName]<br>";
  $allowedExts = array('jpeg', 'jpg', 'JPEG', 'JPG', 'png', 'PNG', 'gif', 'GIF');
  if ( !$mapImage && ($fieldName == 'DZ_BsnsImgs' || $fieldName == 'DZ_BgndImgs' || $fieldName == 'DZ_ProdOther') ) { //Handle multiple files
      $fileType = $_FILES[$fieldName]['type'];
if($DB) echo "DB:In saveUploadedImage#2, fileType=[$fileType], Fileinfo for $fieldName name=[{$_FILES[$fieldName]['name']}], tempname=[{$_FILES[$fieldName]['tmp_name']}], type=[{$_FILES[$fieldName]['type']}], size=[{$_FILES[$fieldName]['size']}]<br>";
      if( $fileType == 'image/jpeg' ) { $fileExt = 'jpg'; }
      else if( $fileType == 'image/png' ) { $fileExt = 'png'; }
      else if( $fileType == 'image/gif' ) { $fileExt = 'gif'; }
      $temp = explode('.', $_FILES[$fieldName]['name']);
      $fileExt = strtolower( end($temp) );
if($DB) echo "DB:In saveUploadedImage#3, bsnsWebDir=['$bsnsWebDir'], temp[0]=[{$temp[0]}], temp[1]=[".empty($temp[1])?'empty':$temp[1]."], fileExt=[$fileExt], fileType=[$fileType], Fileinfo for $fieldName name=[{$_FILES[$fieldName]['name']}], tempname=[{$_FILES[$fieldName]['tmp_name']}], type=[{$_FILES[$fieldName]['type']}], size=[{$_FILES[$fieldName]['size']}]<br>";

      if (    ($_FILES[$fieldName]['type'] == 'image/jpeg' || $_FILES[$fieldName]['type'] == 'image/png' || $_FILES[$fieldName]['type'] == 'image/gif')
           && ($_FILES[$fieldName]['size'] < 5000000)    //Big is OK since they'll be resized to 640x480
           && in_array($fileExt, $allowedExts) ) {
        if ($_FILES[$fieldName]['error'] > 0) {
          $result = 'File Error: Return Code: ' . $_FILES[$fieldName]['error'];
        } else {
          $saveName = getNextAvailName( $bsnsWebDir, $fieldName );
if($DB) echo "DB:In saveUploadedImage#5, saveName=[$saveName], isUploadedFile = [". is_uploaded_file($_FILES[$fieldName]['tmp_name'][$iX]) ."]<br>";

          if( is_uploaded_file( $_FILES[$fieldName]['tmp_name'] ) )
            {
            if( moveResizedImage( $_FILES[$fieldName]['tmp_name'], $fileExt, "$bsnsWebDir/$saveName" ) )
              { $newUploadedImgFilename = $saveName.$fileExt; }
            }
        }
      } else {
        $result = "Invalid file, type = [{$_FILES[$fieldName]['type']}], ext = [$fileExt], size = [{$_FILES[$fieldName]['size']}]";
      }
  }

  else //Handle single files

  {
    $temp = explode('.', $_FILES[$fieldName]['name']);
    $fileExt = strtolower( end($temp) );
if($DB) echo "DB:In saveUploadedImage#7, temp[0]=[{$temp[0]}], temp[1]=[{$temp[1]}], fileExt=[$fileExt], Fileinfo for $fieldName name=[{$_FILES[$fieldName]['name']}], tempname=[{$_FILES[$fieldName]['tmp_name']}], type=[{$_FILES[$fieldName]['type']}], size=[{$_FILES[$fieldName]['size']}]<br>";
    if (    ($_FILES[$fieldName]['type'] == 'image/jpeg' || $_FILES[$fieldName]['type'] == 'image/png' || $_FILES[$fieldName]['type'] == 'image/gif')
         && ($_FILES[$fieldName]['size'] < 5000000)    //Big is OK since they'll be resized to 640x480
         && in_array($fileExt, $allowedExts) ) {
      if ($_FILES[$fieldName]['error'] > 0) {
        $result = 'File Error: Return Code: ' . $_FILES[$fieldName]['error'];
if($DB) echo "DB: in saveUploadedImage#9, File Error result=[$result]<br>";
      } else {
        if( is_uploaded_file( $_FILES[$fieldName]['tmp_name'] ) )
          {
            if( file_exists( "$bsnsWebDir/$picName.$fileExt" ) )
              { $result = 'OK-CacheRefreshNeeded'; }
            if( moveResizedImage( $_FILES[$fieldName]['tmp_name'], $fileExt, "$bsnsWebDir/$picName" ) )
              { $newUploadedImgFilename = $picName.$fileExt; }
          }
      }
    } else {
      $result = "Invalid file, type = [{$_FILES[$fieldName]['type']}], ext = [$fileExt], size = [{$_FILES[$fieldName]['size']}]";
if($DB) echo "DB: in saveUploadedImage#15, Invalid file result=[$result]<br>";
    }
  } //End of handling single files
  //updateActivityLog( $mysqli, AC_UPLOADIMG, $VTOsiteName.'+'.$fieldName.'+'.$picName );
if($DB) echo "DB: in saveUploadedImage#37, result=[$result]<br>";
  return $result;
}

// =======================================================================
function moveResizedImage( $uploadedTempFile, $fileExt, $destinationFilePathAndName_NoExt )
{ // Reduces all large images to 1024 x 768 max.
  if( empty($uploadedTempFile) ) return false;
  $DB = false;
  $destinationFilePathAndName = $destinationFilePathAndName_NoExt . '.jpg';
if($DB) echo "DB: NTF-moveResizedImage#1: uploadedTempFile=[$uploadedTempFile], fileExt=[$fileExt], destinationFilePathAndName=[$destinationFilePathAndName]<br>\n";

  if( $fileExt == 'JPG' || $fileExt == 'jpeg' || $fileExt == 'JPEG' ) {
    $fileExt = 'jpg';
  }

  list( $origWidth, $origHeight ) = getimagesize( $uploadedTempFile );
  if( $fileExt=='jpg' && $origWidth <= 1024 ) {
    return move_uploaded_file( $uploadedTempFile, $destinationFilePathAndName );
  }

  $newWidth = $origWidth;
  $newHeight = $origHeight;
  $jpegQuality = ( $fileExt == 'jpg' ) ? 75 : 95;
  if( $origWidth > 1024 ) {
    $newWidth = 1024;
    $newHeight = intval($origHeight * (1024 / $origWidth));
  }

  $finalImg = imagecreatetruecolor( $newWidth, $newHeight );
  if( !$finalImg ) return false;

if($DB) echo "DB: NTF-moveResizedImage#1: uploadedTempFile=[$uploadedTempFile], origWidth=[$origWidth], origHeight=[$origHeight], newwidth=[$newWidth], newheight=[$newHeight], fileExt=[$fileExt], destinationFilePathAndName=[$destinationFilePathAndName]<br>\n";

  if( $fileExt == 'jpg' ) {
    $source = imagecreatefromjpeg( $uploadedTempFile );
  } else if( $fileExt == 'png' ) {
    $source = imagecreatefrompng( $uploadedTempFile );
  } else if( $fileExt == 'gif' ) {
    $source = imagecreatefromgif( $uploadedTempFile );
  }
  if( !$source ) return false;
  //NOTE:  All images, whether resized or not (since some are from png or gif, and only big jpg reach here) need to go through imagecopyresized
  if( !imagecopyresized( $finalImg, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight ) ) return false;
  if($DB) echo "DB: finished  imagecopyresized <br>\n";

  $result = imagejpeg( $finalImg, $destinationFilePathAndName, $jpegQuality );
/*
  else if( $fileExt == 'png' ) {
    $result = imagepng( $reducedImg, $destinationFilePathAndName );
  } else if( $fileExt == 'gif' ) {
    $result = imagegif( $reducedImg, $destinationFilePathAndName );
  }
*/
if($DB) echo "DB:moveResizedImage#5: finished moveResizedImage<br>\n";

  unlink( $uploadedTempFile );
if($DB) echo "DB:moveResizedImage#9: finished moveResizedImage, after unlink<br>\n";
  return $result;
}


// ========== SECURITY ================
function safeVal( $origValue, $SVType, $mysqli = NULL )
{ // This function cleans input to make sure it's safe, IT DOES NOT CHECK VALIDITY
  if( empty($origValue) ) {
    switch ( $SVType ) {
      case SV_IDCODE: case SV_STRING: case SV_ALPHANUM: case SV_ALPHA: case SV_EMAIL: case SV_PHONE: case SV_EMWEB: case SV_WEBADDR: case CV_NICKNAME:
        return '';
      case SV_INT: case SV_BSNSID: case SV_DATE:
        return 0;
      case SV_LOGINID:
        return NULL;
    }

    return NULL;
  }

  if( strrchr( $origValue, '~' )!==false ) {
    strtr( $origValue, '~', '_' ); // ~ is used to separate fields
  }
  if( strrchr( $origValue, '^' )!==false ) {
    strtr( $origValue, '^', '_' ); // ^ is used to separate fields
  }

  $regEx = '';
  switch( $SVType ) {
    case SV_IDCODE: //Checked OK, CatCode could be  123AZ  and ; or , as delimiters
      $regEx = '/[^0-9A-Z-]/';
      return (preg_match($regEx, $origValue) === 0) ? $origValue : 1;

    case SV_INT: // Checked OK
      return intval( $origValue );  // returns 0 if not an int or int string

    case SV_LOGINID: // [A-Za-z0-9_] Alphanumeric characters plus "_", Checked OK, sample: Doug12_R _Rogers
      $noDotVal = str_replace( '.', 'A', $origValue );
      if( preg_match( '/\W/', $noDotVal ) ) {
          return '(BadLogin)';
        //header( "Location: index.php" );
        //exit;
        }
      return $origValue;

    case SV_EMAIL:  // Email: letters,digits,+,-,.,_  (English only) NOT VALIDATION, just cleaning OK
    case SV_EMWEB: // EmlWeb name stub: letters,digits,-,.,_  (English only), Checked OK
    case SV_WEBADDR: // Like EmlWeb: a-Z,0-9,-,.,_ (English only) NOT VALIDATION, just cleaning OK
      $regEx = '/[^a-zA-Z0-9_\-\+\.@\/]/';  // only allow
      $cleanValue = preg_replace( $regEx, '', $origValue );
      return filter_var( $cleanValue, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW );

    case SV_NICKNAME: // Checked OK, Letters and numbers and _ . - only,  Allow Viet characters
      $regEx = '/[^\p{L}\p{N}_\.\-]/u';
//      $cleanValue = htmlspecialchars( $origValue, ENT_QUOTES );   //htmlentities    preg_replace( $regEx, ' ', $origValue );
//      return $cleanValue;
      break;

    case SV_STRINGLIST:
      $regEx = '/[^a-zA-Z, ]/';  // only allow
      break;

    case SV_STRING: // Checked OK, Any string, strip out ',",<,> Allow Viet characters
      $cleanValue = htmlspecialchars( $origValue, ENT_QUOTES );   //htmlentities    preg_replace( $regEx, ' ', $origValue );
      return $cleanValue;

    case SV_ALPHANUM: // Checked OK, Letters and numbers and _ only  Allow Viet characters
      $regEx = '/[^\p{L}\p{N}_]/u';
      break;

    case SV_ALPHA: // Checked OK, Letters and _ only, no numbers (eg. Names) Allow Viet characters
      $regEx = '/[^\p{L}_]/u';
      break;

    case SV_BSNSID: // Checked OK, Only allow digits, decimal point (.), and ; as list separator
      $regEx = '/[^0-9.;]/';   //'/[^0-9 \-\+\(\)\.]/'
      break;

    case SV_PHONE: // Checked OK, Only allow digits, space,(,),+,-
      $regEx = '/[^0-9 \-\+\(\)\.]/';   //'/[^0-9 \-\+\(\)\.]/'
      break;

    case SV_DATE: // Checked OK, Only allow digits, -  eg:  14-03-27
      $regEx = '/[^0-9\-]/';
      break;
    }

  $cleanValue = preg_replace( $regEx, ' ', $origValue );
  return $cleanValue;
}

// ==================================================================
function safeSQLValue( $mysqli, $origValue )  //Tested OK
{
  $regEx = '/[\'\"<>]/';  // replace ' " < > with spaces
  $cleanValue = preg_replace( $regEx, ' ', $origValue );

  return $mysqli->escape_string( $cleanValue );
}

// ==================================================================
function handleSQLProblem( $srcOrigin, $s_query, $errInfo )
{
  die("<br>SQLErr: $srcOrigin; Q:[$s_query]<br>Error=[$errInfo]<br>");
}

// ====================================================


function checkForValidValue( $submittedVal, $valType, $safeVal = '' )
{  //Checks LoginID, LoginPwd, WebEmlAbbrv, BsnsNum, Email, Phone for valid values
//echo "DB:checkForValidValue#1: submittedVal=[$submittedVal], valType=[$valType], safeVal=[$safeVal]<br>\n";
  if( ($safeVal != '') && ($submittedVal != $safeVal) )
    {
/*
if($submittedVal != $safeVal)
//echo "DB: ***************** Strings are NOT equal (!=) ********************<br>";
if($submittedVal !== $safeVal)
//echo "DB: ***************** Strings are NOT equal (!==) ********************<br>";
if(strcmp($submittedVal,$safeVal) != 0 )
//echo "DB: ***************** Strings are NOT equal (strcmp) ********************<br>";
for( $ix=0; $ix<strlen($submittedVal); $ix++)
{
if( $submittedVal[$ix]!==$safeVal[$ix])
echo "$ix: [{$submittedVal[$ix]}]!=[{$safeVal[$ix]}]<br>\n";
else
echo "$ix: [{$submittedVal[$ix]}]  ==  [{$safeVal[$ix]}]<br>\n";
}
*/
//echo "DB:checkForValidValue#13: submittedVal=[$submittedVal] != safeVal=[$safeVal]   valType=[$valType]<br>\n";

    return FALSE;
    }

  switch( $valType ) {
    case CV_LOGINID: // ['LoginIDRules'] = '(6-15 chars: [A-Z],[a-z],[0-9],_ OK)';
      if( strlen($submittedVal) < LOGINID_MINLEN || strlen($submittedVal) > LOGINID_MAXLEN )
        { return FALSE; }
      $noDotVal = str_replace( '.', 'A', $submittedVal );
      return !preg_match( '/\W/', $noDotVal ); // ? 'OK' : $RS['LoginRulesErr'];

    case CV_PWD:  // ['PwdRules'] = '(8-25 chars: must have [A-Z]+[a-z]+[0-9])';
      if( strlen($submittedVal) < PWD_MINLEN || strlen($submittedVal) > PWD_MAXLEN )
        { return FALSE; }
/*
      return (   preg_match('/[A-Z]/', $submittedVal)
              && preg_match('/[a-z]/', $submittedVal)
              && preg_match('/[0-9]/', $submittedVal) ); // ? 'OK' : $RS['PwdRulesErr'];
*/
      return true;

    case CV_PHONE: // Checked OK, Only allow digits, space,.,(,),+,-
//echo "<br>DB:checkForValidValue#2: submittedVal(phone)=[$submittedVal]<br>\n";
      if( strlen($submittedVal) < PHONE_MINLEN || strlen($submittedVal) > PHONE_MAXLEN )
        { return FALSE; }
      $regEx = '/[^0-9 \-\+\(\)\.]/';

//$lenSubmitVal = strlen($submittedVal); $PHNE_MAXLEN=PHONE_MAXLEN;
//$pregMatchResult = preg_match( $regEx, $submittedVal );
//echo "<br>DB:checkForValidValue#2: submittedVal(phone)=[$submittedVal], pregMatchResult=[$pregMatchResult],  lenSubmitVal=[$lenSubmitVal], PHNE_MAXLEN=[$PHNE_MAXLEN]<br>\n";

      return !preg_match( $regEx, $submittedVal ); // ? 'OK' : $RS['InvalidPhone'];

    case CV_EMAIL:  // Email: letters,digits,@,+,-,.,_  (English only)
      $regEx = '/^([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})/i';
      return preg_match( $regEx, $submittedVal );

    case CV_EMWEB: // EmlWeb name stub: letters,digits,-,.,_  (English only), Checked OK
      $regEx = '/[^a-zA-Z0-9_\-\.]/';  // only allow
      return !preg_match( $regEx, $submittedVal );

    case CV_NICKNAME: // EmlWeb name stub: letters,digits,-,.,_  (English only), Checked OK
      $VWsubmittedVal = vietToWiet( $submittedVal );
      $regEx = '/[^a-zA-Z0-9_]/';  // only allow
      return !preg_match( $regEx, $VWsubmittedVal );

    case CV_BSNSNUM:
      $iVal = intval( $submittedVal );
      return ($iVal >= BSNSNUM_MINVAL && $iVal <= BSNSNUM_MAXVAL );
    }
}

// =======================================================================
function sortMultiArray( $multiArray, $columnDBName, $sortType = 'SORT_ASC' )
{
  if( $columnDBName == '' )
    { return $multiArray; }

  foreach ( $multiArray as $row ) {
    $sortarr[] = $row[$columnDBName];
  }
//echo "columnDBName = [$columnDBName], sortarr[15] = [{$sortarr[15]}]<br>";

// SORT_ASC - sort items ascendingly.
// SORT_DESC - sort items descendingly.
// SORT_REGULAR - compare items normally (don't change types)
// SORT_NUMERIC - compare items numerically
// SORT_STRING - compare items as strings
// SORT_LOCALE_STRING - compare items as strings, based on the current locale. It uses the locale, // which can be changed using setlocale()
// SORT_NATURAL - compare items as strings using "natural ordering" like natsort()
// SORT_FLAG_CASE - can be combined (bitwise OR) with SORT_STRING or SORT_NATURAL to sort strings // // case-insensitively


 if( $sortType == 'SORT_DESC' )
   { array_multisort( $sortarr, SORT_DESC, $multiArray ); }
  else
   { array_multisort( $sortarr, SORT_ASC, $multiArray ); }
 return $multiArray;
}

// =====================================================