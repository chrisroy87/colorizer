<?php
namespace Colorizer;

/**
 * The Colorizer class for Command Line utility
 * 
 * Currently the colorizer works on 16 color matrix. This ensures that
 * the colorizer is compatible with almost all possible terminals.
 * 
 * Allowed modifiers are [bold][\bold] or [b][\b], [underline]
 * [/underline] or [u][/u] and [dim][/dim] or [d][/d] or any color
 */
class Colorizer {
	/**
	 * @var bool	A constant that can be used as a second parameter to
	 * self::end()
	 */ 
	const CLEAR_FALSE = false;
	
	/**
	 * @var array	Associative array of foreground color names as keys and 
	 * codes as a values
	 */ 
	protected $foreground = [
		"Black"=>30,
		"Red"=>31, 
		"Green"=>32, 
		"Yellow"=>33, 
		"Blue"=>34, 
		"Magenta"=>35, 
		"Cyan"=>36, 
		"LightGrey"=>37, 
		"DarkGrey"=>90, 
		"LightRed"=>91, 
		"LightGreen"=>92, 
		"LightYellow"=>93,
		"LightBlue"=>94,
		"LightMagenta"=>95, 
		"LightCyan"=>96, 
		"White"=>97 
	];
	
	/**
	 * @var array	Associative array of background color names as keys and 
	 * codes as a values
	 */
	protected $background = [
		"Black"=>40,
		"Red"=>41,
		"Green"=>42,
		"Yellow"=>43,
		"Blue"=>44,
		"Magenta"=>45,
		"Cyan"=>46,
		"LightGrey"=>47,
		"DarkGrey"=>100,
		"LightRed"=>101,
		"LightGreen"=>102,
		"LightYellow"=>103,
		"LightBlue"=>104,
		"LightMagenta"=>105,
		"LightCyan"=>106,
		"White"=>107
	];
	
	/**
	 * @var string	A line of text that you want to output
	 */ 
	protected $message;
	
	/**
	 * @var int Used internally to keep track of inline tags
	 */ 
	protected $adjustmentCount;
	
	/**
	 * @var int Screen measurements
	 */ 
	protected $width, $wlimit;
	
	/**
	 * @var string	Colors names that you have chosen
	 */ 
	protected $background_color, $foreground_color;
	
	/**
	 * @var string	The final output container variable
	 */ 
	protected $spit = "";
	
	/**
	 * @var string	We use padding to maintain line integrity, this is the 
	 * character used for the padding. If there is a problem with line 
	 * integrity, you might change to something other than space, to help
	 * debug
	 */ 
	protected $padding_char = " ";
	
	/**
	 * @var string
	 */ 
	protected $margin;  
	
	/**
	 * Constructor
	 * 
	 * Sets dimensions and base colors
	 * 
	 * @param int $width	Width of the screen, or frame
	 * 
	 * @param string $background Should be a key from $this->background
	 * 
	 * @param string $foreground Should be a key from $this->foreground
	 * 
	 * @return self
	 */ 
	public function __construct($width, $background, $foreground){
		$this->background_color = $this->background[ucfirst($background)];
		$this->foreground_color = $this->foreground[ucfirst($foreground)];
		$this->width = $width;
		$this->wlimit = $width - 4;
				
		//create color
		$this->spit .= "\e[".$this->background_color."m"."\e[".$this->foreground_color."m";
		
		//create margin
		$this->margin = str_repeat($this->padding_char, 2);
		
		//add initial new line
		$new_line = str_repeat($this->padding_char, (integer) $this->width);
		$this->spit .= $new_line; 
		return $this;
	}
	
	/**
	 * Add message
	 * 
	 * You can add a single line of message or multiple lines with \n
	 * 
	 * @param string $message
	 * 
	 * @return self
	 */ 
	public function addMessage($message){
		$this->message = $message;
		$this->modifier();
		$this->parse();
		return $this;
	}
	
	/**
	 * Parse the last added message
	 * 
	 * @internal
	 */ 
	protected function parse(){
		$lines = explode("\n", $this->message);
		foreach($lines as $line){
			$this->lineParse($line);
		}
		return $this->spit;
	}
	
	/**
	 * Replace tags with color or string formatting codes
	 * 
	 * @internal
	 */ 
	protected function modifier(){
		$replaced = 0;
		$this->adjustmentCount =  0;
		$this->message = str_replace("[bold]", "\e[1m", $this->message, $replaced); //bold
		$this->message = str_replace("[/bold]", "\e[21m", $this->message, $replaced); //remove bold
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[1m") + strlen("e[21m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[b]", "\e[1m", $this->message, $replaced); //bold
		$this->message = str_replace("[/b]", "\e[21m", $this->message, $replaced); //remove bold
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[1m") + strlen("e[21m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[dim]", "\e[2m", $this->message, $replaced); //dim
		$this->message = str_replace("[/dim]", "\e[22m", $this->message, $replaced); //remove dim
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[2m") + strlen("e[22m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[d]", "\e[2m", $this->message, $replaced); //dim
		$this->message = str_replace("[/d]", "\e[22m", $this->message, $replaced); //remove dim
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[2m") + strlen("e[22m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[underline]", "\e[4m", $this->message, $replaced); //underline
		$this->message = str_replace("[/underline]", "\e[24m", $this->message, $replaced); //remove underline
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[4m") + strlen("e[24m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[u]", "\e[4m", $this->message, $replaced); //underline
		$this->message = str_replace("[/u]", "\e[24m", $this->message, $replaced); //remove underline
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[4m") + strlen("e[24m")) * $replaced : 0;
		$replaced = 0;
		// ------ colors ---------
		$this->message = str_replace("[black]", "\e[30m", $this->message, $replaced); //color
		$this->message = str_replace("[/black]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[30m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[red]", "\e[31m", $this->message, $replaced); //color
		$this->message = str_replace("[/red]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[31m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[green]", "\e[32m", $this->message, $replaced); //color
		$this->message = str_replace("[/green]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[32m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[yellow]", "\e[33m", $this->message, $replaced); //color
		$this->message = str_replace("[/yellow]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[33m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[blue]", "\e[34m", $this->message, $replaced); //color
		$this->message = str_replace("[/blue]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[34m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[magenta]", "\e[35m", $this->message, $replaced); //color
		$this->message = str_replace("[/magenta]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[35m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[cyan]", "\e[36m", $this->message, $replaced); //color
		$this->message = str_replace("[/cyan]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[36m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[lightGrey]", "\e[37m", $this->message, $replaced); //color
		$this->message = str_replace("[/lightGrey]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[37m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[darkGrey]", "\e[90m", $this->message, $replaced); //color
		$this->message = str_replace("[/darkGrey]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[90m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[lightRed]", "\e[91m", $this->message, $replaced); //color
		$this->message = str_replace("[/lightRed]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[91m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[lightGreen]", "\e[92m", $this->message, $replaced); //color
		$this->message = str_replace("[/lightGreen]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[92m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[lightYellow]", "\e[93m", $this->message, $replaced); //color
		$this->message = str_replace("[/lightYellow]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[93m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[lightBlue]", "\e[94m", $this->message, $replaced); //color
		$this->message = str_replace("[/lightBlue]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[94m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[lightMagenta]", "\e[95m", $this->message, $replaced); //color
		$this->message = str_replace("[/lightMagenta]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[95m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[lightCyan]", "\e[96m", $this->message, $replaced); //color
		$this->message = str_replace("[/lightCyan]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[96m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("[white]", "\e[97m", $this->message, $replaced); //color
		$this->message = str_replace("[/white]", "\e[".$this->background_color."m"."\e[".$this->foreground_color."m", $this->message, $replaced); //color reset
		$this->adjustmentCount +=  ($replaced > 0 ) ? (strlen("e[97m") + strlen("\e[".$this->background_color."m"."\e[".$this->foreground_color."m")) * $replaced : 0;
		$replaced = 0;
		
		$this->message = str_replace("\t", "     ", $this->message, $replaced); //tab with 5 spaces
	}
	
	/**
	 * Parse each line separated by \n
	 * 
	 * @internal
	 */ 
	protected function lineParse($line){
		//This line has an implicit \n at the end otherwise, I am free to add it
		$count = ((strlen($line) - $this->adjustmentCount) < 1) ? 0 : (strlen($line) - $this->adjustmentCount);
		if($count == 0){
			return false;
		}
		else if($count < $this->wlimit){
			$diff = $this->wlimit - $count;
			$padding = "";
			$i=0;
			while($i<$diff){
				$padding .= $this->padding_char;
				$i++;
			}
			$line = $this->margin.$line.$padding.$this->margin;
			$this->spit .= $line;
		}
		else { //$count > $this->wlimit
			$diff = $count - $this->wlimit;
			
			$_line = $this->margin;
			$_line_end = $this->margin;
			
			//create a virtual line with whole words
			$v_line = "";
			$v_count = 0;
			$v_padding = ""; //to complete the virtual line
			$words = explode(" ", $line);
			foreach($words as $word){
				$_count = strlen($word);
				if(($v_count + $_count) < $this->wlimit){
					$v_line .= $word. " ";
					$v_count += ($_count + 1);
				}
				else {
					$finishing_count = strlen($v_line);
					$finishing_diff = $this->wlimit - $finishing_count;
					$i = 0;
					while($i<$finishing_diff){
						$v_padding .= $this->padding_char;
						$i++;
					}
					$finishing_line = $_line.$v_line.$v_padding.$_line_end;
					$this->spit .=  $finishing_line;
					//reset all v_*
					$v_line = "";
					$v_count = 0;
					$v_padding = "";
				}
			}
		}
	}
	
	/**
	 * Clears the terminal screen
	 *
	 * @return self
	 */  
	public function clear(){
		echo `clear`;
		return $this;
	}
	
	/**
	 * Adds a new line
	 * 
	 * @return self
	 */ 
	public function newLn(){
		//add new line
		$new_line = str_repeat($this->padding_char, (integer) $this->width);
		$this->spit .= $new_line;
		return $this; 
	}
	
	/**
	 * Echos the final output
	 * 
	 * @param bool $newLine	If set to true, which is default, after the 
	 * final output, an empty new line would be added
	 * 
	 * @return void
	 */ 
	public function spit($newLine=true){
		//add no color at the end
		$this->newLn();
		$this->spit .= "\e[0m";
		echo $this->spit;
		if($newLine){
			echo "\n\n";
		}
	}
	
	/**
	 * Alert message
	 * 
	 * This works much like a pop-up. The message displays for 2 seconds
	 * and then disappears.
	 * 
	 * @param string $message
	 *
	 * @return void
	 */  
	public static function alert($message){
		$instance = new static(`tput cols`, "red", "white");
		$instance->clear();
		$instance->addMessage($message);
		$instance->spit();
		sleep(2);
		unset($instance);
	}
	
	/**
	 * Ends the session with a message
	 * 
	 * @param string $message
	 * 
	 * @param bool|self::CLEAR_FALSE $clear	If set to false then, it would 
	 * not clear the current screen otherwise, by default, it clears out
	 * the current screen
	 * 
	 * @return void
	 */ 
	public static function end($message, $clear=true){
		$instance = new static(`tput cols`, "cyan", "black");
		if($clear){
			$instance->clear();
		}
		$instance->addMessage($message);
		$instance->spit();
		exit;
	}
} 
