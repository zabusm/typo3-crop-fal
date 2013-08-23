<?php 
namespace Zabus\CropFal\Tca;

class Wizard {
	public function showIcon($fObj)
	{
		/*ob_start();
		var_dump($fObj);
		$result = ob_get_clean();
		return $result;*/ 
		//uid_local
		$return = '<a href="#"  onclick="window.open(\''
								. 'mod.php?M=zabus_crop_fal&image=' . $fObj['row']['uid_local']
								. '&aspectratio=' . $fObj['itemFormElValue']
								. '\',\'fenster'.rand(0, 1000000).''
								. '\',\'height=620,width=820,status=0,menubar=0,scrollbars=0\');return false;">
		
		<span title="CPDESIGNNEU.png" class="t3-icon t3-icon-mimetypes t3-icon-mimetypes-media t3-icon-media-image">&nbsp;</span></a>';
		$return.= '<input type="hidden" value="'.$fObj['itemFormElValue'].'" name="'.$fObj['itemFormElName'].'" id="zabus_crop_fal_input" />';
		
		return $return;
	}
}
?>