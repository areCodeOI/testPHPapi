<?php
namespace api;

require_once '../tools/tools.php';
require_once '../tools/curl.php';
require_once '../tools/operate.php';

use Imagick;
use ImagickDraw;
use tools\tools as Utils;
use tools\operate;
use tools\curl;

class Face_Grey extends \Core\api {
	const timeout = 30;
	const cache = __DIR__ . '/cache';
	public string $filecache, $filename;
	public ?string $type = null;
	public function main() {
		operate::dir(self::cache);
		operate::delfile(self::cache, self::timeout);
		$this->type = 'image';
		if($url = $this->get('url')) {
			$url = $url;
		} else if($uin = $this->get('uin', 'QQ')) {
			$url = "http://q2.qlogo.cn/headimg_dl?dst_uin={$uin}&spec=5";
		} else {
			return Utils::send(-1, '请输入正确的图片链接或QQ账号', type: $this->type);
		}
		$this->filename = md5($url) . '.gif';
		$this->filecache = self::cache . "/{$this->filename}";
		if(file_exists($this->filecache)) {
			return Utils::send(1, new Imagick($this->filecache), type: $this->type);
		}
		return $this->getImage($url);
	}
	public function getImage(string $url) {
		$curl = new curl;
		$curl->get($url);
		$Imagick = new Imagick;
		try {
			$Imagick->readimageBlob($curl->result);
		} catch (\Exception $e) {
			return Utils::send(-2, '图片链接失效，请输入正确的链接，并且不为webp格式', type: $this->type);
		}
		// $Imagick->setImageFormat('GIF');
		// $Imagick->writeimages($this->cache, true);
		return $this->run($Imagick);
	}
	public function run(Imagick $Imagick) {
		if($Imagick->getImageFormat() == 'GIF') {
			$image = new Imagick;
			$image->setformat('GIF');
			foreach($Imagick->coalesceImages() as $k=>$v) {
				if($k > 30) break;
				$v->setImageFormat('GIF');
				$v->modulateImage(100, 0, 100);
				$image->addImage($v);
				$image->setImageDelay($v->getImageDelay());
				unset($v);
			}
			$Imagick->destroy();
			$image->writeimages($this->filecache, true);
			return Utils::send(1, $image, type: $this->type);
		} else {
			$Imagick->modulateImage(100, 0, 100);
			$Imagick->writeimages($this->filecache, true);
			return Utils::send(1, $Imagick, type: $this->type);
		}
	}
}