<?php

/**
 * PCaptcha :
 * A simple/lightweight class provides you with the necessary tools to generate a friendly/secure captcha and validate it 
 *
 * @author Bader Almutairi (Phpfalcon)
 * @link https://github.com/phpfalcon/pcaptcha/
 * @license http://opensource.org/licenses/MIT MIT License
 */

class PCaptcha
{
	//options
	var $width = 150;
	var $hight = 45;
	var $size = 0; //this will be generated automaticlly - you can set it manually
	var $scale = 0; //this will be generated automaticlly - you can set it manually
	var $leng  = 5;
	var $noise_level = 0;
	var $pixlized = 2;
	var $angle = 20;
	var $grid = true;
	var $font; 
	var $drawcross = false;
	var $drawcircles = true;
	var $background = array(0,  0, 0); //rand if empty
	var $text = array(255, 255, 255); //white
	var $colortwo = array(204, 204, 204); //grey
	var $postfield = 'panswer';
	
	
	/**
	 * called outside to genearate captcha
	 *
	 * @return void
	 */
	function get_captcha()
	{
		if (@extension_loaded('gd') && function_exists('gd_info'))
		{
			$this->gen_cpatcha_image();
		}
		else
		{
			trigger_error("GD library not found", E_USER_ERROR);
		}
		exit();
	}
	
	
	/**
	 * Validate captcha data in post fields 
	 *
	 * @return bool
	 */
	function validate_captcha()
	{
		$answer = trim(@$_POST[$this->postfield]);

		if((!empty($_SESSION['p_code']) &&  $answer != '') && ($_SESSION['p_code'] == $answer))
		{
			unset($_SESSION['p_code']);
			return true;
		}
		
		return false;
	}
	
	

	/**
	 * Generate a CAPTCHA 
	 *
	 * @return void
	 */
	function gen_cpatcha_image()
	{
		if(!isset($this->font))
		{
			$this->font = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'arial.ttf';
		}
		if($this->background[0] == 0)
		{
			$this->background[0] = mt_rand(70, 200);
			$this->background[1] = 0;
			$this->background[2] = mt_rand(70, 200);
		}
		
		//generate a secure random int by using openssl 
		$hash = bin2hex(openssl_random_pseudo_bytes(16)); 
		$num = hexdec( substr(sha1($hash) , 0,15) );
		$security_code = substr(intval($num) ,0, $this->leng);  //trim it down to $leng 
		
		//store the security code
		$_SESSION["p_code"] = $security_code;

		//Set the image width and height
		$width = $this->width;
		$height = $this->hight; 
		
		$sc = 0;
		
		//gen scale
		if($this->scale <= 0)
		{
			$sc = $height / $width;
			$sc = ($sc < 1) ? 2 : $sc + 2;
		}
		else
		{
			$sc = $this->scale;
		}
		
		//gen size
		$size = ($this->size <= 0) ? round($height / $sc) : $this->size;
	

		//Create the image resource
		$image = ImageCreate($width, $height);  

		 
		//grab our paint tools , colors, white, black and gray
		$white = ImageColorAllocate($image, $this->text[0], $this->text[1], $this->text[2]);
		$black = ImageColorAllocate($image, $this->background[0], $this->background[1], $this->background[2]);
		$grey = ImageColorAllocate($image, $this->colortwo[0], $this->colortwo[1], $this->colortwo[2]);


		//fill the world in black
		ImageFill($image, 0, 0, $black); 
		
		$text_count = strlen($security_code);
		$text = str_split($security_code); 
		$step = round($width/3) - $sc * 15; 
		$y = $size + mt_rand(0, round($height/3) - $sc) ;
		foreach($text as $txt)  
		{ 
		    $box = imagettfbbox( $size, 0,  $this->font, $txt ); 
		    $x = abs($box[2]-$box[0]); 

		    imagettftext($image, $size, mt_rand(-$this->angle, 0), $step, $y, $white, $this->font, $txt ); 
		    $step += ($x+5); 
		} 
		
		if($this->pixlized > 1)
		{
			imagefilter($image, IMG_FILTER_PIXELATE, $this->pixlized, true);
		}
		
		if($this->grid)
		{
			$transp = $black;
			$this->imagegrid($image, $width, $height, 10, $transp);
		}
		
		if($this->drawcircles)
		{
		$transp2 = ImageColorAllocate($image, $this->background[0] + 30, $this->background[1], $this->background[2] + 30);
		$this->imagecircles($image, $width, $height, 10, $transp2);
		}
		
		if ($this->noise_level > 0) 
		{
			$noise_level = $this->noise_level; 

			for ($i = 0; $i < $noise_level; ++$i) 
			{
				$x = mt_rand(2, $width);
				$y = mt_rand(2, $height);
				$size = 2;
				imagefilledarc($image, $x, $y, $size, $size, 0, 360, $white, IMG_ARC_PIE);
			}
			

		}
		
		//drow borders
		ImageRectangle($image,0,0,$width-1,$height-1,$grey); 
		
		if($this->drawcross)
		{
 			imageline($image, 0, $height/2, $width, $height/2, $grey); 
 			imageline($image, $width/2, 0, $width/2, $height, $grey); 
		}
		
		//header file
		header("Content-Type: image/png"); 

		//Output the newly created image in jpeg format 
		ImagePng($image);
   
		//Free up resources
		ImageDestroy($image);

	}
	
	/**
	 * Draw circles
	 * @param image $image given image
	 * @param width $w image's width
	 * @param height $w image's height
	 * @param size $w circles size
	 * @param color $color
	 * @return void
	 */
	function imagecircles($image, $w, $h, $s, $color)
	{
	    $x = $w/2;
	    $y = $h/2;
	    for($iw=1; $iw<$w; $iw++)
	    {
		    imagearc($image, $x, $y, $iw*$s, $iw*$s,  0, 360, $color);

	    }
	    
	}
	
	/**
	 * Draws a nice looking grid
	 * @param image $image given image
	 * @param width $w image's width
	 * @param height $w image's height
	 * @param size $w size
	 * @param color $color
	 * @return void
	 */
	function imagegrid($image, $w, $h, $s, $color)
	{
	    for($iw=1; $iw<$w/$s; $iw++){imageline($image, $iw*$s, 0, $iw*$s, $w, $color );}
	    for($ih=1; $ih<$h/$s; $ih++){imageline($image, 0, $ih*$s, $w, $ih*$s, $color );}
	}
}