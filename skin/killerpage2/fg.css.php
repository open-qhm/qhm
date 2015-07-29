<?php

$fgstyle = <<<EOD

/* General
----------------------------------------------------------------------------- */
div#body{
	font-size:15px;
	font-family:%FONT%;
}
img{
	border:none;
	max-width: 100%;
}

div#wrap_content{
	font-size:15px;
	font-family:%FONT%;
}

@media (max-width: 768px) {
  div#wrapper {
    margin-top: 0 !important;
    max-width: 100%;
  }
  div#body {
    width: auto !important;
    max-width: 100%;
    box-sizing: border-box;
    margin-left: 15px !important;
    margin-right: 15px !important;
  }
}

/* CenterBar
----------------------------------------------------------------------------- */
div#body h1,
div#body input{
	line-height:1em;
}
div#body h1{
	font-family:%HeaderFONT%;
	margin:5px 0px 5px 0px;
	font-size:0.8em;
}
div#body h1 a{
	font-family:%HeaderFONT%;
	text-decoration:none;
}
div#body h2{
	font-family:%HeaderFONT%;
	color: %COLOR%;
	font-size: 28px;
	text-align: center;
	line-height: 1.5em;
	margin:1.5em 0px 1.5em 0px;
}
div#body h2 a{
	text-decoration:none;
}
div#body h3{
	font-family:%HeaderFONT%;
	font-size: 22px;
	color: %COLOR%;
	line-height: 1.5em;
	text-align: center;
	margin:2em 0px 1em 0px;
}
div#body h3 a{
	text-decoration:none;
}
div#body h4{
	font-family:%HeaderFONT%;
	font-size: 18px;
	color: %COLOR%;
	line-height: 1.5em;
	margin:1.5em 0px 1em 0px;
}
div#body h4 a{
	text-decoration:none;
}
div#body p{
	margin:1em 0px;
}
div#body ul.list1{
	padding-left:16px;
	margin-left:16px;
	margin-top:1em;
	margin-bottom:1em;
}
div#body ul.list2{
	padding-left:16px;
	margin-left:16px;
}
div#body ul.list3{
	padding-left:16px;
	margin-left:16px;
}
div#body ol.list1{
	padding-left:16px;
	margin-left:16px;
	margin-top:1em;
	margin-bottom:1em;
	list-style-type:decimal;
}
div#body ol.list2{
	padding-left:16px;
	margin-left:16px;
	list-style-type:lower-roman;
}
div#body ol.list3{
	padding-left:16px;
	margin-left:16px;
	list-style-type:lower-alpha;
}
div#body textarea{
	max-width:100%;
}

div#body table{
	word-break:normal;
	word-wrap: break-word;
	max-width:80%;
}
pre{
	border:1px #f00 solid;
	font-family:%FONT%;
	background-color:#eee;
	border-top:#666 1px solid;
	border-right:#888899 2px solid;
	border-bottom:#888899 2px solid;
	border-left:#666 1px solid;
	margin:15px;
	padding:.5em;
	white-space:normal;
	overflow:auto;
	color:black;
}
blockquote{
	margin:1em 2em 1em;
	padding-left:0.3em;
}
/*----- customizing for bullet design ----*/
dl{
	margin:1.5em 1.5em;
}
dt{
	float:left;
	font-family: 'Arial', sans-serif;
}
dd{
	margin-left: 25px;
}
em{
	font-style:italic;
}
strong{
	font-weight:bold;
}
thead td.style_td,
tfoot td.style_td{
	color:inherit;
	background-color:#eee;
	border-color: #333333;
}
thead th.style_th,
tfoot th.style_th{
	color:inherit;
	background-color: #eee;
	border-color: #333333;
}
.style_table{
	border-color: #111;
	margin: 10px auto;
	text-align:left;
	background-color:#111;
}
.style_th{
	padding:5px;
	border-color: #666;
	margin:1px;
	text-align:center;
	color:inherit;
	background-color: #eee;
}
.style_td{
	padding:5px;
	border-color: #666;
	margin:1px;
	color:inherit;
	background-color: #fff;
}
div.ie5{
	text-align:center;
}
span.noexists{
	background-color:#FFFACC;
}
span.size1{
	font-size:xx-small;
}
span.size2{
	font-size:x-small;
}
span.size3{
	font-size:small;
}
span.size4{
	font-size:medium;
}
span.size5{
	font-size:large;
}
span.size6{
	font-size:x-large;
}
span.size7{
	font-size:xx-large;
}
span.handline{
	text-decoration:underline;
}
hr{
	margin:5px 0px;
}
.small{
	font-size:80%;
}

/* Header
----------------------------------------------------------------------------- */
#header{
	font-family:Arial,"Sans Serif","generic font family";
}
#header a{
	text-decoration:none;
}
#logo a{
	text-decoration:none;
}

/* Footer
----------------------------------------------------------------------------- */
div#footer a{
	text-decoration:none;
}

/* Box
---------------------------------------------------------------------------- */

div.bluebox1{
	max-width: 100%;
	border: solid 1px #33a;
	background-color: #fff;
	text-align:left;
	padding: 0px 10px;
}

div.bluebox2{
	max-width: 100%;
	border: solid 1px #33a;
	background-color: #eef;
	text-align:left;
	padding: 0px 10px;
}

div.bluebox3{
	max-width: 100%;
	border: solid 1px #33a;
	background-color: #ffe;
	text-align:left;
	padding: 0px 10px;
}

div.bluebox4{
	max-width: 100%;
	border:none;
	background-color: #eef;
	text-align:left;
	padding: 0px 10px;
}

div.bluebox5{
	max-width: 100%;
	border:none;
	background-color: #ddf;
	text-align:left;
	padding: 0px 10px;
}

div.redbox1{
	max-width: 100%;
	border: solid 1px #f00;
	background-color: #fff;
	text-align:left;
	padding: 0px 10px;
}

div.redbox2{
	max-width: 100%;
	border: solid 1px #f00;
	background-color: #fee;
	text-align:left;
	padding: 0px 10px;
}

div.redbox3{
	max-width: 100%;
	border: solid 1px #f00;
	background-color: #ffe;
	text-align:left;
	padding: 0px 10px;
}

div.redbox4{
	max-width: 100%;
	border:none;
	background-color: #fee;
	text-align:left;
	padding: 0px 10px;
}

div.redbox5{
	max-width: 100%;
	border:none;
	background-color: #fdd;
	text-align:left;
	padding: 0px 10px;
}

div.graybox1{
	max-width: 100%;
	border: solid 1px #000;
	background-color: #fff;
	text-align:left;
	padding: 0px 10px;
}

div.graybox2{
	max-width: 100%;
	border: solid 1px #000;
	background-color: #eee;
	text-align:left;
	padding: 0px 10px;
}

div.graybox3{
	max-width: 100%;
	border: solid 1px #000;
	background-color: #ffe;
	text-align:left;
	padding: 0px 10px;
}

div.graybox4{
	max-width: 100%;
	border:none;
	background-color: #eee;
	text-align:left;
	padding: 0px 10px;
}

div.graybox5{
	max-width: 100%;
	border:none;
	background-color: #ddd;
	text-align:left;
	padding: 0px 10px;
}
EOD;

//----------------------------------------------------------

//set form get values
$font   = isset($_GET['font'])   ? $_GET['font']    : 'gg';
$color   = isset($_GET['color'])   ? $_GET['color']    : 'red';

//font color settings ; fgstyle is output css text;
$fgstyle = str_replace('%COLOR%',$color,$fgstyle);

//header font setting ; default is gothic font
$hfont = substr($font,0,1);
$bfont = substr($font,1,2);

$mincho = '"ヒラギノ明朝 Pro W3","ＭＳ Ｐ明朝", serif';
$gothic = 'Arial,sans-serif';

if($hfont === "m"){
	$fgstyle = str_replace('%HeaderFONT%',$mincho,$fgstyle);
}
else{
	$fgstyle = str_replace('%HeaderFONT%',$gothic,$fgstyle);
}

if($bfont === "m"){
	$fgstyle = str_replace('%FONT%',$mincho,$fgstyle);
}
else{
	$fgstyle = str_replace('%FONT%',$gothic,$fgstyle);
}


//------------------------------------
//
// Output Header (HTTP PROTCOL)
//

header('Server: X-Powered-By: PHP');
header('Content-Type: text/css');
echo '@charset "UTF-8";';
echo $fgstyle;

?>