<?php
/**
 * 验证码插件
 * 
 * example：
 * <pre>
 * 
 * </pre>
 * @name QLib_Plugin_Captcha
 * @version  1.0 (2010-8-7 下午03:06:19)
 * @package package
 * @author peter.zyliu peter.zyliu@gmail.com
 * @since 1.0 
 **/
class Q_Utils_Captcha {
	static $V = array(
		"a", 
		"e", 
		"i", 
		"o", 
		"u", 
		"y"
	);
	static $VN = array(
		"a", 
		"e", 
		"i", 
		"o", 
		"u", 
		"y", 
		"2", 
		"3", 
		"4", 
		"5", 
		"6", 
		"7", 
		"8", 
		"9"
	);
	static $C = array(
		"b", 
		"c", 
		"d", 
		"f", 
		"g", 
		"h", 
		"j", 
		"k", 
		"m", 
		"n", 
		"p", 
		"q", 
		"r", 
		"s", 
		"t", 
		"u", 
		"v", 
		"w", 
		"x", 
		"z"
	);
	static $CN = array(
		"b", 
		"c", 
		"d", 
		"f", 
		"g", 
		"h", 
		"j", 
		"k", 
		"m", 
		"n", 
		"p", 
		"q", 
		"r", 
		"s", 
		"t", 
		"u", 
		"v", 
		"w", 
		"x", 
		"z", 
		"2", 
		"3", 
		"4", 
		"5", 
		"6", 
		"7", 
		"8", 
		"9"
	);
	
	private $_width = 160;
	private $_height = 60;
	private $_fs = 28;
	private $_dotNoiseLevel = 50;
	private $_lineNoiseLevel = 5;
	private $_wordLen = 6;
	
	private $_useNumbers = true;
	/**
	 * Generate new random word
	 *
	 * @return string
	 */
	protected function _generateWord() {
		$word = '';
		$wordLen = $this->getWordLen();
		$vowels = $this->_useNumbers ? self::$VN : self::$V;
		$consonants = $this->_useNumbers ? self::$CN : self::$C;
		
		for ($i = 0; $i < $wordLen; $i = $i + 2) {
			$consonant = $consonants[array_rand($consonants)];
			$vowel = $vowels[array_rand($vowels)];
			$word .= $consonant . $vowel;
		}
		if (strlen($word) > $wordLen) {
			$word = substr($word, 0, $wordLen);
		}
		return $word;
	}
	
	public function getWordLen() {
		return $this->_wordLen;
	}
	public function setWordLen($wordLen) {
		$this->_wordLen = $wordLen;
		return $this;
	}
	public function getWidth() {
		return $this->_width;
	}
	public function getHeight() {
		return $this->_height;
	}
	public function setWidth($width) {
		$this->_width = $width;
		return $this;
	}
	public function setHeight($height) {
		$this->_height = $height;
		return $this;
	}
	public function getFontSize() {
		return $this->_fs;
	}
	public function setFontSize($fs) {
		$this->_fs = $fs;
		return $this;
	}
	public function setDotNoiseLevel($noise = 100) {
		$this->_dotNoiseLevel = $noise;
		return $this;
	}
	public function setLineNoiseLevel($noise = 5) {
		$this->_lineNoiseLevel = $noise;
		return $this;
	}
	public function getDotNoiseLevel() {
		return $this->_dotNoiseLevel;
	}
	public function getLineNoiseLevel() {
		return $this->_lineNoiseLevel;
	}
	/**
	 * Generate random frequency
	 * 
	 * @return float
	 */
	protected function _randomFreq() {
		return 0;
		return mt_rand(700000, 1000000) / 15000000;
	}
	
	/**
	 * Generate random phase
	 * 
	 * @return float
	 */
	protected function _randomPhase() {
		return 0;
		// random phase from 0 to pi
		return mt_rand(0, 3141592) / 1000000;
	}
	
	/**
	 * Generate random character size
	 * 
	 * @return int
	 */
	protected function _randomSize() {
		return mt_rand(300, 700) / 100;
	}
	public function getFont() {
		return dirname(__FILE__) . '/Ttf/' . 2 . '.ttf';
		//return dirname(__FILE__) . '/Ttf/' . mt_rand(1, 10) . '.ttf';
	}
	/**
	 * 将验证码存入Sessioin
	 *
	 * @param string $code
	 * @return true
	 */
	public static function setToSession($namespace, $code) {
		$zf = new Zend_Session_Namespace($namespace);
		$zf->captcha = $code;
		return true;
	}
	/**
	 * 从session中取出已经生成的验证码
	 *
	 * @return string|false
	 */
	public static function getFromSession($namespace) {
		$zf = new Zend_Session_Namespace($namespace);
		if (isset($zf->captcha)) {
			return $zf->captcha;
		}
		else {
			return false;
		}
	}
	/**
	 * Constructor
	 *
	 * @param  array|Zend_Config $options 
	 * @return void
	 */
	public function __construct($options = null) {
		if (isset($options)) {
			foreach ($options as $k => $v) {
				call_user_func_array(array(
					$this, 
					'set' . ucfirst($k)
				), $v);
			}
		}
	}
	public function generate($namespace) {
		header("Content-type: image/png");
		$word = $this->_generateWord();
		$this->_generateImage($word);
		self::setToSession($namespace, $word);
		return $word;
	}
	protected function _generateImage($word) {
		if (!extension_loaded("gd")) {
			throw new Q_Exception("Image CAPTCHA requires GD extension");
		}
		if (!function_exists("imagepng")) {
			throw new Q_Exception("Image CAPTCHA requires PNG support");
		}
		if (!function_exists("imageftbbox")) {
			throw new Q_Exception("Image CAPTCHA requires FT fonts support");
		}
		$font = $this->getFont();
		if (empty($font)) {
			throw new Q_Exception("Image CAPTCHA requires font");
		}
		$w = $this->getWidth();
		$h = $this->getHeight();
		$fsize = $this->getFontSize();
		
		$img = imagecreatetruecolor($w, $h);
		$text_color = imagecolorallocate($img, 0, 0, 0);
		$bg_color = imagecolorallocate($img, 255, 255, 255);
		imagefilledrectangle($img, 0, 0, $w - 2, $h - 2, $bg_color);
		$textbox = imageftbbox($fsize, 0, $font, $word);
		$x = ($w - ($textbox[2] - $textbox[0])) / 2;
		$y = ($h - ($textbox[7] - $textbox[1])) / 2;
		imagefttext($img, $fsize, 0, $x, $y, $text_color, $font, $word);
		
		// generate noise
		for ($i = 0; $i < $this->_dotNoiseLevel; $i++) {
			imagefilledellipse($img, mt_rand(0, $w), mt_rand(0, $h), 2, 2, $text_color);
		}
		for ($i = 0; $i < $this->_lineNoiseLevel; $i++) {
			imageline($img, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), $text_color);
		}
		
		// transformed image
		$img2 = imagecreatetruecolor($w, $h);
		$bg_color = imagecolorallocate($img2, 255, 255, 255);
		imagefilledrectangle($img2, 0, 0, $w - 1, $h - 1, $bg_color);
		// apply wave transforms
		$freq1 = $this->_randomFreq();
		$freq2 = $this->_randomFreq();
		$freq3 = $this->_randomFreq();
		$freq4 = $this->_randomFreq();
		
		$ph1 = $this->_randomPhase();
		$ph2 = $this->_randomPhase();
		$ph3 = $this->_randomPhase();
		$ph4 = $this->_randomPhase();
		
		$szx = $this->_randomSize();
		$szy = $this->_randomSize();
		
		for ($x = 0; $x < $w; $x++) {
			for ($y = 0; $y < $h; $y++) {
				$sx = $x + (sin($x * $freq1 + $ph1) + sin($y * $freq3 + $ph3)) * $szx;
				$sy = $y + (sin($x * $freq2 + $ph2) + sin($y * $freq4 + $ph4)) * $szy;
				
				if ($sx < 0 || $sy < 0 || $sx >= $w - 1 || $sy >= $h - 1) {
					continue;
				}
				else {
					$color = (imagecolorat($img, $sx, $sy) >> 16) & 0xFF;
					$color_x = (imagecolorat($img, $sx + 1, $sy) >> 16) & 0xFF;
					$color_y = (imagecolorat($img, $sx, $sy + 1) >> 16) & 0xFF;
					$color_xy = (imagecolorat($img, $sx + 1, $sy + 1) >> 16) & 0xFF;
				}
				if ($color == 255 && $color_x == 255 && $color_y == 255 && $color_xy == 255) {
					// ignore background
					continue;
				}
				elseif ($color == 0 && $color_x == 0 && $color_y == 0 && $color_xy == 0) {
					// transfer inside of the image as-is
					$newcolor = 0;
				}
				else {
					// do antialiasing for border items
					$frac_x = $sx - floor($sx);
					$frac_y = $sy - floor($sy);
					$frac_x1 = 1 - $frac_x;
					$frac_y1 = 1 - $frac_y;
					
					$newcolor = $color * $frac_x1 * $frac_y1 + $color_x * $frac_x * $frac_y1 + $color_y * $frac_x1 * $frac_y + $color_xy * $frac_x * $frac_y;
				}
				imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newcolor, $newcolor, $newcolor));
			}
		}
		
		// generate noise
		for ($i = 0; $i < $this->_dotNoiseLevel; $i++) {
			imagefilledellipse($img2, mt_rand(0, $w), mt_rand(0, $h), 2, 2, $text_color);
		}
		for ($i = 0; $i < $this->_lineNoiseLevel; $i++) {
			imageline($img2, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), $text_color);
		}
		
		imagepng($img2);
		imagedestroy($img);
		imagedestroy($img2);
	}
}