<?php
/*
Plugin Name: Red Lotus Kua Calculator
Plugin URI: http://redlotusletter.com
Description: The Kua Calculator
Author: Larry Huang
Version: 1.1.2
Author URI: http://redlotusletter.com
*/


/***************************************
 * Constants for the kua calculator
 ***************************************/

// insertion pattern = "{ RL-Kua-Calc }"
$rlkc_pattern = '/\{\s*[Rr][Ll]\-[Kk][Uu][Aa]\-[Cc][Aa][Ll][Cc]\s*\}/';

// where this and all the other kua calculator-related files exist
$rlkc_basepath = dirname(__FILE__) . '/';

// base URL for this plugin, used for image loading and other
// file references.
// NOTE: the directory name of this plugin should be the same
// as the filename of this script.
$rlkc_baseurl = "http://" . $_SERVER['SERVER_NAME'] . "/wp-content/plugins/" . basename(__FILE__, ".php") . '/';

// file name and location of the lunar new year list file
$rlkc_lnyfile = $rlkc_basepath . "lnydates.txt";

// start and end years for the calculator...
// make sure you update the lunar new year list file if 
// you change these values!
$rlkc_start_year = 1901;
$rlkc_end_year = 2040;
$rlkc_start_year_kua_male = 9;
$rlkc_start_year_kua_female = 6;



/***************************************
 * Procedures for the kua calculator
 ***************************************/
function rlkc_detect_keyword($content)
{

	global $rlkc_pattern;

	if (preg_match($rlkc_pattern, $content))
	{
		return true;
	}
	
	return false;
}

function rlkc_generate_form($default_date, $default_gender = 1)
{

	// $default_date should be a string in yyyy-mm-dd format

	global $rlkc_start_year, $rlkc_end_year;

	$dt_default_date = new DateTime($default_date);

	$default_year = $dt_default_date->format("Y");
	$default_month = $dt_default_date->format("m");
	$default_day = $dt_default_date->format("d");

	$monthstring = array (
		1 => "January",
		2 => "February",
		3 => "March",
		4 => "April",
		5 => "May",
		6 => "June",
		7 => "July",
		8 => "August",
		9 => "September",
		10 => "October",
		11 => "November",
		12 => "December"
	);
	
	// generate form header
	$rlkc_form = 
		'
<style type="text/css">
<!--

#rl-kua-calc-form form
{
	background-color: #d8d8d8;
	border: 2px solid #c0c0c0;
	padding: 10px;
}

-->
</style>

	<center>	
		<div id="rl-kua-calc-form" style="width: 96%;" >
' .
// make result snap to anchor location
'	<form name="input" action="#rlkc-result" method="post">
		<div id="rl-kua-calc-bday" style="text-align: left;">
		Birthday: &nbsp;
		';

	// generate the birth month options
	$rlkc_form .=
		'
		<select name="rlkc_month" style="width: 9em;">
		';

	for ($i = 1; $i < 13; $i++)
	{	
		$rlkc_form .=
			'<option value="' . sprintf("%02d",$i) . '"' . 
				(($i == $default_month) ? ' selected="1">' : '>') .
				$monthstring[$i] . '</option>';
	}
		
	$rlkc_form .=
		'
		</select>
		';

	// generate the birth day options
	$rlkc_form .=
		'
		<select name="rlkc_day" style="width: 5em;">
		';

	for ($i = 1; $i < 32; $i++)
	{
		$rlkc_form .= 
			'<option value="' . sprintf("%02d",$i) . '"' .
				(($i == $default_day) ? ' selected="1">' : '>') . 
				$i . '</option>';
	}

	$rlkc_form .=
		'
		</select>
		';

	// generate the birth year options
	$rlkc_form .=
		'
		<select name="rlkc_year" style="width: 6em;">
		';

	for ($i = $rlkc_start_year; $i < $rlkc_end_year + 1; $i++)
	{
		$rlkc_form .= 
			'<option value="' . $i . '"' .
				(($i == $default_year) ? ' selected="1">' : '>') . 
				$i . '</option>';
	}

	$rlkc_form .=
		'
		</select>
		';


	$rlkc_form .=
		'</div>
			<br />
		<div id="rl-kua-calc-gender" style="text-align: left; float: left; width: 100%;">
<div style="float: left; width: auto;">
<div style="float: left; padding-right: 5px;">	
<p>	
	Gender: 
</p>
</div>	
	';


	// generate the gender option
	$rlkc_form .=
		'
<div style="float: left; padding: 0 10px;">
<p>
		<input type="radio" name="rlkc_gender" value="1" id="rlkc_gender_male" style="width: auto;" ' . ($default_gender ? 'checked="checked" />' : '/>') . '
		<label for="rlkc_gender_male">Male</label>
</p>
</div>
<div style="float: left; padding: 0 10px;">
<p>
		<input type="radio" name="rlkc_gender" value="0" id="rlkc_gender_female" style="width: auto;" ' . ($default_gender ? '/>' : 'checked="checked" />') . ' 
		<label for="rlkc_gender_female">Female</label>
</p>	
</div>
</div>
';
		
	
	$rlkc_form .=
		'</div>
		';

	// generate the submit button
	$rlkc_form .=
		'
			<br />
			<div id="kua-calc-submit" style="text-align: center;">
			<input type="submit" value="Go" />
			</div>
		';

	// generate the form footer
	$rlkc_form .=
		'

		</form>

		</div>
		</center>
		';

	return $rlkc_form;

}


function rlkc_find_lny($filename, $year)
{

	// $year should be a string in yyyy format

	$handle = fopen($filename, "r");
	if ($handle)
	{
		while (!feof($handle)) 
		{
			fscanf ($handle, "%s", $lnydate);

			if ($year == substr($lnydate, 0, 4))
			{
				fclose($handle);
				return new DateTime($lnydate);
			}
		}

	}
	fclose($handle);
	return false;
}



function rlkc_date_valid($date)
{
	// $date should be a string in yyyy-mm-dd format

	$converted_date = new DateTime($date);

	return ($date == $converted_date->format('Y-m-d'));
}


function rlkc_get_params()
{
		$birthdate = 
			$_POST["rlkc_year"] . "-" .
			$_POST["rlkc_month"] . "-" .
			$_POST["rlkc_day"];
	
		$gender = $_POST["rlkc_gender"];

		return array($birthdate, $gender);

}


function rlkc_calculate_kua($dt_birthdate, $gender)
{

	// $dt_birthdate should be a DateTime object

	global 
		$rlkc_start_year, 
		$rlkc_start_year_kua_male, 
		$rlkc_start_year_kua_female,
		$rlkc_lnyfile;

	$kua = false;

	$dt_lnydate = rlkc_find_lny($rlkc_lnyfile, $dt_birthdate->format("Y"));

	// get the year difference
	$yeardiff = $dt_birthdate->format("Y") - $rlkc_start_year;

	// if the month and day of the birthdate is earlier than the 
	// lunar new year month and day, then that birthday counts for the
	// prior lunar year, so correct for that.
	if ($dt_birthdate < $dt_lnydate)
	{
		$yeardiff--;
	}

	// male = 1, female = 0
	if ($gender)
	{
		$kua = (($rlkc_start_year_kua_male - ($yeardiff % 9)) % 9);
		if ($kua == 0) $kua = 9;
	}
	else
	{
		$kua = (($rlkc_start_year_kua_female + ($yeardiff % 9)) % 9);
		if ($kua == 0) $kua = 9;
	}
	return $kua;

}

function rlkc_generate_kua_content_style()
{
	$kua_content_style = '
<style type="text/css">
<!--
#rlkc-kua-content {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 12px;
	font-style: normal;
	font-variant: normal;
	line-height: 16px;
	margin-top: 15px;
}
.rlkc-kua-desc-heading {
	font-weight: bold;
}
#rlkc-info-text {
	float: left;
	width: 50%;
	height: auto;
}
#rlkc-info-trigram {
	float: right;
	width: 50%;
	height: 100%;
	text-align: center;
}
#rlkc-info-set {
	float: left;
	width: 80%;
}
.rlkc-h1 {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 24px;
	font-weight: bold;
	font-variant: normal;
	color: #000000;
	text-align: left;
}
.rlkc-kua-sample-pic {
	border-top-width: 0px;
	border-right-width: 0px;
	border-bottom-width: 0px;
	border-left-width: 0px;
}
-->
</style>
';

	return $kua_content_style;


}


function rlkc_get_kua_content($kua_number, $gender, $birthdate_text)
{

	global $rlkc_basepath, $rlkc_baseurl;
	$kua_content = "";

	$kua_file = file_get_contents(
		$rlkc_basepath . 
		"kua" . $kua_number . (($kua_number == 5) ? ($gender ? "male" : "female") : "") . 
		".php"
	);

$kua_footer = file_get_contents(
       $rlkc_basepath .
      "kua-content-footer.php"
	);

	// generate the styling for the kua calculator stuff
	$kua_content .= rlkc_generate_kua_content_style();

$gender_text = $gender ? "Male" : "Female";


	// read in the appropriate kua information and 
	// capture eval output
	ob_start();
	eval(' ?> ' . $kua_file . $kua_footer . ' <? ');
	$kua_content .= ob_get_contents();
	ob_end_clean();

	return $kua_content;

}

function rlkc_generate_content() {

	global $rlkc_pattern, $rlkc_basepath, $rlkc_lnyfile;

	$kc_content = "";

		if (
			(isset($_POST["rlkc_year"])) &&
			(isset($_POST["rlkc_month"])) &&
			(isset($_POST["rlkc_day"])) &&
			(isset($_POST["rlkc_gender"]))
		)
		{
			list($birthdate, $gender) = rlkc_get_params();


			if (!rlkc_date_valid($birthdate))
			{
		
				// date is not valid
				$kc_content .= "<h3>Sorry, the date you have chosen is invalid.</h3>";

				$kc_content .= rlkc_generate_form(date("Y-m-d"));
				

			}
			else
			{

				$dt_birthdate = new DateTime($birthdate);

				$kua_number = rlkc_calculate_kua($dt_birthdate, $gender);


				$kc_content .= 	rlkc_generate_form($dt_birthdate->format("Y-m-d"), $gender);	


// add anchor for result
$kc_content .= "<a name=\"rlkc-result\"></a>";

				$kc_content .= rlkc_get_kua_content($kua_number, $gender, $dt_birthdate->format("F j, Y"));

			}
		}
		else
		{			

			/* $kc_add = rlkc_generate_form(date("Y-m-d"));

			$kc_content = "";
			$kc_content .= $kc_add; */
			$kc_content .= rlkc_generate_form(date("Y-m-d")); 

		}
		
		return $kc_content;	
}



// This is the main entry function for the Red Lotus Kua Calculator
function rlkc_main($content)
{

	// If the keyword doesn't exist, don't do anything
	// and get out immediately.
	if (!rlkc_detect_keyword($content))
	{
		return $content;
	}

	global $rlkc_pattern, $rlkc_lnyfile;

	// build kua content
	

		$content = preg_replace(
			$rlkc_pattern, 
			rlkc_generate_content(),
			$content);

	return $content;

}



// allow wordpress to hook into here
if (function_exists('add_filter'))
{
	add_filter ('the_content', 'rlkc_main');
}
else
{
	echo ('Direct access is not allowed.');
}

?>