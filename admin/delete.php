<?php
/*
	blogless - a blogless writing system
	Author:  Martin Doering <martin@datenbrei.de>
	Project: http://blogless.datenbrei.de
	License: http://blogless.datenbrei.de/license.html
*/

	require('include.php');
	require('auth.php');
	
	function rrmdir($dir) { 
		foreach(glob($dir . '/*') as $file) { 
			if(is_dir($file)) rrmdir($file); else unlink($file); 
		} 
		rmdir($dir); 
	}

	if ($_SERVER["REQUEST_METHOD"] == "GET") {
		$article = (isset($_GET['article'])) ? $_GET['article'] : NULL; 
		$file = (isset($_GET['file'])) ? $_GET['file'] : NULL; 
		
		if ($article && $file && $file != 'index.html') {
			unlink('../' . $article . '/' . $file);
			header('Location: edit.php?article=' . urlencode($article));
		}
		elseif ($file && $file != 'index.html') {
			unlink('../' . $file);
			header('Location: edit.php');
		}

		elseif ($article) {
			rrmdir('../' . urldecode($article));
			header('Location: index.php');
		}
		else
			header('Location: index.php');
	}
?>
