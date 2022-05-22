<?php
/*
Plugin Name: Red Lotus Kua Calculator
Plugin URI: http://redlotusletter.com
Description: The Kua Calculator
Author: Larry Huang, Dusty Sun
Version: 2.1.1
Author URI: http://redlotusletter.com
*/

namespace RedLotusLetter;
use \DustySun\WP_Settings_API\v2 as DSWPSettingsAPI;
//Include the admin panel page
require_once( dirname( __FILE__ ) . '/lib/dustysun-wp-settings-api/ds_wp_settings_api.php');
//Include the admin panel page
require_once( dirname( __FILE__ ) . '/rl-kua-calc-admin.php');
define( 'RLL_KUA_CALCULATOR__FILE__', __FILE__ );
class RLL_Kua_Calculator
{
    private $rll_kua_calc_json_file;
    private $rll_kua_calc_settings_obj;
    public $current_settings;
    public $rll_kua_calc_main_settings;

    /***************************************
     * variables for the kua calculator
     ***************************************/
    private $report_url;
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
        // get the settings
        $this->rll_kua_calc_create_settings();
    }
    public function rll_kua_calc_create_settings() {

        // set the settings api options
        $ds_api_settings = array(
          'json_file' => plugin_dir_path( __FILE__ ) . '/rl-kua-calc.json'
        );
        $this->rll_kua_calc_settings_obj = new DSWPSettingsAPI\SettingsBuilder($ds_api_settings);

        // get the settings
        $this->current_settings = $this->rll_kua_calc_settings_obj->get_current_settings();

        // Get the plugin options
        $this->rll_kua_calc_main_settings = $this->rll_kua_calc_settings_obj->get_main_settings();
        
        // SET THE URL
        // $this->report_url = '/resources/kua-calculator-download/?report=create';
        $this->report_url = $this->current_settings['rll_kua_calculator_general_settings_options']['report_download_url'];

        $this->wl( $this->report_url);

    } // end function rll_kua_calc_create_settings
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
        $custom_attributes = shortcode_atts(['post_id' => get_the_ID() , ], $attributes);

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
        $url = $_SERVER['REQUEST_URI'];
        $base_url = site_url() . strtok($url, '?');
        ob_start();
        ?>
	<style type="text/css">
	
    .kua-calc-form-header p {
        text-align: center;
        font-size: 20px;
        font-weight: 600;
    }
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
        position: relative;
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
        line-height: 42px;
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
	</style>
        <div class="kua-calc-form-header"><p>Enter your birthdate & gender below and press Calculate My Personal Kua Number.</p></div>

			<div id="rl-kua-calc-form">
        	<form name="input" action="<?php echo $base_url; ?>#rlkc-result" method="post">
			<div id="rl-kua-calc-bday" style="text-align: left;">
			<span>Birthday: &nbsp;</span>

			<select name="rlkc_month" style="width: 9em;">
                <?php
                for ($i = 1;$i < 13;$i++) {
                    echo '<option value="' . sprintf("%02d", $i) . '"' . (($i == $default_month) ? ' selected="1">' : '>') . $monthstring[$i] . '</option>';
                }
                ?>
			</select>

			<select name="rlkc_day" style="width: 5em;">
            <?php
                for ($i = 1;$i < 32;$i++) {
                    echo '<option value="' . sprintf("%02d", $i) . '"' . (($i == $default_day) ? ' selected="1">' : '>') . $i . '</option>';
                }
            ?>
			</select>
			
			<select name="rlkc_year" style="width: 6em;">
                <?php  
                for ($i = $this->rlkc_start_year;$i < $this->rlkc_end_year + 1;$i++) {
                    echo '<option value="' . $i . '"' . (($i == $default_year) ? ' selected="1">' : '>') . $i . '</option>';
                }
                ?>
			</select>
		</div>
        <div id="rl-kua-calc-gender">

	<div>	
		<p style="font-weight: 600;">Gender:</p>
	</div>	
		
	<div>
	<p>
        <?php 
        echo '<input type="radio" name="rlkc_gender" value="1" id="rlkc_gender_male" style="width: auto;" ' . ($default_gender ? 'checked="checked" />' : '/>') . '<label for="rlkc_gender_male">Male</label>'; 
        ?>
	</p>
	</div>
	<div>
	<p>
    <?php 
        echo '<input type="radio" name="rlkc_gender" value="0" id="rlkc_gender_female" style="width: auto;" ' . ($default_gender ? '/>' : 'checked="checked" />') . '<label for="rlkc_gender_female">Female</label>';
        ?>
	</p>	
	</div>

    </div>
		
    <div id="kua-calc-submit" style="text-align: center;">
    <input type="submit" value="Calculate My Personal Kua Number" />
    </div>

    </form>

    </div>
    <?php 
	$rlkc_form = ob_get_contents(); 
    ob_end_clean();

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

    public function rlkc_get_cookie_params() {
        if(isset($_COOKIE['kua_calc_info'])) {
            $str = preg_replace('/\\\"/',"\"", $_COOKIE['kua_calc_info']);
            $kua_calc_info = json_decode($str, ARRAY_A);
            return array(
                'birthdate' => $kua_calc_info['birthdate'],
                'gender' => $kua_calc_info['gender'],
                'gender_text' => $kua_calc_info['gender_text'],
                'rlkc_year' => $kua_calc_info['rlkc_year'],
                'rlkc_month' => $kua_calc_info['rlkc_month'],
                'rlkc_day' => $kua_calc_info['rlkc_day'],
                'show_download_links' => true
            );
        } else {
            // PUT SOMETHING HERE
            return false;
        }
    }
    public function rlkc_get_post_params()
    {
        $rlkc_year = sanitize_text_field($_POST["rlkc_year"]);
        $rlkc_month = sanitize_text_field($_POST["rlkc_month"]);
        $rlkc_day = sanitize_text_field($_POST["rlkc_day"]);
        $birthdate = $rlkc_year . "-" . $rlkc_month . "-" . $rlkc_day;
        $gender = sanitize_text_field($_POST["rlkc_gender"]);
        $gender_text = $gender ? "Male" : "Female";
        return array(
            'birthdate' => $birthdate,
            'gender' => $gender,
            'gender_text' => $gender_text,
            'rlkc_year' => $rlkc_year,
            'rlkc_month' => $rlkc_month,
            'rlkc_day' => $rlkc_day,
            'show_download_links' => false
        );
    }

    public function rlkc_calculate_kua($dt_birthdate, $gender)
    {

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

    public function rlkc_get_kua_content($kua_number, $gender, $birthdate_text)
    {

        $kua_content = "";

        $kua_file = file_get_contents($this->rlkc_basepath . "kua" . $kua_number . (($kua_number == 5) ? ($gender ? "male" : "female") : "") . ".php");

        $kua_footer = file_get_contents($this->rlkc_basepath . "kua-content-footer.php");

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

        $kc_content = "";
        $this->rlkc_params = '';
        // see if there's a URL parameter to create the report download
        $url = $_SERVER['REQUEST_URI'];
        $components = parse_url($url, PHP_URL_QUERY);
        parse_str($components, $results);
        if(isset($results['report']) && $results['report'] == 'create') {

            if(isset($_COOKIE['kua_calc_info'])) {
                $this->rlkc_params = $this->rlkc_get_cookie_params();
            } else {
                // remove the report parameter because no cookie is
                // set and redirect to the kua calc page
                $base_url = strtok($url, '?');
                wp_redirect(site_url() . $base_url); 
            }

        } else if( (isset($_POST["rlkc_year"]) && isset($_POST["rlkc_month"]) && isset($_POST["rlkc_day"]) && isset($_POST["rlkc_gender"])) ) {
            $this->rlkc_params = $this->rlkc_get_post_params();
        }
        
        if( is_array($this->rlkc_params) ) {

            if (!$this->rlkc_date_valid($this->rlkc_params['birthdate'])) {

                // date is not valid
                $kc_content .= "<h3>Sorry, the date you have chosen is invalid.</h3>";

                $kc_content .= $this->rlkc_generate_form(date("Y-m-d"));
            } else {
                // we passed, have a valid birthdate, and should process. 

                $dt_birthdate = new \DateTime($this->rlkc_params['birthdate']);

                $kua_number = $this->rlkc_calculate_kua($dt_birthdate, $this->rlkc_params['gender']);
                $kc_content .= $this->rlkc_generate_form($dt_birthdate->format("Y-m-d") , $this->rlkc_params['gender']);

                // set a cookie with the type
                $gender_text = $this->rlkc_params['gender'] ? "Male" : "Female";
                $result_type = array( 
                    // 'kua_number' => $kua_number,
                    'birthdate' => $this->rlkc_params['birthdate'],
                    'gender' => $this->rlkc_params['gender'],
                    'gender_text' => $gender_text,
                    'rlkc_year' => $this->rlkc_params['rlkc_year'],
                    'rlkc_month' => $this->rlkc_params['rlkc_month'],
                    'rlkc_day' => $this->rlkc_params['rlkc_day']
                );
                setcookie( 'kua_calc_info', json_encode($result_type, JSON_UNESCAPED_SLASHES), time() + 3600, '/', $_SERVER['HTTP_HOST'], true, true);

                // add anchor for result
                $kc_content .= "<div id=\"rlkc-result\"></div>";

                $kc_content .= $this->rlkc_get_kua_content($kua_number, $this->rlkc_params['gender'], $dt_birthdate->format("F j, Y"));
            }
        } else {
            // generate the form
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



			<div id="rlkc-report-header">

			<div class="rlkc-left-col">
			    <h1 class="rlkc-h1">Your KUA number is: <?php echo $this->rlkc_user_kua;?></h1>

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

        if($this->rlkc_params['show_download_links']) {
            if($this->rlkc_user_kua == 5) {
                $file_name = 'Kua-Number-Report--Kua-' . $this->rlkc_user_kua . '-' . $this->rlkc_user_gender . '.pdf';
    
            } else {
                $file_name = 'Kua-Number-Report--Kua-' . $this->rlkc_user_kua . '.pdf';
            }
            ob_start(); ?>
            <style>
                .loader-clearfix::after, .loader-clearfix::before {
                    display: table;
                    content: '';
                }
                .loader-clearfix:after {
                    clear: both;
                }
                #inner-report-download {
                    opacity: 0;
                    transition: all 0.4s ease-in-out;
                    width: 100%;
                }
                #inner-report-download.show {
                    opacity: 1;
                }
                .report-download-header a {
                    font-size: 36px;
                    display: block;
                    text-align: center;
                    line-height: 42px;
                }
                #rlkc-report-loader{
                    width: 200px;
                    height: 200px;
                    display: block;
                    position: absolute;
                    margin:auto;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                }
                #rlkc-info-report {
                    padding-left: 0;
                    max-width: 300px;
                    margin: 20px auto 15px;
                }
                #rlkc-info-report img {
                   box-shadow: -2px 1px 14px 2px rgb(230 86 140 / 50%);
                }
                #rlkc-report-download-button {
                    text-align: center;
                }
                #rlkc-report-download-button .report-download-button a {
                    display: inline-block;
                    font-family: "Poppins", sans-serif;
                    font-weight: 600;
                    font-size: 18px;
                    letter-spacing: 1px;
                    border: none;
                    background: #e6568c;
                    color: #fff;
                    padding: 10px 25px;
                }
                #rlkc-report-download-button .report-download-button a:hover {
                    background-color: #e6cc67;
                }
                .patelise_blockG{
                    background-color:rgb(255,255,255);
                    border:2px solid rgb(230 86 140);
                    float:left;
                    height: 150px;
                    margin-left:20px;
                    width:40px;
                    opacity:0.1;
                    animation-name:bounceG;
                        -o-animation-name:bounceG;
                        -ms-animation-name:bounceG;
                        -webkit-animation-name:bounceG;
                        -moz-animation-name:bounceG;
                    animation-duration:1.5s;
                        -o-animation-duration:1.5s;
                        -ms-animation-duration:1.5s;
                        -webkit-animation-duration:1.5s;
                        -moz-animation-duration:1.5s;
                    animation-iteration-count:infinite;
                        -o-animation-iteration-count:infinite;
                        -ms-animation-iteration-count:infinite;
                        -webkit-animation-iteration-count:infinite;
                        -moz-animation-iteration-count:infinite;
                    animation-direction:normal;
                        -o-animation-direction:normal;
                        -ms-animation-direction:normal;
                        -webkit-animation-direction:normal;
                        -moz-animation-direction:normal;
                    transform:scale(0.7);
                        -o-transform:scale(0.7);
                        -ms-transform:scale(0.7);
                        -webkit-transform:scale(0.7);
                        -moz-transform:scale(0.7);
                }

                #blockG_1{
                    animation-delay:0.45s;
                        -o-animation-delay:0.45s;
                        -ms-animation-delay:0.45s;
                        -webkit-animation-delay:0.45s;
                        -moz-animation-delay:0.45s;
                }

                #blockG_2{
                    animation-delay:0.6s;
                        -o-animation-delay:0.6s;
                        -ms-animation-delay:0.6s;
                        -webkit-animation-delay:0.6s;
                        -moz-animation-delay:0.6s;
                }

                #blockG_3{
                    animation-delay:0.75s;
                        -o-animation-delay:0.75s;
                        -ms-animation-delay:0.75s;
                        -webkit-animation-delay:0.75s;
                        -moz-animation-delay:0.75s;
                }
                div#blockText {text-align: center;margin-top: 40px;margin-left:  -30px;margin-right: -30px;}

                #blockText h3 {color: #e6568c;}


                @keyframes bounceG{
                    0%{
                        transform:scale(1.2);
                        opacity:1;
                    }

                    100%{
                        transform:scale(0.7);
                        opacity:0.1;
                    }
                }

                @-o-keyframes bounceG{
                    0%{
                        -o-transform:scale(1.2);
                        opacity:1;
                    }

                    100%{
                        -o-transform:scale(0.7);
                        opacity:0.1;
                    }
                }

                @-ms-keyframes bounceG{
                    0%{
                        -ms-transform:scale(1.2);
                        opacity:1;
                    }

                    100%{
                        -ms-transform:scale(0.7);
                        opacity:0.1;
                    }
                }

                @-webkit-keyframes bounceG{
                    0%{
                        -webkit-transform:scale(1.2);
                        opacity:1;
                    }

                    100%{
                        -webkit-transform:scale(0.7);
                        opacity:0.1;
                    }
                }

                @-moz-keyframes bounceG{
                    0%{
                        -moz-transform:scale(1.2);
                        opacity:1;
                    }

                    100%{
                        -moz-transform:scale(0.7);
                        opacity:0.1;
                    }
                }
            </style>
            <script type="text/javascript">
            jQuery(function($) {
                $('document').ready(function() {
                    setTimeout(function() {
                        $('#rlkc-report-loader').fadeOut(function() {
                            $('#inner-report-download').addClass('show');
                        });
                    }, 3000);
                });
            });
            </script>
            </div> <!-- .rlkc-left-col -->
            <div class="rlkc-right-col">

                <div id="rlkc-report-loader">
                    <div id="blockG_1" class="patelise_blockG"></div>
                    <div id="blockG_2" class="patelise_blockG"></div>
                    <div id="blockG_3" class="patelise_blockG"></div>
                    <div class="loader-clearfix"></div>
                    <div id="blockText"><h3>LOADING REPORT</h3></div>
                </div>
                <div id="inner-report-download">
                    <div id="rlkc-info-report-text">
                        <p class="report-download-header"><a href="<?php echo plugins_url('media/' . $file_name, RLL_KUA_CALCULATOR__FILE__);?>" target="_blank">YOUR REPORT IS READY</a></p>
                    </div>
                    <div id="rlkc-info-report">
                        <a href="<?php echo plugins_url('media/' . $file_name, RLL_KUA_CALCULATOR__FILE__);?>" target="_blank"><img src="<? echo plugins_url('media/Kua-number-report-cover.png',RLL_KUA_CALCULATOR__FILE__);?>" name="Kua Number Report Cover" /></a>
                    </div>
                    <div id="rlkc-report-download-button">
                        <p class="report-download-button"><a href="<?php echo plugins_url('media/' . $file_name, RLL_KUA_CALCULATOR__FILE__);?>" target="_blank">DOWNLOAD MY REPORT</a></p></p>
                    </div>
                </div>
            </div><!-- .rlkc-right-col -->
                
            </div> <!-- #rlkc-report-header -->
            <?php $contents = ob_get_contents(); 
            ob_end_clean();
            return $contents;
        } else {
            ob_start(); ?>

            </div> <!-- .rlkc-left-col -->
            <div class="rlkc-right-col">
                <div id="rlkc-info-report-text">
                    <p class="report-download-header"><a href="<?php echo $this->report_url; ?>" target="_blank">Free Download</a></p>
                    <p class="report-download-text">Get This FREE 6 Page Report with Details about Your Unique Kua Number.</p>
                </div>
                <div id="rlkc-info-report">
                <a href="<?php echo $this->report_url; ?>" target="_blank"><img src="<? echo plugins_url('media/Kua-number-report-cover.png',RLL_KUA_CALCULATOR__FILE__);?>" name="Kua Number Report Cover" /></a>
                </div>
            </div><!-- .rlkc-right-col -->
                
            </div> <!-- #rlkc-report-header -->
            <?php $contents = ob_get_contents(); 
            ob_end_clean();
            return $contents;
        }
		
		
		
	}

    public function show_report_download() {
        // SHOW PREV BIRTHDATE
        if(isset($_COOKIE['kua_calc_info'])) {
            $str = preg_replace('/\\\"/',"\"", $_COOKIE['kua_calc_info']);
            $kua_calc_info = json_decode($str, ARRAY_A);

            $kua_number = $kua_calc_info['kua_number'];
            $gender = $kua_calc_info['gender'];
            $rlkc_year = $kua_calc_info['rlkc_year'];
            $rlkc_month = $kua_calc_info['rlkc_month'];
            $dateObj   = \DateTime::createFromFormat('!m', $rlkc_month);
            $rlkc_month = $dateObj->format('F'); // March
            $rlkc_day = $kua_calc_info['rlkc_day'];
        } else {
            // PUT SOMETHING HERE
            return false;
        }
		if($kua_number == 5) {
			$file_name = 'Kua-Number-Report--Kua-' . $kua_number . '-' . $gender . '.pdf';

		} else {
			$file_name = 'Kua-Number-Report--Kua-' . $kua_number . '.pdf';
		}
		
		ob_start(); ?>
        <style type="text/css">
            #rlkc-report-header {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                margin-bottom: 10px;
            }
            .rlkc-left-col {
                flex: 1 1 50%;
            }
            .rlkc-right-col {
                flex: 1 1 50%;
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                position: relative;
            }
        </style>
        <div id="rlkc-report-header">
            <div class="rlkc-left-col">
                <div class="rlkc-info">
                    <p><strong>Birthdate: </strong> <?php echo $rlkc_month . ' ' . $rlkc_day . ', ' . $rlkc_year; ?></p>
                    <p><strong>Gender: </strong> <?php echo $gender; ?>
                </div>
                <div id="rlkc-info-report-text">
                    <p class="report-download-header"><a href="<?php echo plugins_url('media/' . $file_name, RLL_KUA_CALCULATOR__FILE__);?>" target="_blank">Free Download</a></p>
                    <p class="report-download-text">Get This FREE 6 Page Report with Details about Your Unique Kua Number.</p>
                </div>
            </div>
            <div class="rlkc-right-col">
                
                <div id="rlkc-info-report">
                    <a href="<?php echo plugins_url('media/' . $file_name, RLL_KUA_CALCULATOR__FILE__);?>" target="_blank"><img src="<? echo plugins_url('media/Kua-number-report-cover.png',RLL_KUA_CALCULATOR__FILE__);?>" name="Kua Number Report Cover" /></a>
                </div>
            </div><!-- .rlkc-right-col -->
        </div>
		<?php $contents = ob_get_contents(); 
		ob_end_clean();
		return $contents;
     
    }
} // end class RLL_Kua_Calculator
$rll_kua_calculator = new RLL_Kua_Calculator();

