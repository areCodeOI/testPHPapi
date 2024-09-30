<?php
namespace api;

require_once '../tools/tools.php';
require_once '../tools/curl.php';
require_once '../tools/operate.php';

use tools\tools as Utils;
use tools\operate;
use tools\curl;

class BaiduEncyclopedia {
	public function __construct(public $param){
		$this->main();
	}
	private function main() {
		$type = Utils::isParam($this->param, 'type');
		if($msg = Utils::isParam($this->param, 'msg')) {
			$url = 'https://baike.baidu.com/api/searchui/searchword?word='.urlencode($msg).'&ajax=1&_='.time();
			$requests = new requests(['User-Agent'=>'Mozilla/5.0 (Linux; Android 11; PCLM10 Build/RKQ1.200928.002; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/83.0.4103.106 Mobile Safari/537.36', 'referer'=>'https://baike.baidu.com/']);
			$data = json_decode($requests->get($url)->result, true);
			// print_r($data);exit;
			$info = $data['page'];
			if($info == 'search'){
				return Utils::send(-2, '百度百科暂未收录该词条1', type: $type);
			}else{
				$requests->location = true;
				$requests->addheaders('Cookie', 'BAIDUID=FD50FCD835DE802B9E101B143C1C037D:FG=1; delPer=0; BAIDUID_BFESS=FD50FCD835DE802B9E101B143C1C037D:FG=1; BIDUPSID=FD50FCD835DE802B9E101B143C1C037D; PSTM=1672990528; BDUSS=MybVVDcDJWNGNWd3hyRDloV0E1c0hwSHo3RU15eE42TGFxQnliZjZDb3BndDlqRUFBQUFBJCQAAAAAAAAAAAEAAAAO4OVfwr27or-ow8trbQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACn1t2Mp9bdjQk; BDUSS_BFESS=MybVVDcDJWNGNWd3hyRDloV0E1c0hwSHo3RU15eE42TGFxQnliZjZDb3BndDlqRUFBQUFBJCQAAAAAAAAAAAEAAAAO4OVfwr27or-ow8trbQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACn1t2Mp9bdjQk; ');
				$url = 'https:'.$data['url'];
				$requests->get($url);
				// print_r($requests);
				preg_match('/<meta property="og:description" content="(.*?)"\s*\/?>/', $requests->result, $result);
				preg_match('/<meta property="og:image" content="(.*?)"\s*\/?>/', $requests->result, $image);
				// print_r($image);
				$result = isset($result[1]) ? $result[1] : null;
				$image = (isset($image[1]) && $image[1] ? (preg_match('/^http/', $image[1]) ? $image[1] : 'https:'.$image[1]) : null);
				if(!$result || !$image){
					return Utils::send(-2, '百度百科暂未收录该词条', type: $type);
				}
				return Utils::send(1, "±img={$image}±\n搜索词条：{$msg}\n{$result}\n在线查看：{$url}", array('search'=>$msg, 'result'=>$result, 'image'=>$image, 'url'=>$url), $type);
			}
		} else {
			return Utils::send(-1, '请输入需要搜索的词条', type: $type);
		}
	}
}