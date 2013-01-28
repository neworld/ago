<?php
class logeris {
	private $file;

	public function __construct($filename) {
		if (!file_exists($filename)) {
			$this->file=fopen($filename, 'w');
		} else {
			$this->file=fopen($filename, 'a');
		}
	}

	public function write($text) {
		$date=date('Y-m-d H:i:s');

		fwrite($this->file, "$date $text\n");
	}

	public function __destruct() {
		fclose($this->file);
	}
}
?>
