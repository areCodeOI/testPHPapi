<?
namespace tools;
class tools
{
	const RedisHost = '127.0.0.1';
	private function __construct() {
		
	}
	private function __clone() {
		
	}
	/**
	 * @access public
	 * @json|object 格式化输出
	 * @param array|object $array 需要输出的内容
	 * @return string
	 */
	public static function json(array|object $array) {
		return json_encode($array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}
	/**
	 * 输出内容
	 * @access public
	 * @param int $code 状态码
	 * @param mixed $message 返回内容
	 * @param array|Object $data 返回数据
	 * @param string $type 返回格式
	 * @param Bool $e 是否结束，默认是
	 * @return mixed
	 */
	public static function send(int $code = 1, mixed $message = null, array | Object $data = [], mixed $type = 'json', Bool $e = true) : void
	{
		// print_r(gettype($message));exit;
		if(is_array($message)) {
			if(isset($message['code'])) $code = $message['code'];
			if(isset($message['data']) && is_array($message['data'])) $data = $message['data'];
			if(isset($message['message'])) $message = $message['message'];
			if(isset($message['text'])) $message = $message['text'];
			if(isset($message['msg'])) $message = $message['msg'];
		}
		$type = strtolower((string) $type);
		switch($type) {
			case 'text':
			case 'url':
				header('content-type: text/plain; charset=utf-8');
				echo $message;
			break;
			case 'json':
				header('content-type: application/json; charset=utf-8');
				$array = [
					'code'=>$code,
					'message'=>$message
				];
				if($data) $array['data'] = $data;
				echo json_encode($array, 456);
			break;
			case 'xml':
				// 设置编码格式为UTF-8
				header('Content-Type: text/xml;charset=utf-8');
				// $doc = new DOMDocument("1.0", "UTF-8"); // 创建DOM文档对象
				$array = [
					'code'=>$code,
					'message'=>$message,
				];
				if($data) $array['data'] = $data;
				echo self::xml($array);
				// echo $doc->savexml();
			break;
			case 'image':
				if(gettype($message) == 'object') {
					if(!is_a($message, 'Imagick')) {
						header('HTTP/1.1 502');
						echo 'Bad Image';
					} else {
						$Imagick = $message;
						$message = $Imagick->getImagesBlob() ?: $Imagick;
					}
				} else if(str_starts_with($message, 'http')) {
					$http = new requests;
					$http->get($message);
					try {
						$Imagick = new \Imagick;
						$Imagick->readImageBlob($http->result);
						$message = $Imagick->getImageBlob();
					} catch (\Exception $e) {
						$Imagick = self::Text2Image($message);
						$message = $Imagick->getImageBlob();
					}
				} else {
					try{
						$Imagick = new \Imagick;
						$Imagick->readImageBlob($message);
					} catch (\Exception $e) {
						$Imagick = self::text2Image($message);
						try{
							$message = $Imagick->getImagesBlob();
						} catch (\Exception $e) {
							$message = $Imagick;
						}
						// header('HTTP/1.1 502');
						// echo 'Bad Image';
					}
				}
				try{
					$format = $Imagick->getImageFormat();
				} catch (\Exception $e) {
					$format = $Imagick->getFormat();
				}
				// echo $Imagick->getformat();exit;
				header("content-type: image/" . $format);
				echo $message;
				$Imagick->destroy();
			break;
			case 'location':
				if(str_starts_with($message, 'http') || str_contains($message, '://')) {
					preg_match('/((http[s]*|[\w]+):\/\/[^\s\r\n±]+)/', $message, $http);
					// print_r($http);exit;
					Header('Location:' . $http[1]);
				} else {
					Header('Location:' . 'http://' . $message);
				}
			break;
			default:
				header('content-type: application/json; charset=utf-8');
				$array = [
					'code'=>$code,
					'message'=>$message
				];
				if($data) $array['data'] = $data;
				echo json_encode($array, 456);
			break;
			
		}
		if($e === true) exit();
	}
	/**
	 * 判断是否是域名
	 * @access public
	 * @param String $domain 域名
	 * @param Bool $v4 是否启用IPV4检测
	 * @return String|Bool
	 */
	public static function is_domain(string $domain, Bool $v4 = false)
	{
		$domain = filter_var($domain, FILTER_SANITIZE_URL);
		$explode = explode('/', $domain);
		foreach($explode as $v)
		{
			$v = reset(explode(':', $v));
			if(filter_var($v, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return ($v4 === true ? $v : false);
			if(filter_var($v, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) return false;
			if($v == 'http' || $v == 'https' || !$v) continue;
			if(!str_starts_with($v, 'http')) $v = "http://{$v}";
			// print_r(filter_var($v, FILTER_VALIDATE_URL));
			if(filter_var($v, FILTER_VALIDATE_URL)) {
				$domain = end(explode('/', $v));
				if(preg_match('/.+\..+/', $domain)) {
					return $domain;
				}
			}
		}
		return false;
	}
	/**
	 * 获取Cookie
	 * @access public
	 * @param $key Cookie地址
	 * @param $robot 提供Cookie的账号
	 * @param $url Cookie的api，可以自己搞，我的不定时寄
	 * @return string
	 */
	public static function cookie($key, $word = false, $robot = 2830877581, $url = '') {
		return true;
	}
	/**
	 * QQ的bkn/gtk计算方法
	 * @access public
	 * @param String $skey skey或者pskey
	 * @param String|int $hash hash
	 * @return string
	 */
	public static function GTK($skey, $hash = 5381) {
		$len = strlen((String)$skey);
		for ($i = 0; $i < $len; $i++) {
			$hash += ($hash << 5 & 2147483647) + ord($skey[$i]) & 2147483647;
			$hash &= 2147483647;
		}
		return $hash & 2147483647;
	}
	public static function Xml(array $array, $head = true) {
		$XmlHead = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL;
		$replace = function($str) {
			return str_replace(['<', '>', '&'], ['&gt;', '&lt;', '&amp;'], $str);
		};
		$Xml = ($head ? '<Document>' : '');
		foreach($array as $k=>$v) {
			$k = $replace((is_numEric($k) ? 'text' : $k));
			if(is_array($v)) {
				$Xml .= "<{$k} type=\"array\">" . self::Xml($v, false) . "</{$k}>".PHP_EOL;
			} else {
				$v = $replace($v);
				$Xml .= "<{$k} type=\"" . gettype($v) . "\">{$v}</{$k}>".PHP_EOL;
			}
		}
		$Xml .= ($head ? '</Document>' : '');
		return ($head ? $XmlHead . $Xml : $Xml);
	}
	/**
	 * 一个字符串内，将大写转换为小写，小写转换为大写
	 * @access public
	 * @param $str 需要转换的字符串
	 * @return string
	 */
	public static function A2a($str) {
		return preg_replace_callback('/[A-Za-z]/', function($match) {
			$match = join('', $match);
			return ctype_upper($match) ? strtolower($match) : strtoupper($match);
		}, $str);
	}
	/**
	 * 只替换一次
	 * @access public
	 * @param $find 要搜索的字符
	 * @param $replace 替换的字符
	 * @param $str 被搜索的字符
	 * @return string
	 */
	public static function replace_once($find, $replace, $str) {
		$str = mb_str_split($str);
		$bool = false;
		$string = '';
		foreach($str as $v) {
			if($v === $find) {
				if($bool === false) {
					$string .= $replace;
					$bool = true;
				} else {
					$string .= $v;
					$bool = false;
				}
			} else {
				$string .= $v;
				$bool = false;
			}
		}
		return $string;
	}
	/**
	 * 秒转时间
	 * @access public
	 * @param int $seconds 秒数
	 * @return string 时间
	 */
	public static function formatTime($seconds) {
		$units = [
			"天" => 24 * 60 * 60,
			"小时" => 60 * 60,
			"分钟" => 60,
			"秒" => 1
		];
		
		$timeComponents = [];
		
		foreach ($units as $unit => $value) {
			if ($seconds >= $value) {
				$amount = floor($seconds / $value);
				$timeComponents[] = "{$amount}{$unit}";
				$seconds -= $amount * $value;
			}
		}
		
		return join(' ', $timeComponents) ?: 0;
	}
	/**
	 * emoji转换为UTF-32编码
	 * @access public
	 * @param string $emoji emoji表情
	 * @return string
	*/
	public static function emoji2utf(string $emoji) {
		$hex = bin2hex(mb_convert_encoding($emoji, 'UTF-32', 'UTF-8'));
		return 'u'.substr($hex, 3);
	}
	/**
	 * 文本转图片
	 * @access public
	 * @param string $text 文本
	 * @param string $type 默认值，无需填写
	 * @param int $width 宽度
	 * @param int $size 字体大小
	 * @param string $line 换行符
	 * @param Bool|int $Object 返回格式默认返回Imagick对象，可以填false返回为字符串
	 * @return ImagickObject|string
	 */
	public static function text2Image(string $text, string $type = 'start', int $width = 500, int $size = 30, string | null $line = "", Bool | int $Object = true) {
		$edge = 20;
		$base = null;
		$fontFile = './wdjy.ttf';
		Switch ($type) {
			case 'start':
				$FontSize = $size;
				$string = self::text2Image($text, 'auto', $width, $FontSize, $line);
				$Imagick = new \Imagick();
				$Draw = new \ImagickDraw();
				$Draw->setFont($fontFile);
				$Draw->setFontSize($FontSize);
				$Draw->setTextEncoding('UTF-8');
				$Draw->setFillColor(new \ImagickPixel('rgb(0, 0, 0)'));
				$Draw->Annotation($edge,  ($edge + $size), $string);
				$TextInfo = $Imagick->queryFontMetrics($Draw, $string);
				// print_r($TextInfo);exit;
				$Imagick->newImage(($TextInfo['textWidth'] + $edge * 2), ($TextInfo['textHeight'] + $edge * 2), 'white');
				$Imagick->DrawImage($Draw);
				$Imagick->setformat('png');
				return ($Object == true ? $Imagick : $Imagick->getImagesBlob());
			break;
			case 'line':
				$msg = explode($line, $text);
				$width = $info['width'];
				$FontSize = $size;
				$string = '';
				foreach($msg as $v)
				{
					$string .= self::text2Image($v, 'auto', $width, $FontSize, $line) . "\n";
				}
				$string = trim($string);
				$Imagick = new \Imagick();
				$Draw = new \ImagickDraw();
				$Draw->setFont($fontFile);
				$Draw->setFontSize($FontSize);
				$Draw->setFillColor(new \ImagickPixel('rgb(0, 0, 0)'));
				$Draw->Annotation($edge, ($edge + $size), $string);
				$TextInfo = $Imagick->queryFontMetrics($Draw, $string);
				// print_r($TextInfo);exit;
				$Imagick->newImage(($TextInfo['textWidth'] + $edge * 2), ($TextInfo['textHeight'] + $edge * 2), 'white');
				$Imagick->DrawImage($Draw);
				$Imagick->setformat('png');
				// $base = base64_encode($Imagick->getImagesBlob());
				// $Imagick->destroy();
				return ($Object == true ? $Imagick : $Imagick->getImagesBlob());
			break;
			case 'auto':
				if(!is_numEric($width) || $width < 1) $width = 500;
				$array = mb_str_split($text);
				$string = '';
				$str = '';
				$StrWidth = 0;
				$Imagick = new \Imagick();
				$Draw = new \ImagickDraw();
				$Draw->SetFontSize($size);
				$Draw->SetFont($fontFile);
				foreach($array as $v)
				{
					$string .= $v;
					$Void = $Imagick->queryFontMetrics($Draw, $string);
					if($width < $Void['textWidth'])
					{
						$str .= "\n".$v;
						$string = $v;
					} else {
						$str .= $v;
					}
					unSet($Void);
				}
				// $base = $str;
				$Imagick->destroy();
				$Draw->destroy();
				return $str;
			break;
		}
		return $base;
	}
	/**
	 * 数字格式化成汉字
	 * @access public
	 * @param number $figure 待格式化的数字
	 * @param boolean $capital 使用汉字大写数字
	 * @param boolean $mode 单字符转换模式
	 * @return string
	 */
	public static function ChineseNumerals($figure, $capital = false, $mode = true) {
		$numberChar = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];
		$unitChar = ['', '十', '百', '千', '', '万', '亿', '兆', '京', '垓', '秭', '穣', '沟', '涧', '正', '载', '极', '恒河沙', '阿僧祇', '那由他', '不可思议', '无量大数'];
		if ($capital !== false) {
			$numberChar = ['零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
			$unitChar = ['', '拾', '佰', '仟', '', '万', '亿', '兆', '京', '垓', '秭', '穣', '沟', '涧', '正', '载', '极', '恒河沙', '阿僧祇', '那由他', '不可思议', '无量大数'];
		}
		$dec = "点";
		$target = '';
		$matches = [];
			if ($mode) {
			preg_match("/^0*(\d*)\.?(\d*)/", $figure, $matches);
		} else {
			preg_match("/(\d*)\.?(\d*)/", $figure, $matches);
		}
			list(, $number, $point) = $matches;
			if ($point) {
			$target = $dec . self::toChineseNumerals($point, $capital, false);
		}
			if (!$number) {
			return $target;
		}
		$str = strrev($number);
		for ($i = 0; $i < strlen($str); $i++) {
			$out[$i] = $numberChar[$str[$i]];
			if ($mode === false) {
				continue;
			}
			$out[$i] .= $str[$i] != '0' ? $unitChar[$i % 4] : '';
			if ($i > 0 && $str[$i] + $str[$i - 1] == 0) {
				$out[$i] = '';
			}
			if ($i % 4 == 0) {
				$temp = substr($str, $i, 4);
				$out[$i] = str_replace($numberChar[0], '', $out[$i]);
				if (strrev($temp) > 0) {
					$out[$i] .= $unitChar[4 + floor($i / 4)];
				} else {
					$out[$i] .= $numberChar[0];
				}
			}
		}
		return join('', array_reverse($out)) . $target;
	}
	/**
	 * 获取访问者IP
	 * @access public
	 * @return string
	 */
	public static function userip() {
		$unknown = 'unknown';
		$ip = null;
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	/**
	 * 返回一个uuid
	 * @access public
	 * @param ?string $prefix 字首
	 * @return string
	 */
	public static function uuid(?string $prefix=""){
		$chars = md5(uniqid(mt_rand(), true));
		$uuid = substr ( $chars, 0, 8 ) . '-'
			. substr ( $chars, 8, 4 ) . '-'
			. substr ( $chars, 12, 4 ) . '-'
			. substr ( $chars, 16, 4 ) . '-'
			. substr ( $chars, 20, 12 );
		return $prefix.$uuid ;
	}
	/**
	 * 检测参数是否是数字且不小于某个值
	 * @access public
	 * @param mixed $number 需要检测的参数
	 * @param Int $val 不小于的值，默认1
	 * @return Bool
	 */
	public static function is_num(mixed $number, int $val = 1) : Bool
	{
		if(is_numEric($number)) {
			if($number > $val) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/**
	 * 将jsonp类型的json文本转换为php可用的json
	 * @access public
	 * @param ?string $jsonp jsonp文本
	 * @param ?Bool $associative=null 当为 true 时，JSON 对象将返回关联 array；当为 false 时，JSON 对象将返回 object。当为 null 时，JSON 对象将返回关联 array 或 object，这取决于是否在 flags 中设置 JSON_OBJECT_AS_ARRAY。
	 * @param int $depth=512 需要解码的结构，其最大嵌套深度。该值必须大于 0 或者小于等于 2147483647。
	 * @param int $flags=0 由 JSON_BIGINT_AS_STRING、JSON_INVALID_UTF8_IGNORE、JSON_INVALID_UTF8_SUBSTITUTE、JSON_OBJECT_AS_ARRAY、JSON_THROW_ON_ERROR 组成的掩码。
	 * @return array|Object|null|Bool
	*/
	public static function jsonp_decode(?string $jsonp, ?Bool $associative = null, int $depth = 512, int $flags = 0) {
		if(preg_match('/\((.+)\)/', $jsonp, $matches)) {
			$match = (isset($matches[1]) && $matches[1] ? $matches[1] : '');
			if($match) {
				return json_decode($match, $associative, $depth, $flags);
			} else {
				return null;
			}
		} else {
			return false;
		}
	}
	/**
	 * 获取随机字符串
	 * @access public
	 * @param int $length 字符串长度
	 * @param ?int $type 输出格式，1：数字，2：小写字母，3：大写字母。默认null，也就是随机
	 * @return string
	*/
	public static function getRandStr($length = 4, ?int$type = null) {
		//从ASCII码中获取
		$captcha = '';
		//随机取：大写、小写、数字
		for($i = 0; $i < $length; $i++){
			//随机确定是字母还是数字
			switch(($type === null ? mt_rand(1, 3) : $type)) {
				case 1:				//数字：49-57分别代表1-9
					$captcha .= chr(mt_rand(49,57));   
				break;
				case 2:				//小写字母:a-z
					$captcha .= chr(mt_rand(65,90));
				break;
				case 3:				//大写字母:A-Z
					$captcha .= chr(mt_rand(97,122));
				break;
			}
		}
		//返回
		return $captcha; 
	}
	/**
	* 判断传入的是否是QQ号/群号
	* @param mixed $uin QQ号|群号
	* @return Bool
	*/
	public static function is_uin($uin = null)
	{
		if(!$uin) return false;
		if(preg_match('/^[1-9][0-9]{4,11}$/', $uin)) return true;
		return false;
	}
	/**
	 * 获取QQ音乐的Cookies，成功会返回一个Object，失败会返回Bool，Cookie刷新错误返回int
	 * @access public
	 * @return Object|Bool|int
	 */
	public static function getQQMusicToken() {
		return new \stdClass();
	}
	/**
	 * base64
	 * @access public
	 * @param String $value 被base64的字符串
	 * @param Bool $encode true为加密其他为解密;
	 * @return String
	 */
	public static function base(String $value, $encode = true) {
		if($encode === true) return self::a2A(base64_encode($value));
		return base64_decode(self::a2A($value));
	}
	/**
	 * 判断是否是手机号
	 * @access public
	 * @param mixed $phone_number 手机号
	 * @return Bool
	 */
	public static function is_phone($phone_number) {
		$phone_number = (String) $phone_number;
		$pattern = '/^1[3-9]\d{9}$/';
		if (preg_match($pattern, $phone_number)) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Cookie转json
	 * @access public
	 * @param array $Cookie 需要转换的Cookie
	 * @return array
	 */
	public static function Cookie2json($Cookie = '') {
		$e = [];
		foreach(explode(';', $Cookie) as $v) {
			if(preg_match('/(.+?)=(.+)/', $v, $preg)) {
				$e[trim($preg[1])] = $preg[2];
			}
		}
		return $e;
	}
	/**
	 * json转Cookie
	 * @access public
	 * @param array $json jsonCookie
	 * @return string
	 */
	public static function json2Cookie(array $json) {
		$Cookie = '';
		foreach($json as $k=>$v) {
			if(is_array($v) || is_object($v)) {
				$Cookie .= "{$k}=". json_encode($v, 320) . '; ';
			} else {
				$Cookie .= "{$k}={$v}; ";
			}
			// echo $k, $Cookie;
		}
		return $Cookie;
		return trim($Cookie, '; ');
	}
	/**
	 * bytes转换为可读大小
	 * @access public
	 * @param mixed $bytes bytes
	 * @param int $r 返回小数点位数
	 * @return string
	 */
	public static function B2Size(mixed $bytes, int $r = 2) {
		if ($bytes >= 1073741824)
		{
			$bytes = number_format($bytes / 1073741824, $r) . ' GB';
		}
		elseif ($bytes >= 1048576)
		{
			$bytes = number_format($bytes / 1048576, $r) . ' MB';
		}
		elseif ($bytes >= 1024)
		{
			$bytes = number_format($bytes / 1024, $r) . ' KB';
		} else {
			$bytes = $bytes . ' bytes';
		}
		return $bytes;
	}
}