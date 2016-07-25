<?php

/**
 * 字符串处理
 *
 */
class Q_Utils_String
{
	/**
	 * 
	 * @var Q_Utils_String
	 */
	private static $string;
	
	/**
	 * 
	 * @return Q_Utils_String
	 */
	public static function format()
	{
		if (!self::$string)
		{
			self::$string = new Q_Utils_String();
		}
		return self::$string;
	}
	
	/**
	 *
	 * 按字来切分字符 (UTF－8截字)
	 *
	 * @param String $str
	 * @param Integer $length
	 * @param Integer $start
	 * @param String $encoding
	 * @return String
	 */
	public function mbSubstr($str, $length, $start = 0, $suffix = '...', $encoding = "utf-8")
	{
		if (!is_string($str))
		{
			return $str;
		}
		$str = trim($str);
		if (mb_strlen($str) == $length)
		{
			return $str;
		}
		$strs = mb_substr($str, $start, $length, $encoding);
		if ((mb_strlen($str) / 3) > $length)
		{
			$strs .= $suffix;
		}
		return $strs;
	}
	
	/**
	 * 按字节来切分字符
	 *
	 * @param String $str
	 * @param Integer $length
	 * @param Integer $start
	 * @param String $encoding
	 */
	public function mbStrcut($str, $length, $start = 0, $encoding = "utf-8")
	{
		return mb_strcut($str, $start, $length, $encoding);
	}
	
	/**
	 * 判定是否是某个编码的合法字串
	 *
	 * @param String $str
	 * @param String $coding
	 * @return String
	 */
	public function isValidCoding($str, $coding = "UTF-8")
	{
		return mb_check_encoding($str, $coding);
	}
	
	/**
	 * gbk 转 unicode
	 *
	 * @param String $text
	 * @return String
	 */
	public function baseGbkToUnicode($str)
	{
		$rtext = "";
		preg_match_all("/[\x81-\xfe]?./", $str, $regs);
		foreach ($regs[0] as $v)
		{
			if (ord($v) > 127)
			{
				$rtext .= "&#" . base_convert(bin2hex(iconv("gb2312", "ucs-2", $v)), 16, 10) . ";";
			}
			else
			{
				$rtext .= $v;
			}
		}
		return $rtext;
	}
	
	/**
	 * 转换16进制到UTF－8
	 *
	 * @param String $text
	 * @return String
	 */
	public function unicode16ToUtf($text)
	{
		preg_match_all("/\%u([a-zA-Z0-9]{0,4}+)/", $text, $regs);
		foreach ($regs[0] as $v)
		{
			$to = "&#" . base_convert(str_replace("%u", "", $v), 16, 10) . ";";
			$text = str_replace($v, $to, $text);
		}
		return $text;
	}
	
	/**
	 * utf 转 gbk
	 *
	 * @param String $str
	 * @return String
	 */
	public function utfToGbk($str)
	{
		$info = mb_convert_encoding($str, "gbk", "utf-8");
		return $info;
	}
	
	/**
	 * UTF8 转换
	 * @param String $str
	 * @return String
	 */
	public function gbkToUtf8($str)
	{
		$str = mb_convert_encoding($str, "utf-8", "gbk");
		$convmap = array(
			0x0080, 
			0xffff, 
			0x0000, 
			0xffff
		);
		//0x0026, 0x0026, 0x0000, 0xffff);  <-这个是转&号为&amp;
		$str = mb_encode_numericentity($str, $convmap, "utf-8");
		return $str;
	}
	
	/**
	 * Hex2Str 解码
	 * @param String $msg
	 * @return String
	 */
	public function Hex2Str($msg, $isString = true)
	{
		$outstr = "";
		$l = strlen($msg);
		if ($l % 2 != 0)
		{
			$l--;
		}
		$par = "H" . $l;
		$outstr = pack($par, $msg);
		if ($isString == false)
		{
			return $outstr;
		}
		return $this->gbkToUtf8($this->unicodeToGBK($outstr));
	}
	
	/**
	 * Str2Hex 编码
	 * @param String $msg
	 * @return String
	 */
	public function Str2Hex($msg)
	{
		$str = unpack("C*", $this->utf8TOunicode($msg));
		$result = "";
		for ($i = 1; $i <= count($str); $i++)
		{
			$tmp = DecHex($str[$i]);
			if (strlen($tmp) == 1)
			{
				$tmp = "0" . $tmp;
			}
			$result .= $tmp;
		}
		return $result;
	}
	
	/**
	 * 转换编码
	 * @param String $str
	 * @return String
	 */
	public function gbkToUnicode($str)
	{
		return mb_convert_encoding($str, "unicode", "gbk");
	}
	
	/**
	 * 转换成GBK
	 * @param String $str
	 * @return String
	 */
	public function unicodeToGBK($str)
	{
		return mb_convert_encoding($str, "gbk", "unicode");
	}
	
	/**
	 * unicode 转UTF-8
	 * @param String $str
	 * @return String
	 */
	public function unicodeTOUtf8($str)
	{
		return mb_convert_encoding($str, "utf-8", "unicode");
	}
	
	/**
	 * UTF-8转unicode
	 *
	 * @param String $str
	 * @return String
	 */
	public function utf8TOunicode($str)
	{
		return mb_convert_encoding($str, "unicode", "utf-8");
	}
	
	/**
	 * 对addslashes处理中文字符出现错误的解决
	 *
	 * @param string  $string 要转义的字符串
	 * @param boolean $escape: 是否对中文特殊处理，默认为false
	 * @return string
	 */
	public function addslash($string, $escape = false)
	{
		if (!$escape)
		{
			return addslashes($string);
		}
		$string = ereg_replace("([^\xA1-\xFE])[\x5c]", "\\1\\", $string);
		$string = str_replace("\\", "\\\\", $string);
		$string = str_replace("'", "\\'", $string);
		$string = str_replace("\"", "\\\"", $string);
		return $string;
	}
	
	/**
	 * CRC转换为16进制
	 *
	 * @param String $str
	 * @return String
	 */
	public function crc2Dechex($val)
	{
		return dechex(crc32($val));
	}
	
	/**
	 * 对象进行编码crc32有符号
	 *
	 * @param String $val
	 * @return String
	 */
	public function obj2Crc2($val)
	{
		$crc = crc32($val);
		return $crc;
	}
	
	/**
	 * 格式化html代码的 (包括中文)
	 *
	 * @param String $str
	 * @return String
	 */
	public function htmlEntities($str)
	{
		return htmlentities($str);
	}
	
	/**
	 * 格式化html代码的 (不包括中文)
	 *
	 * @param String $str
	 * @return String
	 */
	public function htmlSpecialChars($str)
	{
		return htmlspecialchars($str);
	}
	
	/**
	 *
	 * 按字来切分字符 (UTF－8截字) 并 格式化代码
	 *
	 * @param String $str
	 * @param Integer $length
	 * @param Integer $start
	 * @param String $suffix
	 * @param String $encoding
	 * @return String
	 */
	public function mbSubstrHtml($str, $length, $start = 0, $suffix = '...', $encoding = "utf-8")
	{
		return $this->htmlSpecialChars($this->mbSubstr($str, $length, $start, $suffix, $encoding));
	}
	
	/**
	 * 替换数据
	 *
	 * @param String $str
	 * @param String $pattern
	 * @param String $replacement
	 * @return String
	 */
	public function pregReplace($str, $pattern = '/\[(.*?)\]/i', $replacement = '')
	{
		return preg_replace($pattern, $replacement, $str);
	}
	
	/**
	 * 获取字符串首字母, 可传入汉字，字母 ，数字
	 *
	 * @param String $string
	 * @return String
	 */
	public function getFirstLetter($string)
	{
		$string = iconv('utf-8', 'gbk', $string); //字符编码转换
		$dict = array(
			'a' => 0xB0C4, 
			'b' => 0xB2C0, 
			'c' => 0xB4ED, 
			'd' => 0xB6E9, 
			'e' => 0xB7A1, 
			'f' => 0xB8C0, 
			'g' => 0xB9FD, 
			'h' => 0xBBF6, 
			'j' => 0xBFA5, 
			'k' => 0xC0AB, 
			'l' => 0xC2E7, 
			'm' => 0xC4C2, 
			'n' => 0xC5B5, 
			'o' => 0xC5BD, 
			'p' => 0xC6D9, 
			'q' => 0xC8BA, 
			'r' => 0xC8F5, 
			's' => 0xCBF9, 
			't' => 0xCDD9, 
			'w' => 0xCEF3, 
			'x' => 0xD188, 
			'y' => 0xD4D0, 
			'z' => 0xD7F9
		);
		$letter = substr($string, 0, 4);
		if ($letter >= chr(0x81) && $letter <= chr(0xfe))
		{
			$num = hexdec(bin2hex(substr($string, 0, 2)));
			foreach ($dict as $k => $v)
			{
				if ($v >= $num)
					break;
			}
			return strtoupper($k);
		}
		elseif ((ord($letter) > 64 && ord($letter) < 91) || (ord($letter) > 96 && ord($letter) < 123))
		{
			return strtoupper($letter{0});
		}
		elseif ($letter >= '0' && $letter <= '9')
		{
			return $letter;
		}
		else
		{
			return false;
		}
	}
	/**
	 * 去除html标签
	 *
	 * @param String $string
	 * @return String
	 */
	public function pregReplaceHtml($string)
	{
		if (empty($string))
		{
			return $string;
		}
		$pattern = array(
			"'<script[^>]*?>[\s\S]*?</script>'si", 
			"'<[\/\!]*?[^<>]*?>'si", 
			"'<iframe[^>]*?>[\s\S]*?</iframe>'si"
		);
		$replacement = '';
		return preg_replace($pattern, $replacement, $string);
	}
	
	/**
	 * 去除危险标签
	 *
	 * @param String $string
	 * @return String
	 */
	public function replaceDangerCode($string)
	{
		if (empty($string))
		{
			return $string;
		}
		$pattern = array(
			"'<style[^>]*?>[\s\S]*?</style>'si", 
			"'<iframe[^>]*?>[\s\S]*?</iframe>'si", 
			"'<script[^>]*?>[\s\S]*?</script>'si", 
			"'<link[^>]*?/>'si"
		);
		$replacement = '';
		return preg_replace($pattern, $replacement, $string);
	}
	
	/* 过滤危险html */
	public function filter_html($str)
	{
		/* 过滤style标签 */
		return preg_replace_callback(
			/* 过滤style标签内容 */
			'/(\<\s*style[^\>]*\>)((?:(?!\<\s*\/\s*style\s*\>).)*)(\<\s*\/\s*style\s*\>)?/i', create_function('$str', 'return $str[1] . filter_css($str[2]) . $str[3];'), preg_replace(array(
					/* 删除html注释 */
					'/\<\!\-\-.*?\-\-\>/i',
					/* 删除标签：script、link、object、embed、iframe、frame、frameset */
					'/\<\s*(script|object|embed|link|i?frame(set)?)[^\>]*\>(.*?\<\s*\/\s*\\1\s*\>)?/i',
					/* 删除事件、javascript协议、css表达式 */
					'/\<[^\>]+((on[a-z]+\s*\=|(javascript|vbscript|behavior)\s*\:[^\;\"\\\']|(import|expression)\s*\()[^\>]*)+\>?/i'
		), '', $str));
	}
	
	/* 过滤样式正文 */
	function filter_css($str)
	{
		/* 删除注释、javascript协议、表达式 */
		return preg_replace(array(
			'/(\/\*((?!\*\/).)*\*\/|\/\*|\*\/)/i', 
			'/(expression|import)\s*\((.*?\))?|(javascript|vbscript|behavior)\s*\:/i'
		), '', $str);
	}
	
	/**
	 * 去除非0-9A-Z的字符
	 * @param String $str
	 * @return String
	 */
	public function filterString($str)
	{
		return preg_replace('/[^\w]/i', '', $str);
	}
	/**
	 * SEO
	 *
	 * @param String $string
	 * @return String
	 */
	public function toSEO($string)
	{
		$search = array(
			' ', 
			'%20', 
			'&', 
			'?', 
			'@', 
			'\/'
		);
		$replace = array(
			'_', 
			'_', 
			'_', 
			'_', 
			'_', 
			'_'
		);
		$string = str_replace($search, $replace, $string);
		return $string;
	}
	
	/**
	 * 字符替换
	 *
	 * @param String $document
	 * @return String
	 */
	public function striptext($document)
	{
		$search = array(
			"'<script[^>]*?>.*?</script>'si",  // strip out javascript
			"'<[\/\!]*?[^<>]*?>'si",  // strip out html tags
			"'([\r\n])[\s]+'",  // strip out white space
			"'&(quot|#34|#034|#x22);'i",  // replace html entities
			"'&(amp|#38|#038|#x26);'i",  // added hexadecimal values
			"'&(lt|#60|#060|#x3c);'i", 
			"'&(gt|#62|#062|#x3e);'i", 
			"'&(nbsp|#160|#xa0);'i", 
			"'&(iexcl|#161);'i", 
			"'&(cent|#162);'i", 
			"'&(pound|#163);'i", 
			"'&(copy|#169);'i", 
			"'&(reg|#174);'i", 
			"'&(deg|#176);'i", 
			"'&(#39|#039|#x27);'", 
			"'&(euro|#8364);'i",  // europe
			"'&a(uml|UML);'",  // german
			"'&o(uml|UML);'", 
			"'&u(uml|UML);'", 
			"'&A(uml|UML);'", 
			"'&O(uml|UML);'", 
			"'&U(uml|UML);'", 
			"'&szlig;'i"
		);
		$replace = array(
			"", 
			"", 
			"\\1", 
			"\"", 
			"&", 
			"<", 
			">", 
			" ", 
			chr(161), 
			chr(162), 
			chr(163), 
			chr(169), 
			chr(174), 
			chr(176), 
			chr(39), 
			chr(128), 
			"?", 
			"?", 
			"?", 
			"?", 
			"?", 
			"?", 
			"?"
		);
		
		return preg_replace($search, $replace, $document);
	}
	
	public function stripTags($html)
	{
		$search = array(
			'&nbsp;', 
			"'<script[^>]*?>.*?</script>'si", 
			"'<[\/\!]*?[^<>]*?>'si", 
			"'([\r\n])[\s]+'", 
			"'&(quot|#34|#034|#x22);'i", 
			"'&(amp|#38|#038|#x26);'i", 
			"'&(lt|#60|#060|#x3c);'i", 
			"'&(gt|#62|#062|#x3e);'i", 
			"'&(nbsp|#160|#xa0);'i", 
			"'&(iexcl|#161);'i", 
			"'&(cent|#162);'i", 
			"'&(pound|#163);'i", 
			"'&(copy|#169);'i", 
			"'&(reg|#174);'i", 
			"'&(deg|#176);'i", 
			"'&(#39|#039|#x27);'", 
			"'&(euro|#8364);'i", 
			"'&a(uml|UML);'", 
			"'&o(uml|UML);'", 
			"'&u(uml|UML);'", 
			"'&A(uml|UML);'", 
			"'&O(uml|UML);'", 
			"'&U(uml|UML);'", 
			"'&szlig;'i", 
			"\n", 
			"\r", 
			"\t", 
			"\0", 
			"　"
		);
		return trim(str_replace($search, '', strip_tags($html)));
	}
	
	function code2utf($num)
	{
		if ($num < 128)
			return chr($num);
		if ($num < 2048)
			return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
		if ($num < 65536)
			return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
		if ($num < 2097152)
			return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
		return '';
	}
	function deu($s)
	{
		return $this->code2utf(hexdec($s));
	}
	function conv_js_utf8($s)
	{
		return iconv('UTF-8', 'GBK', preg_replace('/(\\\u|%u)(....)/e', '$this->deu("\\2")', $s));
	}

}