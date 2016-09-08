#!/usr/bin/env php
<?php

/**
 * This is an example implementation of the capabilities of
 * Colorizer
 */

require_once 'vendor/autoload.php';
use Colorizer\Colorizer;
 
function help(){
	$colorizer = new Colorizer(`tput cols`,"green", "white");
	$colorizer->clear();
	$colorizer->addMessage("Welcome to the [u]Colorizer Example[/u]")->newLn();
	$colorizer->addMessage("You can choose from the following options:")->newLn();
	$colorizer->addMessage("\t\t [ Tags ] - Type [u]tags[/u] to select this option. You can see an example of using tags");
	$colorizer->addMessage("\t\t [ Alert ] - Type [u]alert[/u] to select this option. You can see an example of using alerts");
	$colorizer->addMessage("\t\t [ End ] - Type [u]end[/u] to select this option. You can see an example of using the end function");
	$colorizer->spit();
	$response = converse("Please choose an option");
	switch(strtolower(trim($response))){
		case "tags":
			$colorizer = new Colorizer(`tput cols`,"green", "white");
			$colorizer->clear();
			$colorizer->addMessage("[red]This is red[/red]");
			$colorizer->addMessage("Now lets make it [b]bold[/b]");
			$colorizer->addMessage("Or, make it [u]underlined[/u]");
			$colorizer->addMessage("\t - A demonstration of various tags in action");
			$colorizer->spit(false);
			Colorizer::end("Thank for using Colorizer. Have a good day!", Colorizer::CLEAR_FALSE);
		case "alert":
			Colorizer::alert("This is an alert message! It will disappear...");
		case "end":
			Colorizer::end("Thank for using Colorizer. Have a good day!");
	}
}

function converse($msg, $num_lines=20){
	if(!defined("STDIN")){
		define("STDIN", fopen('php://stdin','r'));
	}
	print($msg.": ");
	$response = fread(STDIN, $num_lines);
	echo "\n";
	return $response;
} 

help();
 
?>
