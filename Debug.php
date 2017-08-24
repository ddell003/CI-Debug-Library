<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Debug {
	
	private static $start_time = NULL;
	private static $last_time = NULL;
	private $_allow_debug;

	function __construct()
	{
		$CI =& get_instance();
		$this->_allow_debug = TRUE; //$CI->config->item('allow_debug');	

		$this->_cli_request = php_sapi_name() == 'cli';
	}

	public function trace($exit = TRUE)
	{
		echo '<br><br><pre>';
		debug_print_backtrace();
		echo '</pre><br><br>';

		if($exit)
		{
			exit('end backtrace!');
		}
	}

	public function reflect($source, $exit = TRUE)
	{
		if( ! $this->_allow_debug) {
			return;
		}
		 $reflector = new ReflectionClass($source);
		 $vars = get_class_vars($source);
		 //$methods = get_class_vars($source);

		 foreach($vars as $var)
		 {
		 	echo 'var: '.$var.' prop: '.$reflector->getProperty($var).'<br>';
		 }

		 if($exit)
		 {
		 	exit();
		 }	
	}

	public function frames($input,$exit = TRUE)
	{
		if( ! $this->_allow_debug) {
			return;
		}
		echo '<div style="height:800px;overflow-y:auto;text-align:left;margin-left: 200px;">=======================================================<pre>';
		if(is_array($input))
		{
			print_r($input);
		}
		else
		{
			echo($input);
		}
		echo '<br />=======================================================</pre><br />Debug Exit!</div>';
		if($exit)
		{
			exit();
		}
	}

	public function no_frames($input,$exit = TRUE)
	{
		if( ! $this->_allow_debug) {
			return;
		}
		echo (php_sapi_name() == 'cli') ? "\r\n" : '<div style="background: #fff !important;text-align:left;margin-left: 200px;">=======================================================<pre>';
		if(is_array($input))
		{
			print_r($input);
		}
		else
		{
			echo($input);
		}
		echo (php_sapi_name() == 'cli') ? "\r\n" : '<br />=======================================================</pre><br />Debug Exit!</div>';
		if($exit)
		{
			exit();
		}
	}	

	public function dump($input,$exit = TRUE)
	{
		if( ! $this->_allow_debug) {
			return;
		}
		echo '<div style="background: #fff !important;text-align:left;margin-left: 200px;">=======================================================<pre>';
		if(is_object($input)) {
			print_r(get_object_vars($input));
		} else {
			var_dump($input);
		}
			
		echo '<br />=======================================================</pre><br />Debug Exit!</div>';
		if($exit)
		{
			exit();
		}
	}

	public function dump_email($to, $subject, $message, $headers, $exit = TRUE)
	{
		if( ! $this->_allow_debug) {
			return;
		}

		echo '<div style="background: #fff !important;text-align:left;margin-left: 200px;">=======================================================<pre>';
		echo $to.'<br>';
		echo $subject.'<br>';
		echo $message.'<br>';
		echo $headers.'<br>';
		echo '<br />=======================================================</pre><br />Dump Email Exit!</div>';

		if($exit)
		{
			exit();
		}
	}

	public function sql_dump($arr, $exit = TRUE)
	{
		$CI =& get_instance();

		$this->no_frames(array('count'=>count($arr), 'sql'=>$CI->db->last_query(), 'result'=>$arr), $exit);
	}

	public function post($exit = TRUE) {
		$CI =& get_instance();
		return $this->dump($CI->input->post(), $exit);
	}

	public function files($exit = TRUE) {
		$CI =& get_instance();
		return $this->dump($_FILES, $exit);
	}

	public function get($exit = TRUE) {
		$CI =& get_instance();
		return $this->dump($CI->input->get(), $exit);
	}

	public function time_page($string = '', $exit = TRUE)
	{
		if( ! $this->_allow_debug) {
			return;
		}
		$endTime = explode(' ', microtime());
		$elapsed = round((($endTime[1] + $endTime[0]) - (Debug::$start_time[1] + Debug::$start_time[0])), 4);
		$break = round((($endTime[1] + $endTime[0]) - (Debug::$last_time[1] + Debug::$last_time[0])), 4);
		$break_color = $this->_find_color($break);

		echo('elapsed <strong style="color: #360;">'.$elapsed.' sec.</strong> /'.
			' break: <strong style="color: '.$break_color.'">'.$break.' sec.</strong> : ');

		echo $string != '' ? $string : '';
		echo '<br>';


		Debug::$last_time = $endTime;

		($exit) ? exit() : NULL;
		
	}

	private function _find_color($timer)
	{
		if($timer < .005) return 'Green';
		if($timer < .01) return 'YellowGreen';
		if($timer < .05) return 'Gold';
		if($timer < .5) return 'GoldenRod';
		if($timer < 1) return 'Orange';
		
		return 'Red';
	}

	public function start_timer()
	{
		if( ! $this->_allow_debug) {
			return;
		}
		if(Debug::$start_time === NULL)
		{
			Debug::$start_time =  explode(' ', microtime());
			Debug::$last_time = Debug::$start_time;
		}
	}

	/**
	 * View any string as a hexdump.
	 *
	 * This is most commonly used to view binary data from streams
	 * or sockets while debugging, but can be used to view any string
	 * with non-viewable characters.
	 *
	 * @version     1.3.2
	 * @author      Aidan Lister <aidan@php.net>
	 * @author      Peter Waller <iridum@php.net>
	 * @link        http://aidanlister.com/2004/04/viewing-binary-data-as-a-hexdump-in-php/
	 * @param       string  $data        The string to be dumped
	 * @param       bool    $htmloutput  Set to false for non-HTML output
	 * @param       bool    $uppercase   Set to true for uppercase hex
	 * @param       bool    $return      Set to true to return the dump
	 */
	public function hexdump($data, $htmloutput = true, $uppercase = false, $return = false)
	{
		if( ! $this->_allow_debug) {
			return;
		}
	    // Init
	    $hexi   = '';
	    $ascii  = '';
	    $dump   = ($htmloutput === true) ? '<pre style="margin-left: 200px;">' : '';
	    $offset = 0;
	    $len    = strlen($data);
	  
	    // Upper or lower case hexadecimal
	    $x = ($uppercase === false) ? 'x' : 'X';
	  
	    // Iterate string
	    for ($i = $j = 0; $i < $len; $i++)
	    {
	        // Convert to hexidecimal
	        $hexi .= sprintf("%02$x ", ord($data[$i]));
	  
	        // Replace non-viewable bytes with '.'
	        if (ord($data[$i]) >= 32) {
	            $ascii .= ($htmloutput === true) ?
	                            htmlentities($data[$i]) :
	                            $data[$i];
	        } else {
	            $ascii .= '.';
	        }
	  
	        // Add extra column spacing
	        if ($j === 7) {
	            $hexi  .= ' ';
	            $ascii .= ' ';
	        }
	  
	        // Add row
	        if (++$j === 16 || $i === $len - 1) {
	            // Join the hexi / ascii output
	            $dump .= sprintf("%04$x  %-49s  %s", $offset, $hexi, $ascii);
	             
	            // Reset vars
	            $hexi   = $ascii = '';
	            $offset += 16;
	            $j      = 0;
	             
	            // Add newline            
	            if ($i !== $len - 1) {
	                $dump .= "\n";
	            }
	        }
	    }
	  
	    // Finish dump
	    $dump .= $htmloutput === true ?
	                '</pre>' :
	                '';
	    $dump .= "\n";
	  
	    // Output method
	    if ($return === false) {
	        echo $dump;
	    } else {
	        return $dump;
	    }
	}
}
/* End of file Debug.php */
