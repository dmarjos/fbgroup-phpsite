<?php
/**
 * @ignore
 * @subpackage Controller
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This is the standard error manager for views<br>
 *
 * @author Daniel Marjos <dmarjos@gmail.com>
 * @version 1.0
 */
class Base_Controller_View_Error {
	
	/**
	 * Use this method to assign a value to a view's variable
	 * @param string $var The variable to be assigned
	 * @param mixed $val The value
	 */
	function show($title,$message) {
		echo '
<html>
<head>
<title>404 Page Not Found</title>
<style type="text/css">

body {
background-color:	#fff;
margin:				40px;
font-family:		Lucida Grande, Verdana, Sans-serif;
font-size:			12px;
color:				#000;
}

#content  {
border:				#999 1px solid;
background-color:	#fff;
padding:			20px 20px 12px 20px;
}

h1 {
font-weight:		normal;
font-size:			14px;
color:				#990000;
margin: 			0 0 4px 0;
}
</style>
</head>
<body>
	<div id="content">
		<h1>'.$title.'</h1>
		<p>'.$message.'</p>	</div>

</body>
</html>
		';
		die();
	}
}