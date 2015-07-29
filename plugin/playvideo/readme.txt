****************************************************************************
FLVplayerLite version 1.8
http://www.flvplayerlite.com
****************************************************************************

I. Description

    This package contains the free edition of FLVplayerLite.   
    It supports the following features:

    o   Streams FLV videos and H.264 encoded videos
    o   Displays JPG or SWF as preview
    o   Fullscreen view with correct aspect ratio
    o   Smooths the image preventing jagged edges
    o   Uses Mousewheel and Keyboard for control
    o   Intelligent, contextual and autohiding graphics user interface
    
II. File List

    o   normal.html - a common example
    o   autoPlay.html - an example with autoPlay set to true
    o   noThumb.html - an example with no jpg thumbnail
    o   noWatermark.html - an example with hidden watermark
    o   noSeekbar.html - an example with no seekbar
    o   noLoop.html - an example with autoloop disabled
    o   aspectRatioFit.html - by default the video fits itself
    o   aspectRatioOriginal.html - the original size of your video
    o   aspectRatioCut.html - cuts out the video
    o   aspectRatioStretch.html - stretches the video
    o   readme.txt   - this file    
    o   swf/playerLite.swf  - the tiny, smart player
    o   swf/expressInstall.swf - features automatic upgrade of flash
    o   js/swfobject.js - the popular javascript for flash embedding

III. Requirements
	
    1.	This player is intended only for online use with broadband connections.
	Slow connections may suffer of stuttering and long loading times.

    2.  A CPU capabable of video playback for the chosen definition.

    3.  Hosting with streaming support and a FTP client to upload files in there.


IV. How to Use

    1.  Unpak everything into your directory.

    2.  Prepare your FLV video and thumbnail. Keep the same width and height
	for both files.
		
	NOTE: thumbnail preview supports jpg and swf files.

    3.  Open the example "index.html" file with an html editor.

    4.	Locate the javascript between the head tags and replace the flashvars
	with your parameters. You must specify width, height, absolute path of
	both FLV and thumbnail files as well if the video should autoplay or not
	by setting it to "true" or "false".
	
	NOTE: if you leave the thumbnail path empty, like this "", the player
	will seek the first frame of the video and show it as preview. By setting
	autoplay to true no preview will be shown.

    5.	Copy the whole edited javascript and paste it into your destination document
	between the head tags. Remember to include also "swfobject.js" as shown in 
	the example.

    6.  Now write down a div with id "playerLite", like this: <div id="playerLite"></div>
	
	This div will display the video player and you can edit its parameters with
	your custom style sheet. Place the alternative content inside these div for 
	those who don't have the requested version of flash player installed.

    	NOTE: you can place this div anywhere you want, just put it inside the body tag.

    7.  Save, publish and test your document.


VI. History:

    Version 1.1 - First release
    Version 1.2 - Mouse autohiding fix & Playlist Addon
    Version 1.3 - Minor improvements, smaller default watermark
    Version 1.4 - Added seekbar, thumbnail reflections and autoLoop support
    Version 1.5 - Hotfix for seekbar in fullscreen, added variables for playlist
    Version 1.6 - Fixed embedding of playlist inside another flash movie
    Version 1.7 - Minor bugfixes, added metadetection of video size and blur on reflections
    Version 1.8 - Introduced custom Aspectratio support, thumbnail now shows on rewind
    
VII. Compatibility

    This player has been tested under the following browsers:

        Internet Explorer
	Mozilla Firefox
	Safari
	Opera
	Google Chrome

VIII. Known Issues

    Mousewheel might not work on Mac.

IX. Additional Resources

    Visit http://www.flvplayerlite.com for updates and support.