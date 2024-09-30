<?
namespace api\MirrorImage;

require_once '../tools/tools.php';
require_once '../tools/curl.php';
require_once '../tools/operate.php';

use Imagick;
use tools\tools as Utils;
use tools\curl;
use tools\operate;

class MirrorImage {
	const path = DIR . '/cache';
	public string $file;
	public function main() {
		operate::dir(self::path);
		operate::delfile(self::path, 60);
		$right = $_REQUEST['right'] ? 1 : 0;
		if($url = $_REQUEST['url']) {
			$this->file = md5($url . $right);
			if(file_exists(self::path . "/{$this->file}")) return Utils::send(1, new Imagick(self::path . "/{$this->file}"), type: 'Image');
			$curl = new curl;
			$curl->location(true);
			$curl->get($url);
			if(!$curl->error && $curl->info['http_code'] == 200 && str_contains(strtolower($curl->info['content_type']), 'image')) {
				//链接正确，并且是图片
				$Imagick = new Imagick;
				try {
					$Imagick->readImageBlob($curl->result);
				} catch (\Exception $e) {
					return Utils::send(-1, '链接不为图片，或者为不支持的格式', type: 'Image');
				}
			} else {
				return Utils::send(-2, '请输入正确的图片链接', type: 'Image');
			}
		} else if($Imagick = $this->upload()) {
			// $Imagick = $upload;
		} else {
			Utils::send(-4, '请上传图片或图片的base64编码或图片的url链接', type: 'Image');
		}
		if(isset($Imagick) && $Imagick instanceof Imagick) {
			$width = $Imagick->getImageWidth();
			if($width % 2 > 0) $width += 1;
			$height = $Imagick->getImageHeight();
			if($height % 2 > 0) $height += 1;
			$x = $right ? ceil($width/2) : ceil($width/2*-1);
			$xRight = ceil($x * -1);
			$y = 0;
			if($Imagick->getImageFormat() == 'GIF') {
				$GIF = new Imagick;
				$GIF->setFormat('GIF');
				foreach($Imagick->coalesceImages() as $k => $v) {
					if($k >= 45) break;
					$v->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
					$ImagickLeft = $v->getImage();
					// $Imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
					$ImagickLeft->flopImage();
					$v->cropImage($width, $height, $x, $y);
					$ImagickLeft->cropImage($width, $height, $xRight, 0);
					$background = new Imagick;
					$background->newImage($width, $height, 'white');
					$background->setImageFormat('GIF');
					$background->compositeimage($v, Imagick::COMPOSITE_ATOP, 0, 0);
					$background->compositeimage($ImagickLeft, Imagick::COMPOSITE_ATOP, ceil($width/2), 0);
					$GIF->AddImage($background);
					$GIF->setImageDelay($v->getImageDelay());
					unset($background, $ImagickLeft, $v);
				}
				operate::set(self::path . "/{$this->file}", $GIF->getImagesBlob());
				return Utils::send(1, $GIF, type: 'Image');
			} else {
				$Imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);
				$ImagickLeft = $Imagick->getImage();
				$ImagickLeft->flopImage();
				$Imagick->cropImage($width, $height, $x, $y);
				$ImagickLeft->cropImage($width, $height, $xRight, 0);
				$background = new Imagick;
				$background->newImage($width, $height, 'none');
				$background->setImageFormat('png');
				$background->compositeimage($Imagick, Imagick::COMPOSITE_PLUS, 0, 0);
				$background->compositeimage($ImagickLeft, Imagick::COMPOSITE_PLUS, ceil($width/2), 0);
				operate::set(self::path . "/{$this->file}", $background->getImageBlob());
				return Utils::send(1, $background, type: 'Image');
			}
		} else {
			return Utils::send(-5, '系统出现错乱, 请联系管理员');
		}
	}
	public function upload() {
		$right = $_REQUEST['right'] ? 1 : 0;
		if($file = $_FILES['file']) {
			$file = reset($file);
			$this->file = md5($file['name'] . $right);
			//文件上传
			$name = self::path . '/' . $this->file;
			if(file_exists($name)) {
				//使用已有缓存
				// return new Imagick($name);
				return Utils::send(1, new Imagick($name), type: 'Image');
			} else if(move_uploaded_file($file['tmp_name'], $name)) {
				//创建缓存
				try {
					$Imagick = new Imagick($name);
				} catch (\Exception $e) {
					unlink($name);
					return Utils::send(-3, '请上传正确的图片文件，且不为webp格式');
				}
				return $Imagick;
			} else {
				return Utils::send(-3, '请使用正确的图片文件，且不为webp格式');
			}
		} else if($base64 = str_replace(' ', '+', (Utils::isParam($this->param, 'base64') ?: ''))) {
			$name = md5($base64 . $right);
			$this->file = $name;
			$name = self::path . '/' . $name;
			// operate::set($name, $base64);exit;
			if (file_exists($name)) return Utils::send(1, new Imagick($name), type: 'Image');
			$Imagick = new Imagick;
			try {
				$Imagick->readImageBlob(base64_decode($base64));
			} catch (\Exception $e) {
				return null;
			}
			return $Imagick;
		} else {
			return null;
		}
	}
}

