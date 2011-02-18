	/************************************************************************************************************
	(C) www.dhtmlgoodies.com, April 2006

	This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.

	Terms of use:
	You are free to use this script as long as the copyright message is kept intact. However, you may not
	redistribute, sell or repost it without our permission.

	Thank you!

	www.dhtmlgoodies.com
	Alf Magne Kalleland

	************************************************************************************************************/
	/*
		fixes for TYPO3
		(c) www.4many.net, 2006 -2007  Peter Russ, peter.russ@4many.net
	*/

	/*PRS+  20060719 */
	var extensionPath='../res/crop-image/';
	var md5Hash='';
	/*PRS-  20060719 */
	/*PRS+ 20071129 */
	var cropInit=false;
	/*PRS- 20071129 */
	/*PRS+ 2007 11 30 */
	var isLocked=false;
	var keepRatio=false;
	/*PRS- 2007 11 30 */

	var crop_script_alwaysPreserveAspectRatio = false;
	var crop_script_fixedRatio = false;	 // Fixed aspect ratio(example of value: 1.5). Width of cropping area relative to height(1.5 means that the width is 150% of the height)
										 // Set this variable to false if you don't want a fixed aspect ratio

	var crop_script_browserIsOpera = (navigator.userAgent.indexOf('Opera')>=0)?true:false;
	var cropDiv_left = false;
	var cropDiv_top = false;
	var cropDiv_right = false;
	var cropDiv_bottom = false;
	var cropDiv_dotted = false;

	var crop_currentResizeType = false;

	var cropEvent_posX;
	var cropEvent_posY;

	var cropEvent_eventX;
	var cropEvent_eventY;
	var crop_resizeCounter = -1;
	var crop_moveCounter = -1;

	var crop_imageDiv = false;
	var imageDiv_currentWidth = false;
	var imageDiv_currentHeight = false;
	var imageDiv_currentLeft = false;
	var imageDiv_currentTop = false;

	var smallSquare_tl;
	var smallSquare_tc;
	var smallSquare_tr;
	var smallSquare_lc;
	var smallSquare_rc;
	var smallSquare_bl;
	var smallSquare_bc;
	var smallSquare_br;

	var offsetSmallSquares = Math.floor(smallSquareWidth/2);

	var cropScriptAjaxObjects = new Array();
	var preserveAspectRatio = false;

	var cropWidthRatio = false;	// width of cropping area relative to height
	/*PRS+ 2008 6 20 */
	var autoCrop=false; // will display crop frame automatically
	/*PRS- 2008 6 20 */
	function crop_createDivElements()
	{
		crop_imageDiv= document.getElementById('imageContainer');

		cropDiv_left = document.createElement('DIV');
		cropDiv_left.className = 'crop_transparentDiv';
		cropDiv_left.style.visibility = 'visible';
		cropDiv_left.style.left = '0px';
		cropDiv_left.style.top = '0px';
		cropDiv_left.style.height = crop_imageHeight + 'px';
		cropDiv_left.style.width = '0px';
		cropDiv_left.innerHTML = '<span></span>';
		crop_imageDiv.appendChild(cropDiv_left);

		cropDiv_top = document.createElement('DIV');
		cropDiv_top.className = 'crop_transparentDiv';
		cropDiv_top.style.visibility = 'visible';
		cropDiv_top.style.left = '0px';
		cropDiv_top.style.top = '0px';
		cropDiv_top.style.height = '0px';
		cropDiv_top.style.width = crop_imageWidth + 'px';
		cropDiv_top.innerHTML = '<span></span>';
		crop_imageDiv.appendChild(cropDiv_top);

		cropDiv_right = document.createElement('DIV');
		cropDiv_right.className = 'crop_transparentDiv';
		cropDiv_right.style.visibility = 'visible';
		cropDiv_right.style.left = (crop_imageWidth) + 'px';
		cropDiv_right.style.top = '0px';
		cropDiv_right.style.height = crop_imageHeight + 'px';
		cropDiv_right.style.width = '0px';
		cropDiv_right.innerHTML = '<span></span>';
		crop_imageDiv.appendChild(cropDiv_right);

		cropDiv_bottom = document.createElement('DIV');
		cropDiv_bottom.className = 'crop_transparentDiv';
		cropDiv_bottom.style.visibility = 'visible';
		cropDiv_bottom.style.left = '0px';
		cropDiv_bottom.style.top = (crop_imageHeight) + 'px';
		cropDiv_bottom.style.height = '0px';
		cropDiv_bottom.style.width = crop_imageWidth + 'px';
		cropDiv_bottom.innerHTML = '<span></span>';
		crop_imageDiv.appendChild(cropDiv_bottom);

		cropDiv_dotted = document.createElement('DIV');
		cropDiv_dotted.className='crop_dottedDiv';
		cropDiv_dotted.style.left = '0px';
		cropDiv_dotted.style.top = '0px';
		cropDiv_dotted.style.width = (crop_imageWidth-(cropToolBorderWidth*2)) + 'px';
		cropDiv_dotted.style.height = (crop_imageHeight-(cropToolBorderWidth*2)) + 'px';
		cropDiv_dotted.innerHTML = '<div></div>';
		cropDiv_dotted.style.cursor = 'move';

		if(crop_script_browserIsOpera){
			var div = cropDiv_dotted.getElementsByTagName('DIV')[0];
			div.style.backgroundColor='transparent';
			cropDiv_bottom.style.backgroundColor='transparent';
			cropDiv_right.style.backgroundColor='transparent';
			cropDiv_top.style.backgroundColor='transparent';
			cropDiv_left.style.backgroundColor='transparent';

		}

		cropDiv_dotted.onmousedown = cropScript_initMove;

		smallSquare_tl = document.createElement('IMG');
		/*PRS+  20060719 */
		smallSquare_tl.src = extensionPath+'images/small_square.gif';
		/*PRS-  20060719 */
		smallSquare_tl.style.position = 'absolute';
		smallSquare_tl.style.left = (-offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_tl.style.top = (-offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_tl.style.cursor = 'nw-resize';
		smallSquare_tl.id = 'nw-resize';
		smallSquare_tl.onmousedown = cropScript_initResize;
		cropDiv_dotted.appendChild(smallSquare_tl);

		smallSquare_tr = document.createElement('IMG');
		/*PRS+  20060719 */
		smallSquare_tr.src = extensionPath+'images/small_square.gif';
		/*PRS-  20060719 */
		smallSquare_tr.style.position = 'absolute';
		smallSquare_tr.style.left = (crop_imageWidth - offsetSmallSquares - (cropToolBorderWidth*2)) + 'px';
		smallSquare_tr.style.top = (-offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_tr.style.cursor = 'ne-resize';
		smallSquare_tr.id = 'ne-resize';
		smallSquare_tr.onmousedown = cropScript_initResize;
		cropDiv_dotted.appendChild(smallSquare_tr);

		smallSquare_bl = document.createElement('IMG');
		/*PRS+  20060719 */
		smallSquare_bl.src = extensionPath+'images/small_square.gif';
		/*PRS-  20060719 */
		smallSquare_bl.style.position = 'absolute';
		smallSquare_bl.style.left = (-offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_bl.style.top = (crop_imageHeight - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_bl.style.cursor = 'sw-resize';
		smallSquare_bl.id = 'sw-resize';
		smallSquare_bl.onmousedown = cropScript_initResize;
		cropDiv_dotted.appendChild(smallSquare_bl);

		smallSquare_br = document.createElement('IMG');
		/*PRS+  20060719 */
		smallSquare_br.src = extensionPath+'images/small_square.gif';
		/*PRS-  20060719 */
		smallSquare_br.style.position = 'absolute';
		smallSquare_br.style.left = (crop_imageWidth - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_br.style.top = (crop_imageHeight - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_br.style.cursor = 'se-resize';
		smallSquare_br.id = 'se-resize';
		smallSquare_br.onmousedown = cropScript_initResize;
		cropDiv_dotted.appendChild(smallSquare_br);

		smallSquare_tc = document.createElement('IMG');
		/*PRS+  20060719 */
		smallSquare_tc.src = extensionPath+'images/small_square.gif';
		/*PRS-  20060719 */
		smallSquare_tc.style.position = 'absolute';
		smallSquare_tc.style.left = (Math.floor(crop_imageWidth/2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_tc.style.top = (-offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_tc.style.cursor = 's-resize';
		smallSquare_tc.id = 'n-resize';
		smallSquare_tc.onmousedown = cropScript_initResize;
		cropDiv_dotted.appendChild(smallSquare_tc);

		smallSquare_bc = document.createElement('IMG');
		/*PRS+  20060719 */
		smallSquare_bc.src = extensionPath+'images/small_square.gif';
		/*PRS-  20060719 */
		smallSquare_bc.style.position = 'absolute';
		smallSquare_bc.style.left = (Math.floor(crop_imageWidth/2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_bc.style.top = (crop_imageHeight - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_bc.style.cursor = 's-resize';
		smallSquare_bc.id = 's-resize';
		smallSquare_bc.onmousedown = cropScript_initResize;
		cropDiv_dotted.appendChild(smallSquare_bc);

		smallSquare_lc = document.createElement('IMG');
		/*PRS+  20060719 */
		smallSquare_lc.src = extensionPath+'images/small_square.gif';
		/*PRS-  20060719 */
		smallSquare_lc.style.position = 'absolute';
		smallSquare_lc.style.left = (-offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_lc.style.top = (Math.floor(crop_imageHeight/2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_lc.style.cursor = 'e-resize';
		smallSquare_lc.id = 'w-resize';
		smallSquare_lc.onmousedown = cropScript_initResize;
		cropDiv_dotted.appendChild(smallSquare_lc);

		smallSquare_rc = document.createElement('IMG');
		/*PRS+  20060719 */
		smallSquare_rc.src = extensionPath+'images/small_square.gif';
		/*PRS-  20060719 */
		smallSquare_rc.style.position = 'absolute';
		smallSquare_rc.style.left = (crop_imageWidth - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_rc.style.top = (Math.floor(crop_imageHeight/2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_rc.style.cursor = 'e-resize';
		smallSquare_rc.id = 'e-resize';
		smallSquare_rc.onmousedown = cropScript_initResize;
		cropDiv_dotted.appendChild(smallSquare_rc);

		crop_imageDiv.appendChild(cropDiv_dotted);
	}

	function cropScript_initMove(e)
	{
		if(document.all)e=event;

		if (e.target) source = e.target;
			else if (e.srcElement) source = e.srcElement;
			if (source.nodeType == 3) // defeat Safari bug
				source = source.parentNode;

		if(source.id && source.id.indexOf('resize')>=0)return;

		imageDiv_currentLeft = cropDiv_dotted.style.left.replace('px','')/1;
		imageDiv_currentTop = cropDiv_dotted.style.top.replace('px','')/1;
		imageDiv_currentWidth = cropDiv_dotted.style.width.replace('px','')/1;
		imageDiv_currentHeight = cropDiv_dotted.style.height.replace('px','')/1;
		cropEvent_eventX = e.clientX;
		cropEvent_eventY = e.clientY;

		crop_moveCounter = 0;
		cropScript_timerMove();
		return false;
	}

	function cropScript_timerMove()
	{
		if(crop_moveCounter>=0 && crop_moveCounter<10){
			crop_moveCounter++;
			setTimeout('cropScript_timerMove()',1);
			return;
		}

	}

/*PRS+ 2007 11 30 */
	function updateLockRatio()
	{
		isLocked=document.getElementById('input_lock').checked;
		keepRatio=document.getElementById('input_ratio').checked;
		if (!crop_script_alwaysPreserveAspectRatio && keepRatio)
		{
			cropWidthRatio = cropDiv_dotted.style.width.replace('px','')/1 / cropDiv_dotted.style.height.replace('px','')/1;
		}
		crop_script_alwaysPreserveAspectRatio=keepRatio;
	}
/*PRS- 2007 11 30 */

	function cropScript_initResize(e)
	{
		/*PRS+ 2007 11 30 */
		updateLockRatio();
		if (!isLocked)
		{
			if(document.all)e = event;

			cropDiv_dotted.style.cursor = 'default';
			crop_currentResizeType = this.id;

			cropEvent_eventX = e.clientX;
			cropEvent_eventY = e.clientY;
			crop_resizeCounter = 0;
			imageDiv_currentWidth = cropDiv_dotted.style.width.replace('px','')/1;
			imageDiv_currentHeight = cropDiv_dotted.style.height.replace('px','')/1;
			imageDiv_currentLeft = cropDiv_dotted.style.left.replace('px','')/1;
			imageDiv_currentTop = cropDiv_dotted.style.top.replace('px','')/1;


			/* cropWidthRatio = cropDiv_dotted.offsetWidth / cropDiv_dotted.offsetHeight; */
			if(!keepRatio && crop_script_fixedRatio)cropWidthRatio = crop_script_fixedRatio;

			if(document.all){
				var div = cropDiv_dotted.getElementsByTagName('DIV')[0];
				div.style.display='none';
			}

			cropScript_timerResize();
		}
		/*PRS- 2007 11 30 */
		return false;
	}

	function cropScript_timerResize()
	{
		if(crop_resizeCounter>=0 && crop_resizeCounter<10){
			crop_resizeCounter = crop_resizeCounter + 1;
			setTimeout('cropScript_timerResize()',1);
			return;
		}


	}
	/*PRS+ 2007 12 8 */
	function getPercent()
	{
		var fixedSize=document.getElementById('input_fixedsize');
		if (fixedSize.value!='')
		{
			var values=fixedSize.value.split('x');
			var percent=values[0]/document.getElementById('input_crop_width').value*100;
			window.status=percent;
			return percent;
		} else {
			return document.getElementById('crop_percent_size').value;
		}
	}
	function cropScript_setSize(buttonObj)
	{
		var elm=document.getElementById('input_fixedsize').value;
		if (elm!='')
		{
			values=elm.split('x');
			document.getElementById('input_crop_width').value=values[0];
			document.getElementById('input_crop_height').value=values[1];
			document.getElementById('input_ratio').checked=true;
			cropWidthRatio=values[0]/values[1];
			cropInit=true;
			cropScript_setCropSizeByInput();
//			cropScript_setCropSizeByInput();
		}
	}
	/*PRS- 2007 12 8 */
	function cropScript_executeCrop(buttonObj)
	{
		crop_startProgressBar();
		buttonObj.style.visibility='hidden';
//PRS+ 20060721
		buttonObj.style.display='none';
//PRS- 20060721
		var ajaxIndex = cropScriptAjaxObjects.length;
		cropScriptAjaxObjects[ajaxIndex] = new sack();
		var url = crop_script_server_file + '?image_ref=' + document.getElementById('input_image_ref').value
		+ '&x=' + document.getElementById('input_crop_x').value
		+ '&y=' + document.getElementById('input_crop_y').value
		+ '&width=' + document.getElementById('input_crop_width').value
		+ '&height=' + document.getElementById('input_crop_height').value
		/*PRS+ 2007 12 8 */
		+ '&percentSize=' + getPercent()
		/*PRS- 2007 12 8 */
//PRS+ 20060722
		+ '&cmd=crop&chash='+md5Hash
//PRS- 20060722
		+ '&convertTo=' + document.getElementById('input_convert_to').options[document.getElementById('input_convert_to').selectedIndex].value;
//PRS+ 20060722
		var elm=document.getElementById('input_preview');
		if (elm.checked)
		{
			url +='&preview='+elm.value;
		}
		elm=document.getElementById('input_store');
		if (elm.checked)
		{
			url +='&store='+elm.value;
		}
//PRS- 20060722
		cropScriptAjaxObjects[ajaxIndex].requestFile = url;	// Specifying which file to get
		cropScriptAjaxObjects[ajaxIndex].onCompletion = function(){ cropScript_cropCompleted(ajaxIndex,buttonObj); };	// Specify function that will be executed after file has been found
		cropScriptAjaxObjects[ajaxIndex].runAJAX();		// Execute AJAX function
	}

	function cropScript_cropCompleted(ajaxIndex,buttonObj)
	{
		buttonObj.style.visibility='';
//PRS+ 20060721
		buttonObj.style.display='block';
//PRS- 20060721
		crop_hideProgressBar();
		eval(cropScriptAjaxObjects[ajaxIndex].response)
		cropScriptAjaxObjects[ajaxIndex] = false;
	}


	function crop_cancelEvent(e)
	{
		if(document.all)e = event;
		if (e.target) source = e.target;
			else if (e.srcElement) source = e.srcElement;
			if (source.nodeType == 3) // defeat Safari bug
				source = source.parentNode;

		if(source.tagName && source.tagName.toLowerCase()=='input')return true;
		return false;
	}

	var mouseMoveEventInProgress = false;
	function cropScript_mouseMove(e)
	{

		if(mouseMoveEventInProgress)return;
		if(crop_moveCounter<10 && crop_resizeCounter<10)return;
		if(document.all)mouseMoveEventInProgress = true;
		if(document.all)e = event;
		var widthChanged=false;
		var heightChanged=false;




		if(crop_resizeCounter==10){
			/*PRS+ 2007 12 7 */
			var width=(cropInit)?0:cropToolBorderWidth*2;
			var bWidth=width/2;
			/*PRS- 2007 12 7 */

			if(crop_currentResizeType=='e-resize' || crop_currentResizeType=='ne-resize' || crop_currentResizeType == 'se-resize'){
				cropDiv_dotted.style.width = Math.max(crop_minimumWidthHeight,(imageDiv_currentWidth + e.clientX - cropEvent_eventX)) + 'px';
				widthChanged=true;
			}
			if(crop_currentResizeType=='s-resize' || crop_currentResizeType=='sw-resize' || crop_currentResizeType == 'se-resize'){
				cropDiv_dotted.style.height = Math.max(crop_minimumWidthHeight,(imageDiv_currentHeight + e.clientY - cropEvent_eventY)) + 'px';
				heightChanged=true;
			}

			if(crop_currentResizeType=='w-resize' || crop_currentResizeType=='sw-resize' || crop_currentResizeType=='nw-resize'){
				var tmpTop = cropDiv_dotted.style.left.replace('px','')/1;
				var newTop = Math.max(0,(imageDiv_currentLeft + e.clientX - cropEvent_eventX));
				if((newTop + crop_minimumWidthHeight)> (cropDiv_dotted.style.left.replace('px','')/1 + cropDiv_dotted.style.width.replace('px','')/1)){
					newTop = (cropDiv_dotted.style.left.replace('px','')/1 + cropDiv_dotted.style.width.replace('px','')/1) - crop_minimumWidthHeight;
				}
				cropDiv_dotted.style.left = newTop + 'px';
				cropDiv_dotted.style.width = (cropDiv_dotted.style.width.replace('px','')/1 + tmpTop - cropDiv_dotted.style.left.replace('px','')/1) + 'px';
				widthChanged=true;
			}

			if(crop_currentResizeType=='n-resize' || crop_currentResizeType=='nw-resize' || crop_currentResizeType=='ne-resize'){
				var tmpTop = cropDiv_dotted.style.top.replace('px','')/1;
				var newTop = Math.max(0,(imageDiv_currentTop + e.clientY - cropEvent_eventY));
				if((newTop + crop_minimumWidthHeight)> (cropDiv_dotted.style.top.replace('px','')/1 + cropDiv_dotted.style.height.replace('px','')/1)){
					newTop = (cropDiv_dotted.style.top.replace('px','')/1 + cropDiv_dotted.style.height.replace('px','')/1) - crop_minimumWidthHeight;
				}
				cropDiv_dotted.style.top = newTop + 'px';
				cropDiv_dotted.style.height = (cropDiv_dotted.style.height.replace('px','')/1 + tmpTop - cropDiv_dotted.style.top.replace('px','')/1) + 'px';
				heightChanged=true;
			}

			if((cropDiv_dotted.offsetHeight + width + cropDiv_dotted.style.top.replace('px','')/1) > crop_imageHeight)
				cropDiv_dotted.style.height = (crop_imageHeight - cropDiv_dotted.style.top.replace('px','')/1 - width) + 'px';

			if((cropDiv_dotted.offsetWidth + width + cropDiv_dotted.style.left.replace('px','')/1) > crop_imageWidth)
				cropDiv_dotted.style.width = (crop_imageWidth - cropDiv_dotted.style.left.replace('px','')/1 - width) + 'px';



			if(e.ctrlKey || crop_script_alwaysPreserveAspectRatio)preserveAspectRatio=true; else preserveAspectRatio = false;
			/*PRS+ 2007 12 8 */
			if(preserveAspectRatio){
				var tmpRatio = cropDiv_dotted.style.width.replace('px','')/1 / cropDiv_dotted.style.height.replace('px','')/1;
				if(tmpRatio<cropWidthRatio){
					if (heightChanged) cropDiv_dotted.style.width = (cropDiv_dotted.style.height.replace('px','')/1 * cropWidthRatio) + 'px';
					if (widthChanged) cropDiv_dotted.style.height = (cropDiv_dotted.style.width.replace('px','')/1 / cropWidthRatio) + 'px';
				}else{
					if (widthChanged) cropDiv_dotted.style.height = (cropDiv_dotted.style.width.replace('px','')/1 / cropWidthRatio) + 'px';
					if (heightChanged) cropDiv_dotted.style.width = (cropDiv_dotted.style.height.replace('px','')/1 * cropWidthRatio) + 'px';
				}
				//window.status=tmpRatio+' : '+(cropDiv_dotted.style.width.replace('px','')/1 / cropDiv_dotted.style.height.replace('px','')/1);
				if((cropDiv_dotted.offsetHeight + width + cropDiv_dotted.style.top.replace('px','')/1) > crop_imageHeight){
					var ratioToAdjust = crop_imageHeight/(cropDiv_dotted.offsetHeight + width + cropDiv_dotted.style.top.replace('px','')/1);
					cropDiv_dotted.style.height = ((cropDiv_dotted.style.height.replace('px','') * ratioToAdjust)+bWidth) + 'px';
					cropDiv_dotted.style.width = ((cropDiv_dotted.style.width.replace('px','') * ratioToAdjust)+bWidth) + 'px';

				}
				if((cropDiv_dotted.offsetWidth + width + cropDiv_dotted.style.left.replace('px','')/1) > crop_imageWidth){
					var ratioToAdjust = crop_imageWidth/(cropDiv_dotted.offsetWidth + width + cropDiv_dotted.style.left.replace('px','')/1);
					cropDiv_dotted.style.height = ((cropDiv_dotted.style.height.replace('px','') * ratioToAdjust)+bWidth) + 'px';
					cropDiv_dotted.style.width = ((cropDiv_dotted.style.width.replace('px','') * ratioToAdjust)+bWidth) + 'px';
				}
			}
			/*PRS- 2007 12 8 */
			/*PRS+ 2007 12 7 */
			if(!crop_script_fixedRatio && !e.ctrlKey && !keepRatio)cropWidthRatio = cropDiv_dotted.offsetWidth / cropDiv_dotted.offsetHeight;
			/*PRS- 2007 12 7 */
		}

		if(crop_moveCounter==10){
			var tmpLeft = imageDiv_currentLeft + e.clientX - cropEvent_eventX;
			if(tmpLeft<0)tmpLeft=0;
			if((tmpLeft + imageDiv_currentWidth + (cropToolBorderWidth*2))>crop_imageWidth)tmpLeft = crop_imageWidth - imageDiv_currentWidth - (cropToolBorderWidth*2);
			cropDiv_dotted.style.left = tmpLeft + 'px';
			var tmpTop = imageDiv_currentTop + e.clientY - cropEvent_eventY;
			if(tmpTop<0)tmpTop=0;
			if((tmpTop + imageDiv_currentHeight + (cropToolBorderWidth*2))>crop_imageHeight)tmpTop = crop_imageHeight - imageDiv_currentHeight - (cropToolBorderWidth*2);
			cropDiv_dotted.style.top = tmpTop + 'px';

		}

		repositionSmallSquares();
		resizeTransparentSquares();
//		if(updateFormValuesAsYouDrag)cropScript_updateFormValues();
		cropScript_updateFormValues();
		mouseMoveEventInProgress = false;


	}

	function repositionSmallSquares()
	{
		smallSquare_tc.style.left = (Math.floor((cropDiv_dotted.style.width.replace('px','')/1 + (cropToolBorderWidth*2)) /2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_bc.style.left = (Math.floor((cropDiv_dotted.style.width.replace('px','')/1 + (cropToolBorderWidth*2)) /2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_tr.style.left = (cropDiv_dotted.style.width.replace('px','')/1 + (cropToolBorderWidth*2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_rc.style.left = (cropDiv_dotted.style.width.replace('px','')/1 + (cropToolBorderWidth*2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_br.style.left = (cropDiv_dotted.style.width.replace('px','')/1 + (cropToolBorderWidth*2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';

		smallSquare_br.style.top = (cropDiv_dotted.style.height.replace('px','')/1 + (cropToolBorderWidth*2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_bc.style.top = (cropDiv_dotted.style.height.replace('px','')/1 + (cropToolBorderWidth*2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_bl.style.top = (cropDiv_dotted.style.height.replace('px','')/1 + (cropToolBorderWidth*2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_lc.style.top = (Math.floor((cropDiv_dotted.style.height.replace('px','')/1 + cropToolBorderWidth)/2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';
		smallSquare_rc.style.top = (Math.floor((cropDiv_dotted.style.height.replace('px','')/1 + cropToolBorderWidth)/2) - offsetSmallSquares -(cropToolBorderWidth*2)) + 'px';

	}

	function resizeTransparentSquares()
	{

		cropDiv_left.style.width = cropDiv_dotted.style.left;
		cropDiv_right.style.width = Math.max(0,crop_imageWidth - (cropToolBorderWidth*2) - (cropDiv_dotted.style.width.replace('px','')/1 + cropDiv_dotted.style.left.replace('px','')/1)) + 'px';
		cropDiv_right.style.left = (cropDiv_dotted.style.width.replace('px','')/1 + (cropToolBorderWidth*2) + cropDiv_dotted.style.left.replace('px','')/1) + 'px';
		cropDiv_bottom.style.height = Math.max(0,crop_imageHeight - (cropToolBorderWidth*2) - (cropDiv_dotted.style.height.replace('px','')/1 + cropDiv_dotted.style.top.replace('px','')/1)) + 'px';
		cropDiv_bottom.style.top = (cropDiv_dotted.style.height.replace('px','')/1 + (cropToolBorderWidth*2) + cropDiv_dotted.style.top.replace('px','')/1) + 'px';

		cropDiv_top.style.height = cropDiv_dotted.style.top;

		cropDiv_bottom.style.left = cropDiv_dotted.style.left;
		cropDiv_bottom.style.width = (cropDiv_dotted.style.width.replace('px','')/1 + (cropToolBorderWidth*2)) + 'px' ;
		cropDiv_top.style.left = cropDiv_dotted.style.left;
		cropDiv_top.style.width = (cropDiv_dotted.style.width.replace('px','')/1 + (cropToolBorderWidth*2)) + 'px' ;

		if(cropDiv_left.style.width=='0px')cropDiv_left.style.visibility='hidden';else cropDiv_left.style.visibility='visible';
		if(cropDiv_right.style.width=='0px')cropDiv_right.style.visibility='hidden';else cropDiv_right.style.visibility='visible';
		if(cropDiv_bottom.style.width=='0px')cropDiv_bottom.style.visibility='hidden';else cropDiv_bottom.style.visibility='visible';

	}

	function cropScript_updateFormValues()
	{
		/*PRS+ 2007 11 29 */
		var width=(cropInit)?0:cropToolBorderWidth*2;
		var oldWidth=document.getElementById('input_crop_width').value;
		var oldHeight=document.getElementById('input_crop_height').value;
		if (oldWidth==0) oldWidth=crop_originalImageWidth;
		if (oldHeight==0) oldHeight=crop_originalImageHeight;
		/*PRS- 2007 11 29 */
		document.getElementById('input_crop_x').value = Math.round(cropDiv_dotted.style.left.replace('px','')/1 * (crop_originalImageWidth/crop_imageWidth));
		document.getElementById('input_crop_y').value = Math.round(cropDiv_dotted.style.top.replace('px','')/1 * (crop_originalImageHeight/crop_imageHeight));
		/*PRS+ 2007 11 30 */
		updateLockRatio();
		if (!isLocked)
		{
			document.getElementById('input_crop_width').value = Math.round((cropDiv_dotted.style.width.replace('px','')/1 + width) * (crop_originalImageWidth/crop_imageWidth));
			document.getElementById('input_crop_height').value = Math.round((cropDiv_dotted.style.height.replace('px','')/1 + width) * (crop_originalImageHeight/crop_imageHeight));
			if (!cropInit) cropInit=(oldWidth!=document.getElementById('input_crop_width').value)||(oldHeight!=document.getElementById('input_crop_height').value);
		}
		/*PRS- 2007 11 30 */
	}

	function cropScript_stopResizeMove()
	{
		crop_resizeCounter = -1;
		crop_moveCounter = -1;
		cropDiv_dotted.style.cursor = 'move';
		cropScript_updateFormValues();
		if(document.all){
			var div = cropDiv_dotted.getElementsByTagName('DIV')[0];
			div.style.display='block';
		}
	}

	function cropScript_setCropSizeByInput()
	{
		var obj_x = document.getElementById('input_crop_x');
		var obj_y = document.getElementById('input_crop_y');
		var obj_width = document.getElementById('input_crop_width');
		var obj_height = document.getElementById('input_crop_height');

		obj_x.value = obj_x.value.replace(/[^0-9]/gi,'');
		obj_y.value = obj_y.value.replace(/[^0-9]/gi,'');
		obj_width.value = obj_width.value.replace(/[^0-9]/gi,'');
		obj_height.value = obj_height.value.replace(/[^0-9]/gi,'');



		if(obj_x.value.length==0)obj_x.value=0;
		if(obj_y.value.length==0)obj_y.value=0;
		if(obj_width.value.length==0)obj_width.value=crop_originalImageWidth;
		if(obj_height.value.length==0)obj_height.value=crop_originalImageHeight;


		if(obj_x.value>(crop_originalImageWidth-crop_minimumWidthHeight))obj_x.value = crop_originalImageWidth-crop_minimumWidthHeight;
		if(obj_y.value>(crop_originalImageHeight-crop_minimumWidthHeight))obj_y.value = crop_originalImageHeight-crop_minimumWidthHeight;

		if(obj_width.value/1 > crop_originalImageWidth)obj_width.value = crop_originalImageWidth - obj_x.value/1;
		if(obj_height.value/1 > crop_originalImageHeight)obj_height.value = crop_originalImageHeight - obj_y.value/1;

		if(obj_x.value/1 + obj_width.value/1 > crop_originalImageWidth)obj_width.value = crop_originalImageWidth - obj_x.value;
		if(obj_y.value/1 + obj_height.value/1 > crop_originalImageHeight)obj_height.value = crop_originalImageHeight - obj_y.value;



		/*PRS+ 2007 12 7 */
		cropDiv_dotted.style.left = (obj_x.value/1 * (crop_imageWidth / crop_originalImageWidth)) + 'px';
		cropDiv_dotted.style.top = (obj_y.value/1 * (crop_imageHeight / crop_originalImageHeight)) + 'px';
		var width=(cropInit)?0:cropToolBorderWidth*2;
		/*PRS+ 2007 12 8 */
		var oldWidth=cropDiv_dotted.style.width.replace('px','')/1;
		cropDiv_dotted.style.width = (obj_width.value/1 - width)  * (crop_imageWidth / crop_originalImageWidth) + 'px';
		var newWidth=cropDiv_dotted.style.width.replace('px','')/1;
		if (oldWidth!=newWidth)
		{
			if (keepRatio)
			{
				cropDiv_dotted.style.height = (newWidth / cropWidthRatio) + 'px';
				document.getElementById('input_crop_height').value = Math.round(obj_width.value/cropWidthRatio);
			}
		} else {
			var oldHeight=cropDiv_dotted.style.height.replace('px','')/1;
			cropDiv_dotted.style.height = (obj_height.value/1 - width) * (crop_imageHeight / crop_originalImageHeight) + 'px';
			var newHeight=cropDiv_dotted.style.height.replace('px','')/1;
			if (oldHeight!=newHeight && keepRatio)
			{
				cropDiv_dotted.style.width = (newHeight * cropWidthRatio) + 'px';
				document.getElementById('input_crop_width').value = Math.round(obj_height.value*cropWidthRatio);
			}
		}
		/*PRS- 2007 12 8 */
		/*PRS- 2007 12 7 */
		repositionSmallSquares();
		resizeTransparentSquares();
	}



	function cropScript_setBasicEvents()
	{
		document.documentElement.ondragstart = crop_cancelEvent;
		document.documentElement.onselectstart = crop_cancelEvent;
		document.documentElement.onmousemove = cropScript_mouseMove;
		document.documentElement.onmouseup = cropScript_stopResizeMove;

		document.getElementById('input_crop_x').onblur = cropScript_setCropSizeByInput;
		document.getElementById('input_crop_y').onblur = cropScript_setCropSizeByInput;
		document.getElementById('input_crop_width').onblur = cropScript_setCropSizeByInput;
		document.getElementById('input_crop_height').onblur = cropScript_setCropSizeByInput;
		document.getElementById('crop_percent_size').onblur = cropScript_validatePercent;
	}

	function cropScript_validatePercent()
	{
		this.value=this.value.replace(/[^0-9]/gi,'');
		if(this.value.length==0)this.value='1';
		if(this.value/1>crop_maximumPercent)this.value='100';
		if(this.value/1<crop_minimumPercent)this.value=crop_minimumPercent
	}

	function crop_progressBar()
	{
		var div = document.getElementById('crop_progressBar');
		var subDiv = document.createElement('DIV');
		div.appendChild(subDiv);
		subDiv.style.position = 'absolute';
		subDiv.className='crop_progressBar_parentBox';
		subDiv.style.left = '0px';
		var progressBarSquare = document.createElement('DIV');
		progressBarSquare.className='progressBar_square';
		subDiv.appendChild(progressBarSquare);
		var progressBarSquare = document.createElement('DIV');
		progressBarSquare.className='progressBar_square';
		subDiv.appendChild(progressBarSquare);
		var progressBarSquare = document.createElement('DIV');
		progressBarSquare.className='progressBar_square';
		subDiv.appendChild(progressBarSquare);
		crop_progressBarMove();
		crop_hideProgressBar();

	}

	function crop_hideProgressBar()
	{
//PRS+ 20060721
		var div=document.getElementById('crop_progressBar');
		div.style.visibility = 'hidden';
		div.style.display='none';
//PRS- 20060721
	}

	function crop_startProgressBar()
	{
//PRS+ 20060721
		var divDiv = document.getElementById('crop_progressBar').getElementsByTagName('DIV')[0];
		divDiv.style.left = '0px';
		var div=document.getElementById('crop_progressBar');
		div.style.visibility = 'visible';
		div.style.display='block';
//PRS- 20060721
	}

	function crop_progressBarMove()
	{
		var div = document.getElementById('crop_progressBar').getElementsByTagName('DIV')[0];
		var left = div.style.left.replace('px','')/1;
		left = left + 1;
		if(left > div.parentNode.clientWidth)left = 0 - div.parentNode.clientWidth;
		div.style.left = left + 'px';

		setTimeout('crop_progressBarMove()',20);

	}
	
//PRS+ 20101223
	var store_data_width = 0;	
	var store_data_height = 0;
	var store_data_x = 0;
	var store_data_y =0;

	
	function init_values() 
	{
		var obj_width = document.getElementById('input_crop_width');
		var obj_height = document.getElementById('input_crop_height');
		var obj_x = document.getElementById('input_crop_x');
		var obj_y = document.getElementById('input_crop_y');

		store_data_y = 0;
		store_data_x = 0;

		
		if (obj_width.value > 0 && obj_height.value >0) {
			store_data_width = obj_width.value;	
			store_data_height = obj_height.value;
			if (obj_x.value > 0) {store_data_x = obj_x.value;}
			if (obj_y.value > 0) {store_data_y = obj_y.value;}
		} 
	}
	
	function restore_values()  
	{
		if (store_data_width > 0 && store_data_height > 0) {
			var obj_width = document.getElementById('input_crop_width');
			var obj_height = document.getElementById('input_crop_height');
			var obj_x = document.getElementById('input_crop_x');
			var obj_y = document.getElementById('input_crop_y');

			document.getElementById('input_ratio').checked=true;
			cropWidthRatio=store_data_width/store_data_height;

			cropInit=true;
			
			obj_width.value = store_data_width;	
			cropScript_setCropSizeByInput();
			obj_height.value = store_data_height;
			cropScript_setCropSizeByInput();
			obj_x.value = store_data_x;
			cropScript_setCropSizeByInput();
			obj_y.value = store_data_y;
			cropScript_setCropSizeByInput();
			
		}  
	}
//PRS- 20101223


	function init_imageCrop()
	{
		document.getElementById('label_dimension').innerHTML = crop_originalImageWidth + ' x ' + crop_originalImageHeight;
		cropScript_setBasicEvents();
		init_values();
		crop_createDivElements();
		crop_progressBar();
		cropScript_updateFormValues();
		if (autoCrop)
		{
			cropScript_setSize(null);
		}
		restore_values();
	}
