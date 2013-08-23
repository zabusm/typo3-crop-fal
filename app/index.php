<?php
//var_dump($_REQUEST);

$realPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath("crop_fal");
$uid = str_replace('sys_file_','',$_REQUEST['image']);

$fileRep = new TYPO3\CMS\Core\Resource\FileRepository();

$file = $fileRep->findByUid($uid);

$x = $y = $w = $h = $x2 = $y2 = "";

if(isset($_GET['aspectratio']))
{
	$aspecRatio = explode(',',$_GET['aspectratio']);
	$x = $aspecRatio[0];
	$y = $aspecRatio[1];
	$w = $aspecRatio[2];
	$h = $aspecRatio[3];
	$x2 = $aspecRatio[4];
	$y2 = $aspecRatio[5];
}

//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($file);
?>
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<script src="<?php echo $realPath ?>Resources/Public/js/jquery.min.js" type="text/javascript"></script>
		<script src="<?php echo $realPath ?>Resources/Public/js/jquery.Jcrop.min.js" type="text/javascript"></script>
		<script src="<?php echo $realPath ?>Resources/Public/js/default.js" type="text/javascript"></script>
		
		<link rel="stylesheet" href="<?php echo $realPath;?>Resources/Public/css/jquery.Jcrop.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo $realPath;?>Resources/Public/css/default.css" type="text/css" />
	</head>
	
	<body>
		<img src="/fileadmin/<?php echo $file->getIdentifier();?>" id="target" style="" alt="[Jcrop Example]" />
	<!--<div id="preview-pane">
    <div class="preview-container">
      <img src="/fileadmin/<?php echo $file->getIdentifier();?>" class="jcrop-preview" alt="Preview" />
    </div>
  </div>-->
  
	<form action="" method="post" onsubmit="">
		<input type="hidden" id="x" name="x" value="<?php echo $x;?>" />
		<input type="hidden" id="y" name="y" value="<?php echo $y;?>" />
		<input type="hidden" id="x2" name="x2" value="<?php echo $x2;?>" />
		<input type="hidden" id="y2" name="y2" value="<?php echo $y2;?>" />
		<input type="hidden" id="w" name="w" value="<?php echo $w;?>" />
		<input type="hidden" id="h" name="h" value="<?php echo $h;?>" />
		<div>
			Aspect:

			<select name="ratio" id="ratio">
				<option value="0:0">None</option>
				<option value="16:9" >16:9</option>
			</select>
		</div>
		<div><a href="#" onclick="crop();return false;">Crop</button></div>
	</form>
	</body>
</html>