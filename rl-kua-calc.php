<?php
/*
Plugin Name: Red Lotus Kua Calculator
Plugin URI: http://redlotusletter.com
Description: The Kua Calculator
Author: Larry Huang, Dusty Sun
Version: 2.0
Author URI: http://redlotusletter.com
*/

namespace RedLotusLetter;
define( 'RLL_KUA_CALCULATOR__FILE__', __FILE__ );
class RLL_Kua_Calculator
{

    /***************************************
     * variables for the kua calculator
     ***************************************/
	private $rlkc_user_kua;
	private $rlkc_user_gender;
	private $rlkc_user_birthdate_text;

	// insertion pattern = "{ RL-Kua-Calc }"
    // private $rlkc_pattern = '/\{\s*[Rr][Ll]\-[Kk][Uu][Aa]\-[Cc][Aa][Ll][Cc]\s*\}/';
    // where this and all the other kua calculator-related files exist
    private $rlkc_basepath;
    private $rlkc_baseurl;
    private $rlkc_lnyfile;
    private $rlkc_start_year;
    private $rlkc_end_year;
    private $rlkc_start_year_kua_male;
    private $rlkc_start_year_kua_female;
    private $rlkc_start_year_kua_femaleman;


    public function __construct()
    {

        // shortcode
        add_shortcode('rl-kua-calc', array(
            $this,
            'rl_kua_calc_shortcode'
        ));
    }

    public function set_values()
    {
		$this->testman = 'banr';
        // where this and all the other kua calculator-related files exist
        $this->rlkc_basepath = dirname(__FILE__) . '/';

        // base URL for this plugin, used for image loading and other
        // file references.
        // NOTE: the directory name of this plugin should be the same
        // as the filename of this script.
        $this->rlkc_baseurl = site_url() . "/wp-content/plugins/" . basename(__FILE__, ".php") . '/';

        // file name and location of the lunar new year list file
        $this->rlkc_lnyfile = $this->rlkc_basepath . "lnydates.txt";

        // start and end years for the calculator...
        // make sure you update the lunar new year list file if
        // you change these values!
        $this->rlkc_start_year = 1901;
        $this->rlkc_end_year = 2040;
        $this->rlkc_start_year_kua_male = 9;
        $this->rlkc_start_year_kua_female = 6;
    }
    public function wl($content)
    {
        error_log(print_r($content, true));
    }

    public function rl_kua_calc_shortcode($attributes = [], $content = null)
    {
        // set default values
        $this->set_values();

        //make the array keys and attributes lowercase
        $attributes = array_change_key_case((array)$attributes, CASE_LOWER);

        //override any default attributes with the user defined parameters
        $custom_attributes = shortcode_atts(['post_id' => get_the_ID() , ], $attributes, $tag);

        $post_id = $custom_attributes['post_id'];

        return $this->rlkc_generate_content();
    }
    /***************************************
     * Procedures for the kua calculator
     ***************************************/
    // function rlkc_detect_keyword($content)
    // {
    // 	global $rlkc_pattern;
    // 	if (preg_match($rlkc_pattern, $content))
    // 	{
    // 		return true;
    // 	}
    // 	return false;
    // }
    public function rlkc_generate_form($default_date, $default_gender = 1)
    {

        // $default_date should be a string in yyyy-mm-dd format
        // global $rlkc_start_year, $rlkc_end_year;
        $dt_default_date = new \DateTime($default_date);

        $default_year = $dt_default_date->format("Y");
        $default_month = $dt_default_date->format("m");
        $default_day = $dt_default_date->format("d");

        $monthstring = array(
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
        $rlkc_form = '
	<style type="text/css">
	<!--

	#rl-kua-calc-form form
	{
		background-color: #d8d8d8;
		border: 2px solid #c0c0c0;
		padding: 10px;
	}
    div#rl-kua-calc-form {
		border: 1px solid #e6568c;
		background: #fcf1f842;
		margin-bottom: 20px;
	}
	#rl-kua-calc-form form {
		display: flex;
		flex-direction: row;
		flex-wrap: wrap;
	}

	.rlkc-kua-desc-heading {
		font-weight: bold;
	}
	#rlkc-report-header {
		display: flex;
		flex-direction: row;
		flex-wrap: wrap;
		margin-bottom: 10px;
	}
	.rlkc-left-col {
		flex: 1 1 50%;
	}
	.rlkc-trigram { 
		margin-top: 10px;
		max-width: 150px;
		text-align: center;
	}
	.rlkc-trigram img {
		max-width: 100%;
	}
	.rlkc-right-col {
		flex: 1 1 50%;
		display: flex;
		flex-direction: row;
		flex-wrap: wrap;
	}
	#rlkc-info-report-text {
		flex: 1 1 30%;
	}
	.report-download-header a {
		font-weight: 600;
		text-transform: uppercase;
		font-size: 24px;
	}
	.report-download-text {
		font-weight: 500;
		font-size: 22px;
		font-family: "bodoni" !important;
		color: #6135ae !important;	
	}
	#rlkc-info-report {
		flex: 0 1 60%;
		text-align: right;
		padding-left: 20px;

	}
	#rlkc-info-report img {
		max-width: 100%;	
		height: auto;
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

	div#rl-kua-calc-bday {
		flex: 1 1 60%;
		display: flex;
		flex-direction: row;
		justify-content: center;
		align-items: center;
	}
	div#rl-kua-calc-bday span {
		line-height: 10px;
		font-weight: 600;
	}
	div#rl-kua-calc-bday select {
		margin: 10px !important;
        flex: 1 1 auto;
	}
	#rl-kua-calc-gender {
		flex: 0 1 250px;
		display: flex;
		flex-direction: row;
		flex-wrap: wrap;
		align-items: center;
		justify-content: flex-end;
	}
	#rl-kua-calc-gender div {
		padding-right: 10px;
	}
	#kua-calc-submit {
		flex: 1 1 100%;
	}
	#kua-calc-submit input {
		width: 100%;
		font-family: "Poppins", sans-serif;
		font-weight: 600;
		font-size: 18px;
		letter-spacing: 1px;
		border: none;
		background: #e6568c;
	}
	@media (max-width: 660px) {
		.rlkc-left-col,
		.rlkc-right-col,
		#rlkc-info-report-text,
		#rlkc-info-report {
			flex-basis: 100%;
		}
		.rlkc-right-col {
			padding-top: 20px;
		}
		#rlkc-info-report {
			padding-top: 10px;
			padding-left: 0;
		}
	}
	-->
	</style>


			<div id="rl-kua-calc-form">
	' .
        // make result snap to anchor location
        '	<form name="input" action="#rlkc-result" method="post">
			<div id="rl-kua-calc-bday" style="text-align: left;">
			<span>Birthday: &nbsp;</span>
			';

        // generate the birth month options
        $rlkc_form .= '
			<select name="rlkc_month" style="width: 9em;">
			';

        for ($i = 1;$i < 13;$i++)
        {
            $rlkc_form .= '<option value="' . sprintf("%02d", $i) . '"' . (($i == $default_month) ? ' selected="1">' : '>') . $monthstring[$i] . '</option>';
        }

        $rlkc_form .= '
			</select>
			';

        // generate the birth day options
        $rlkc_form .= '
			<select name="rlkc_day" style="width: 5em;">
			';

        for ($i = 1;$i < 32;$i++)
        {
            $rlkc_form .= '<option value="' . sprintf("%02d", $i) . '"' . (($i == $default_day) ? ' selected="1">' : '>') . $i . '</option>';
        }

        $rlkc_form .= '
			</select>
			';

        // generate the birth year options
        $rlkc_form .= '
			<select name="rlkc_year" style="width: 6em;">
			';

        for ($i = $this->rlkc_start_year;$i < $this->rlkc_end_year + 1;$i++)
        {
            $rlkc_form .= '<option value="' . $i . '"' . (($i == $default_year) ? ' selected="1">' : '>') . $i . '</option>';
        }

        $rlkc_form .= '
			</select>
			';

        $rlkc_form .= '</div>
			<div id="rl-kua-calc-gender">

	<div>	
		<p style="font-weight: 600;">Gender:</p>
	</div>	
		';

        // generate the gender option
        $rlkc_form .= '
	<div>
	<p>
			<input type="radio" name="rlkc_gender" value="1" id="rlkc_gender_male" style="width: auto;" ' . ($default_gender ? 'checked="checked" />' : '/>') . '
			<label for="rlkc_gender_male">Male</label>
	</p>
	</div>
	<div>
	<p>
			<input type="radio" name="rlkc_gender" value="0" id="rlkc_gender_female" style="width: auto;" ' . ($default_gender ? '/>' : 'checked="checked" />') . ' 
			<label for="rlkc_gender_female">Female</label>
	</p>	
	</div>

	';

        $rlkc_form .= '</div>
			';

        // generate the submit button
        $rlkc_form .= '

				<div id="kua-calc-submit" style="text-align: center;">
				<input type="submit" value="Calculate My Personal Kua Number" />
				</div>
			';

        // generate the form footer
        $rlkc_form .= '

			</form>

			</div>
			';

        return $rlkc_form;
    }

    public function rlkc_find_lny($filename, $year)
    {
        // $year should be a string in yyyy format
        $handle = fopen($filename, "r");
        if ($handle)
        {
            while (!feof($handle))
            {
                fscanf($handle, "%s", $lnydate);

                if ($year == substr($lnydate, 0, 4))
                {
                    fclose($handle);
                    return new \DateTime($lnydate);
                }
            }
        }
        fclose($handle);
        return false;
    }

    public function rlkc_date_valid($date)
    {
        // $date should be a string in yyyy-mm-dd format
        $converted_date = new \DateTime($date);

        return ($date == $converted_date->format('Y-m-d'));
    }

    public function rlkc_get_params()
    {
        $birthdate = $_POST["rlkc_year"] . "-" . $_POST["rlkc_month"] . "-" . $_POST["rlkc_day"];

        $gender = $_POST["rlkc_gender"];

        return array(
            $birthdate,
            $gender
        );
    }

    public function rlkc_calculate_kua($dt_birthdate, $gender)
    {

        // global
        // 	$rlkc_start_year,
        // 	$rlkc_start_year_kua_male,
        // 	$rlkc_start_year_kua_female,
        // 	$rlkc_lnyfile;
        $kua = false;

        $dt_lnydate = $this->rlkc_find_lny($this->rlkc_lnyfile, $dt_birthdate->format("Y"));

        // get the year difference
        $yeardiff = $dt_birthdate->format("Y") - $this->rlkc_start_year;

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
            $kua = (($this->rlkc_start_year_kua_male - ($yeardiff % 9)) % 9);
            if ($kua == 0) $kua = 9;
        }
        else
        {
            $kua = (($this->rlkc_start_year_kua_female + ($yeardiff % 9)) % 9);
            if ($kua == 0) $kua = 9;
        }
        return $kua;
    }

    public function rlkc_generate_kua_content_style()
    {
        $kua_content_style = '
	<style type="text/css">
	<!--
	
	-->
	</style>
	';

        return $kua_content_style;
    }

    public function rlkc_get_kua_content($kua_number, $gender, $birthdate_text)
    {

        $kua_content = "";

        $kua_file = file_get_contents($this->rlkc_basepath . "kua" . $kua_number . (($kua_number == 5) ? ($gender ? "male" : "female") : "") . ".php");

        $kua_footer = file_get_contents($this->rlkc_basepath . "kua-content-footer.php");

        // generate the styling for the kua calculator stuff
        $kua_content .= $this->rlkc_generate_kua_content_style();

        $gender_text = $gender ? "Male" : "Female";
		$this->rlkc_user_kua = $kua_number;
		$this->rlkc_user_gender = $gender_text;
		$this->rlkc_user_birthdate_text = $birthdate_text;
        // read in the appropriate kua information and
        // capture eval output
        ob_start();
        eval(' ?> ' . $kua_file . $kua_footer . ' <? ');
        $kua_content .= ob_get_contents();
        ob_end_clean();

        return $kua_content;
    }

    public function rlkc_generate_content()
    {

        // global $rlkc_pattern, $rlkc_basepath, $rlkc_lnyfile;
        $kc_content = "";

        if ((isset($_POST["rlkc_year"])) && (isset($_POST["rlkc_month"])) && (isset($_POST["rlkc_day"])) && (isset($_POST["rlkc_gender"])))
        {
            list($birthdate, $gender) = $this->rlkc_get_params();

            if (!$this->rlkc_date_valid($birthdate))
            {

                // date is not valid
                $kc_content .= "<h3>Sorry, the date you have chosen is invalid.</h3>";

                $kc_content .= $this->rlkc_generate_form(date("Y-m-d"));
            }
            else
            {

                $dt_birthdate = new \DateTime($birthdate);

                $kua_number = $this->rlkc_calculate_kua($dt_birthdate, $gender);

                $kc_content .= $this->rlkc_generate_form($dt_birthdate->format("Y-m-d") , $gender);

                // add anchor for result
                $kc_content .= "<div id=\"rlkc-result\"></div>";

                $kc_content .= $this->rlkc_get_kua_content($kua_number, $gender, $dt_birthdate->format("F j, Y"));
            }
        }
        else
        {

            /* $kc_add = rlkc_generate_form(date("Y-m-d"));
            
            $kc_content = "";
            $kc_content .= $kc_add; */
            $kc_content .= $this->rlkc_generate_form(date("Y-m-d"));
        }

        return $kc_content;
    }

    // This is the main entry function for the Red Lotus Kua Calculator
    // function rlkc_main($content)
    // {
    // 	// If the keyword doesn't exist, don't do anything
    // 	// and get out immediately.
    // 	if (!rlkc_detect_keyword($content))
    // 	{
    // 		return $content;
    // 	}
    // 	global $rlkc_pattern, $rlkc_lnyfile;
    // 	// build kua content
    

    // 		$content = preg_replace(
    // 			$rlkc_pattern,
    // 			rlkc_generate_content(),
    // 			$content);
    // 	return $content;
    // }
    

    // allow wordpress to hook into here
    // if (function_exists('add_filter'))
    // {
    // 	add_filter ('the_content', 'rlkc_main');
    // }
    // else
    // {
    // 	echo ('Direct access is not allowed.');
    // }

	public function rlkc_report_header() {
		ob_start(); ?>
		<div id="rlkc-kua-content">

			<h1 class="rlkc-h1">Your KUA number is: <?php echo $this->rlkc_user_kua;?></h1>


			<div id="rlkc-report-header">

			<div class="rlkc-left-col">

				<p>Birthday: <? echo $this->rlkc_user_birthdate_text; ?><br />Gender: <? echo $this->rlkc_user_gender; ?></p>
		<?php $contents = ob_get_contents(); 
		ob_end_clean();
		return $contents;
	}

	public function rlkc_show_trigram($number, $name) {
		ob_start(); ?>

		<div class="rlkc-trigram">
          <img src="<? echo plugins_url('media/kua' . $number . '_' . strtolower($name) . '.png',RLL_KUA_CALCULATOR__FILE__); ?>"  name="<?php echo $name; ?>" />
          <p><?php echo $name; ?></p>
        </div>
		<?php $contents = ob_get_contents(); 
		ob_end_clean();
		return $contents;
	}
    
	public function rlkc_show_report_cover() {

		if($this->rlkc_user_kua == 5) {
			$file_name = 'Kua-Number-Report--Kua-' . $this->rlkc_user_kua . '-' . $this->rlkc_user_gender . '.pdf';

		} else {
			$file_name = 'Kua-Number-Report--Kua-' . $this->rlkc_user_kua . '.pdf';
		}
		
		ob_start(); ?>

		</div> <!-- .rlkc-left-col -->
		<div class="rlkc-right-col">
				<div id="rlkc-info-report-text">
					<p class="report-download-header"><a href="<?php echo plugins_url('media/' . $file_name, RLL_KUA_CALCULATOR__FILE__);?>" target="_blank">Free Download</a></p>
					<p class="report-download-text">Get This FREE 6 Page Report with Details about Your Unique Kua Number.</p>
				</div>
				<div id="rlkc-info-report">
				<a href="<?php echo plugins_url('media/' . $file_name, RLL_KUA_CALCULATOR__FILE__);?>" target="_blank"><img src="<? echo plugins_url('media/Kua-number-report-cover.png',RLL_KUA_CALCULATOR__FILE__);?>" name="Kua Number Report Cover" /></a>
				</div>
			</div><!-- .rlkc-right-col -->
			
		</div> <!-- #rlkc-report-header -->
		<?php $contents = ob_get_contents(); 
		ob_end_clean();
		return $contents;
	}
} // end class RLL_Kua_Calculator
$rll_kua_calculator = new RLL_Kua_Calculator();

