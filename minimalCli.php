<?php

namespace diversen;

use diversen\parseArgv;

class minimalCli {

	public $commands = [];
	public $values = [];
	public $flags = [];

	public function run () {
		$parse = new parseArgv();
		$this->values = $parse->values;
		$this->flags = $parse->flags;

		// Look for first sub command
		foreach($this->commands as $key => $command) {
			if (isset($parse->values[$key])) {
				$this->execute($key);		
			}
		
		}
	}

	public function execute($command) {
		$obj = $this->commands[$command];
		if (isset($this->flags['help'])) {
			if (method_exists($obj, 'help')) {
				echo $obj->help();
			}
		}

		$obj->run($this);
	}
	
	/**
	 * Get terminal width
	 */
	public function getTerminalWidth () {
		return 80;	
	}

	public function outputMessage () {

	}


}

