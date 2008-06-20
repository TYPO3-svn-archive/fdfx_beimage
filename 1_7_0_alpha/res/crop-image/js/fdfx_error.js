/*
#########################################################################
#
# License:
#    This program is free software; you can redistribute it and/or
#    modify it under the terms of the MPL Mozilla Public License
#    as published by the Free Software Foundation; either version 1.1
#    of the License, or (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    MPL Mozilla Public License for more details.
#
#    You may have received a copy of the MPL Mozilla Public License
#    along with this program.
#
#    An on-line copy of the MPL Mozilla Public License can be found
#    http://www.mozilla.org/MPL/MPL-1.1.html
#
# 	Copyright (c) 2006 by 4Many Services
#   @author: 	Peter Russ <peter.russ@4many.net>
#   @version:	$Id$
#
#   Date:       01.02.2006
#   Filename:   fdfx_error.js
#
#   Project:    fdfx_be_image
#
##################################################
*/
	var DEBUG=false;
	var errorLogger;
    function debug(text)
    {
        var d=DEBUG;
        DEBUG=true;
        errorLog(text);
        DEBUG=d;
    }
    function errorLog(text)
    {
        if (DEBUG)
        {
            if (errorLogger==null || errorLogger.closed)
            {
                var errorLoggerWindowOpen=true;
                errorLogger=window.open("about:blank","error","width=300,height=400,resizable=yes,scrollbars=yes");
                errorLogger.document.open();
                errorLoggerWindowOpen=false;
            }
            errorLogger.document.writeln(text+"<hr>");
            errorLogger.scrollBy(0, 50);
        }
    }
  function handleError (err, url, errline) {
  	if (DEBUG)
    {
	    var key="JavaScript.Error";
	    var lang=(document.all)?navigator.systemLanguage:navigator.language;
	    switch (lang)
	    {
	        case 'de':
	            var line='<br>Zeile: ';
	            var file=', Datei: ';
	            break;
	        default:
	            var line='<br>Line: ';
	            var file=', file: ';
	            break;
	    }
	    line +=errline;
	    file +=url;
	    var errMsg=err+line+file+"<br><i>("+key+")</i>";
	    debug(errMsg);
    }
    return true;
  }

window.onerror = handleError;
