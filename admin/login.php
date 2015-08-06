<?php
/*
	blogless - a blogless writing system
	Author:  Martin Doering <martin@datenbrei.de>
	Project: http://blogless.datenbrei.de
	License: http://blogless.datenbrei.de/license.html
*/

	// Check Login like this:
	// session_start();
	// if (empty($_COOKIE['blogless']) or empty($_SESSION['login']) or $_COOKIE['blogless'] != $SESSION['login']) {
	// 	header('Location: login.php');
	// 	die("Access denied");
	//}

	// Is there yet a password set? If yes, get it.
	session_start();
	if (is_readable('password.php'))
		@include 'password.php';
	else
		$password = false;

	if ($_SERVER["REQUEST_METHOD"] == "GET") {
		$html = "<html> \n";
		$html .= "<head> \n";
		$html .= "<title>Login</title> \n";
		$html .= "<meta charset=UTF-8> \n";
		$html .= '<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">' . "\n";
		$html .= '<link rel="stylesheet" href="admin.css" type="text/css" media="all">' . "\n";
		$html .= "</head> \n";
		$html .= "<body> \n";
		$html .= '<header>' . "\n";
		$html .= '<h1>Security</h1>' . "\n";
		$html .= '</header>' . "\n";

		if ($password) {
			$html .= "<h2>Login</h2> \n";
			$html .= '<form method="post" action="login.php" autocomplete="off">';
			$html .= '<p><input type="text" name="username" autofocus placeholder="Your Username"></p>' . "\n";
			$html .= '<p><input type="password" name="password" placeholder="Type your Password"></p>';
			$html .= '<p><input id="save" type="submit" value="Login">';
			$html .= '</form>';
		}
		else {
			$html .= "<h2>Set initial Password</h2> \n";
			$html .= '<form method="post" action="login.php" autocomplete="off">' . "\n";
			$html .= '<p><input type="text" name="username" autofocus placeholder="Choose a Username"></p>' . "\n";
			$html .= '<p><input type="password" name="password" placeholder="Set initial Password - Minimum 8 Characters"></p>' . "\n";
			$html .= '<p><input id="login" type="submit" value="Initial Login">';
			$html .= '</form>' . "\n";
		}

		$html .= "<body>";
		$html .= "</html>";
		header('Content-type: text/html; charset=utf-8');
		header('Cache-Control: no-cache, must-revalidate');
		die($html);
	}
	elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
		$pw = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW);
		$user = filter_input(INPUT_POST, "username", FILTER_SANITIZE_STRING);
		// initial login, set username and password
		$crypted = password_hash($pw, PASSWORD_BCRYPT);
		if (!$password) {
			$file = '<?php' . "\n";
			$file .= '$username = ' . "'" . $user . "';\n";
			$file .= '$password = ' . "'" . $crypted . "';\n";
			$file .= '?>' . "\n";
			file_put_contents('password.php', $file);

			// Login
			setcookie('blogless', $crypted, time() + 2*24*3600); // two days
			$_SESSION['login'] = $crypted;
			header('Location: index.php');
		}			
		else {
			// Login successful
			if ($user == $username && password_verify($pw, $password)) {
				setcookie('blogless', $crypted, time() + 2*24*3600); // two days
				$_SESSION['login'] = $crypted;
				header('Location: index.php');
			}
			else {
				setcookie('blogless', '', time() -3600); 
				session_destroy();
				header('Location: login.php');
			}
		}
	}
?>