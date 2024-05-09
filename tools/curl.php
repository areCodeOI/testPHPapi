<?
namespace tools;

class curl {
	public $headers = ['User-Agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36'];
	public $setopt = [
		CURLOPT_HTTPHEADER => [],
		CURLOPT_CONNECTTIMEOUT=>10,
		CURLOPT_TIMEOUT=>30,
		CURLOPT_SSL_VERIFYHOST=>0,
		CURLOPT_SSL_VERIFYPEER=>false,
		CURLOPT_RETURNTRANSFER=>true,
		// CURLOPT_ENCODING=>'gzip',
		CURLOPT_HEADER=>true,
		CURLOPT_CUSTOMREQUEST=>'GET'
	];
	public $result;
	public $info;
	public $error;
	public $accept = 'string';
	public $opt = [];
	/**
	 * @access public
	 * @curl构造函数
	 * @param string $url 链接
	 * @param mixed $data 参数
	 * @param string $method 请求方法
	 */
	public function __construct(public ?string $url = null, public mixed $data = null, public string $method = 'get') {
		$this->headers();
		$this->setopt[CURLOPT_URL] = $this->url;
		// $this->method = strtoupper($method);
		// $this->curl($url, $data, $method);
	}
	/**
	 * @access public
	 * @curl构造函数
	 * @param string $url 链接
	 * @param mixed $data 参数
	 * @param string $method 请求方法
	 * @return object \tools\curl
	 */
	public static function init(string $url, mixed $data = null, string $method = 'get') : curl {
		return new static($url, $data, $method);
	}
	/**
	 * @access public
	 * @设置跟随跳转
	 * @param bool $bool 是否跟随跳转
	 * @return curl
	 */
	public function location(bool $bool = true) : curl {
		$this->setopt[CURLOPT_FOLLOWLOCATION] = true;
		$this->setopt[CURLOPT_AUTOREFERER] = true;
		return $this;
	}
	/**
	 * @access public
	 * @添加单个头部
	 * @param string $key 头部名字或者完整头部
	 * @param string $value 头部内容
	 * @return void
	 */
	public function addheaders(string $key, string $value = '') : void {
		if(($explode = explode(': ', $key)) && count($explode) > 1 && !$value) {
			$this->headers[$explode[0]] = $explode[1];
		} else {
			$this->headers[$key] = $value;
		}
		$this->headers();
		// return $this;
	}
	/**
	 * @access public
	 * @覆盖设置头部
	 * @param array $header 头部
	 * @return curl
	 */
	public function setHeaders(array $header) : void {
		foreach($header as $k=>$v) {
			if(($explode = explode(': ', $v)) && count($explode) > 1 && is_int($k)) {
				$this->addheaders($v);
			} else {
				$this->addheaders($k, $v);
			}
		}
	}
	/**
	 * @access public
	 * @发送get请求
	 * @param string $url 链接
	 * @param ?string $param 参数
	 * @return curl
	 */
	public function get(string $url, ?string $param = null) : curl {
		$this->method = 'get';
		$this->url = $url;
		$this->setopt[CURLOPT_URL] = $url;
		$this->data = $param;
		return $this->curl();
	}
	/**
	 * @access public
	 * @发送post请求
	 * @param string $url 链接
	 * @param ?string $param 参数
	 * @return curl
	 */
	public function post(string $url, string|array|null $param = null) : curl {
		$this->method = 'post';
		$this->setopt[CURLOPT_URL] = $url;
		$this->url = $url;
		$this->data = $param;
		return $this->curl();
	}
	/**
	 * @access public
	 * @开始进行curl
	 * @param string $url 链接
	 * @param mixed $data 请求参数
	 * @param string $method 请求方法
	 * @return object \tools\curl
	 */
	// public function curl($url, $data = null, $method = 'get') : curl {
	public function curl() : curl {
		$method = strtoupper($this->method);
		$curl = curl_init();
		if($method == 'POST') {
			$this->setopt[CURLOPT_POST] = 1;
			$this->setopt[CURLOPT_POSTFIELDS] = $this->data;
			$this->setopt[CURLOPT_CUSTOMREQUEST] = 'POST';
		} else if($method == 'GET') {
			if($this->data) {
				$this->url .= '?' . $this->data;
				$this->data = '';
			}
			if(preg_match('/[\x7f-\xff]/', $this->url)) $this->url = preg_replace_callback_array(['/([\x7f-\xff]+)/'=>function($match) {
				if(strlen($match[0]) >= 3) return urlencode($match[0]);
				return $match[0];
			}], $this->url);
			$this->setopt[CURLOPT_CUSTOMREQUEST] = 'GET';
			$this->setopt[CURLOPT_URL] = $this->url;
		}
		curl_setopt_array($curl, $this->setopt);
		$opt = curl_exec($curl);
		$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		// echo $headerSize;
		$header = substr($opt, 0, $headerSize);
		$this->opt['Header'] = $header;
		Switch(strtolower($this->accept)) {
			case 'json':
				$this->result = json_decode(substr($opt, $headerSize), true);
			break;
			case 'object':
				// print_r(json_decode(substr($opt, $headerSize)));
				$this->result = json_decode(substr($opt, $headerSize));
			break;
			default:
				$this->result = substr($opt, $headerSize);
			break;
		}
		preg_match_all('/Set-Cookie: (.+?);/im', $header, $Cookie);
		if($Cookie) {
			$this->opt['Cookie'] = tools::Cookie2json(join(';', $Cookie[1]));
		} else {
			$this->opt['Cookie'] = [];
		}
		$this->info = curl_getinfo($curl);
		$this->error = curl_errno($curl);
		$this->error = $this->error ? curl_error($curl) : 0;
		curl_close($curl);
		return $this;
	}
	/**
	 * @access private
	 * @key->value数组转头部
	 * @return void
	 */
	private function headers() : void {
		$this->setopt[CURLOPT_HTTPHEADER] = array_map(function($k, $v) {
			return $k . ': ' . $v;
		}, array_keys($this->headers), array_values($this->headers));
	}
	/**
	 * @access public
	 * @设置返回数据格式
	 * @param string $accept 返回格式
	 * @return curl
	 */
	public function accept(string $accept = 'string') : curl {
		$this->accept = $accept;
		return $this;
	}
	/**
	 * @access public
	 * @设置返回nobody
	 * @param bool $bool
	 * @return void 
	 */
	public function nobody(bool $bool) : curl {
		$this->setopt[CURLOPT_NOBODY] = $bool;
		return $this;
	}
	/**
	 * @access public
	 * @设置timeout
	 * @param int $time
	 * @return void
	 */
	public function timeout(int $time) : curl {
		$this->setopt[CURLOPT_TIMEOUT] = $time;
		return $this;
	}
	/**
	 * @access public
	 * @设置代理
	 * @param string $proxy 代理IP
	 * @param ?string $config 代理配置
	 * @return void
	 */
	public function proxy(string|bool $proxy, ?string $config = null) : curl {
		if($proxy === false) {
			unset($this->setopt[CURLOPT_PROXY]);
			unset($this->setopt[CURLOPT_PROXYUSERPWD]);
			unset($this->setopt[CURLOPT_PROXYTYPE]);
			return $this;
		}
		// $proxy = explode(':', $proxy);
		$this->setopt[CURLOPT_PROXY] = $proxy;
		// $this->setopt[CURLOPT_PROXYPORT] = $proxy[1];
		if($config && str_contains($config, ':')) $this->setopt[CURLOPT_PROXYUSERPWD] = $config;
		$this->setopt[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP; //使用http代理模式]
		return $this;
	}
	/**
	 * @access public
	 * @设置返回为object并输出
	 * @return object|null|bool
	 */
	public function object() : object|null|bool {
		return json_decode($this->result);
	}
	/**
	 * @access public
	 * @设置返回为json并输出
	 * @return array|null|bool
	 */
	public function jsonObject() : array|null|bool {
		return json_decode($this->result, true);
	}
	/**
	 * @access public
	 * @设置返回为json并输出
	 * @return array|null|bool
	 */
	public function json() : array|null|bool {
		return json_decode($this->result, true);
	}
	/**
	 * @access public
	 * @设置返回为json并输出
	 * @return array|null|bool
	 */
	public function array() : array|null|bool {
		return json_decode($this->result, true);
	}
	/**
	 * @access public
	 * @设置返回为string并输出
	 * @return string
	 */
	public function string() : string {
		return (string) ($this->result);
	}
	/**
	 * @access public
	 * @设置返回为string并输出
	 * @return string
	 */
	public function __toString() : string {
		return (string) ($this->result);
	}
}