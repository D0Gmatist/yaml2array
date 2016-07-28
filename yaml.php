<?php
class yaml {
	function parse($filename){
		$levels = array();
		$data = array();
		
		$handle = @fopen($filename, "r");
		if ($handle) {
			while (($line = fgets($handle, 4096)) !== false) {
				$tline = ltrim($line);
				$offset = (strlen($line) - strlen($tline));
				
				# It is a list item
				if(substr($tline, 0, 1)=='-'){
					$value = rtrim(substr($tline, 2));
					$data = $this->populate_level($levels, $value, $data, 'list');
				}
				# It is an Array/Object
				else {
					# Label and Value
					list($label, $value) = explode(':', $tline, 2);
					if(substr($value, 0, 1)==' '){
						$value = rtrim(substr($value, 1));
					}

					# Store that data
					if($offset>0){
						$level	= ($offset/2);
						$levels	= array_slice($levels, 0, $level);

						# It's going to be an array
						if(empty(substr($value, 1))){
							$value = array();
						}
						$levels[] = $label;
						$data = $this->populate_level($levels, $value, $data);
					} else {
						# It's going to be an array
						if(empty(substr($value, 1))){
							$data[$label] = array();
							$levels = array($label);
						}
						# It's going to be a value
						else {
							$data[$label] = rtrim($value);
						}
					}

				}
				
			}
			if (!feof($handle)) {
				echo "Error: unexpected fgets() fail\n";
			}
			fclose($handle);
		}
		return $data;
	}

	private function populate_level($levels, $value, $data, $option=null){
		$level = $levels[0];

		foreach($data as $key=>$row){
			if($level==$key){
				$next_levels = array_slice($levels, 1);
				if(count($next_levels)>1){
					$data[$level] = $this->populate_level($next_levels, $value, $row, $option);
				} else if($option=='list'){
					$data[$level][$levels[1]][] = $value;
				} else {
					$data[$level][$levels[1]] = $value;
				}
			}
		}

		return $data;
	}
}
