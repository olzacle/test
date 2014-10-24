<?php 
class General{

	public function logged_in () {
		return(isset($_SESSION['id'])) ? true : false;
	}

	public function logged_in_protect() {
		if ($this->logged_in() === true) {
			header('Location: perfil.php');
			exit();		
		}
	}

	 
	public function logged_out_protect() {
		if ($this->logged_in() === false) {
			header('Location: index.php');
			exit();
		}	
	}
	
	public function logged_out_protect_admin() {
		if ($_SESSION['id'] != 1) {
			header('Location: index.php');
			exit();
		}	
	}
	
	public function logged_out_protect_panel() {
		if ($this->logged_in() === false) {
			header('Location: ../index.php');
			exit();
		}	
	}
	
	
	public function file_newpath($path, $filename){
		if ($pos = strrpos($filename, '.')) {
		   $name = substr($filename, 0, $pos);
		   $ext = substr($filename, $pos);
		} else {
		   $name = $filename;
		}
		
		$newpath = $path.'/'.$filename;
		$newname = $filename;
		$counter = 0;
		
		while (file_exists($newpath)) {
		   $newname = $name .'_'. $counter . $ext;
		   $newpath = $path.'/'.$newname;
		   $counter++;
		}
		
		return $newpath;
	}
}