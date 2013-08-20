			//$("#zabus_crop_fal_input",opener.document).val("zabus");
			//self.close();
jQuery(function($){

    // Create variables (in this scope) to hold the API and image size
    var jcrop_api,
        boundx,
        boundy;



    
    $('#target').Jcrop({
      onSelect: updateCoords,
	  bgOpacity: .2
    },function(){
      // Use the API to get the real image size
      var bounds = this.getBounds();
      boundx = bounds[0];
      boundy = bounds[1];
      // Store the API in the jcrop_api variable
      jcrop_api = this;
	  if($('#x').val() != "")
	  {
		var ratio = [
				$('#x').val(),
				$('#y').val(),
				$('#x2').val(),
				$('#y2').val()
		];
		
		//var ratio = [0,1,164,111];
		
		jcrop_api.animateTo(ratio);
	  }
    });

    /*function updatePreview(c)
    {
		updateCoords(c);
      if (parseInt(c.w) > 0)
      {
        var rx = xsize / c.w;
        var ry = ysize / c.h;

        $pimg.css({
          width: Math.round(rx * boundx) + 'px',
          height: Math.round(ry * boundy) + 'px',
          marginLeft: '-' + Math.round(rx * c.x) + 'px',
          marginTop: '-' + Math.round(ry * c.y) + 'px'
        });
      }
    };*/
	
	function updateCoords(c)
	{
		$('#x').val(c.x);
		$('#y').val(c.y);
		$('#w').val(c.w);
		$('#h').val(c.h);
		$('#x2').val(c.x2);
		$('#y2').val(c.y2);
		console.log(c);
	};
	
	$('#ratio').change(function(e) {
		var ratio = $(this).val();
		var aspects = ratio.split(':');
		
		jcrop_api.setOptions({ 
			aspectRatio: aspects[0]/aspects[1] 
		});
      jcrop_api.focus();
    });
	
  });
  
 	function crop()
	{
		$("#zabus_crop_fal_input",opener.document).val($('#x').val()+","+$('#y').val()+","+$('#w').val()+","+$('#h').val()+","+$('#x2').val()+","+$('#y2').val());
		self.close();
		return false;
	}
	