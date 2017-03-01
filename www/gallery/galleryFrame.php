<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SDH Babice - fotogalerie</title>
<style type="text/css">
body {

margin:5px;
padding:5px;
font:12px arial, Helvetica, sans-serif;
color:#222;
}
</style>
<link type="text/css" rel="stylesheet" href="foliogallery/foliogallery.css" />
<link type="text/css" rel="stylesheet" href="colorbox/colorbox.css" />
<script type="text/javascript" src="foliogallery/jquery.1.11.js"></script>
<script type="text/javascript" src="colorbox/jquery.colorbox-min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    // initiate colorbox
	$('.albumpix').colorbox({rel:'albumpix', maxWidth:'96%', maxHeight:'96%', slideshow:true, slideshowSpeed:3500, slideshowAuto:false});
	$('.vid').colorbox({rel:'albumpix', iframe:true, width:'80%', height:'90%'});
});
</script>
</head>
<body>

<br />
<?php $_REQUEST['fullalbum']=1; include 'foliogallery.php'; ?>

<br/>
</body>
</html>