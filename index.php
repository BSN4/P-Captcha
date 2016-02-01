<?php

/**
 * PCaptcha :
 * A simple/lightweight class provides you with the necessary tools to generate a friendly/secure captcha and validate it 
 *
 * @author Bader Almutairi (Phpfalcon)
 * @link https://github.com/phpfalcon/pcaptcha/
 * @license http://opensource.org/licenses/MIT MIT License
 */


//init PCaptcha 
include_once 'PCaptcha.php';
$captcah = new PCaptcha();

//start sessions
session_start();

//captcha image
if(isset($_GET['PC']))
{
	//you can adjust it here 
	//$captcah->width = 200;
	$captcah->get_captcha();

}
else
{
	//form posted
	if(!empty(@$_POST))
	{
		if($captcah->validate_captcha())
		{
			echo 'Correct code';
		}
		else
		{
			echo 'Wrong code';
		}
		
		echo '<br /> you\'ll be directed in a seconds';
		header('refresh:2;url=index.php'); 
	}
	else
	{
		?>
		<form action="index.php" method="post">
		  First name:<br>
		  <input type="text" name="firstname"><br>
		  Last name:<br>
		  <input type="text" name="lastname"><br>
		  <img src="?PC=1" alt="Enter that number">
		  <input type="text" name="panswer">
		  <br>
		  <input type="submit" value="Submit">
		</form>
		<?php
	}
}
