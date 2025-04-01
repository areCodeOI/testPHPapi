<?
namespace api;

use Imagick;
use ImagickDraw;

class BlueArchive {
	public function __construct() {
		$this->main();
	}
	public function main() {
		$x = (int) $this->Param('x') ?: -18; //光环位置 左右
		$y = (int) $this->Param('y') ?: 0; //光环位置 上下
		$font = __DIR__ . '/BlueArchive.otf'; //字体文件
		$fontSize = 85; //字体大小
		$height = 220; //图片固定高度
		$cross = __DIR__ . '/cache/cross2.png'; //十字
		$halo = __DIR__ . '/cache/halo.png'; //光环
		$startText = (String) $this->Param('startText') ?: 'Blue'; //文字开始部分
		$startText = current(mb_str_split($startText, 64));
		$endText = (String) $this->Param('endText') ?: 'Archive'; //文字结束部分
		$endText = current(mb_str_split($endText, 64));
		$angle = ((int) $this->Param('angle') ?: 15) * -1; //文字倾斜角度
		$angle = $angle < -15 || $angle > 15 ? 15 : $angle;
		$angle = -15;
		$color = (String) $this->Param('color') ?: 'white';
		$color = $this->color($color);
		$crossImagick = new Imagick($cross); //声明十字
		$crossImagick->reSizeImage(210, 210, Imagick::FILTER_LANCZOS, 1);
		$haloImagick = new Imagick($halo); //声明光环
		$haloImagick->reSizeImage(210, 210, Imagick::FILTER_LANCZOS, 1);
		$Imagick = new Imagick(); //声明主背景图
		$startImagickDraw = new ImagickDraw; //声明文字开始部分
		$startImagickDraw->setFont($font); //引用字体
		$startImagickDraw->setfillcolor('#128AFA'); //设置为蓝色
		$startImagickDraw->setfontSize($fontSize); //设置字体大小
		$startWidth = $Imagick->queryFontMetrics($startImagickDraw, $startText)['textWidth']; //获取开始文本所需要的宽度
		$startImagickDraw->skewX($angle); //设置倾斜度
		$startImagickDraw->annotation(90, 150, $startText);
		$endImagickDraw = new ImagickDraw; //声明文字结束部分
		$endImagickDraw->setfont($font); //引用字体
		$endImagickDraw->setfillcolor('#2B2B2B'); //设置为黑色
		$endImagickDraw->setfontSize($fontSize);//设置字体大小
		$endWidth = $Imagick->queryFontMetrics($endImagickDraw, $endText)['textWidth']; //获取结束文本所需要的宽度
		$endImagickDraw->skewX($angle);
		$endImagickDraw->annotation($startWidth + 90, 150, $endText);
		$width = $startWidth + $endWidth + 120;
		$width = intval($width < 220 ? 220 : $width);
		$Imagick->newImage($width, $height, $color);
		$excuWidth = (preg_match('/[0-9a-z]+/i', $endText) ? 5 : 0);
		$excuHeight = (preg_match('/[0-9a-z]+/i', $endText) ? 0 : 10);
		$Imagick->compositeImage($haloImagick, Imagick::COMPOSITE_OVER, intval($startWidth - $excuWidth + $x), intval($y - $excuHeight)); //合并光环
		$Imagick->drawImage($startImagickDraw);
		$Imagick->drawImage($endImagickDraw);
		$Imagick->compositeImage($crossImagick, Imagick::COMPOSITE_OVER, intval($startWidth - $excuWidth + $x), intval($y - $excuHeight)); //合并十字
		$Imagick->setImageformat('png');
		$this->send($Imagick);
	}
	private function color($color) : String {
		if(ctype_xdigit($color)) {
			if(str_starts_with($color, '#')) {
				$color = $color;
			} else {
				$color = '#' . $color;
			}
		}
		$Imagick = new Imagick;
		try {
			$Imagick->newImage(50, 50, $color);
		} catch (\Exception $e) {
			$Imagick->destroy();
			return 'white';
		}
		$Imagick->destroy();
		return $color;
	}

	private function Param(string $key) : String {
		return isset($_REQUEST[$key]) ? $_REQUEST[$key] : '';
	}
	private function send(mixed $message) {
		if($message instanceof Imagick) {
			header('content-type: image/png');
			echo $message->getImageBlob();
		} elseif (is_array($message) || is_object($message)) {
			header('content-type: application/json; charset=utf-8');
			echo json_encode($message, JSON_UNESCAPED_UNICODE);
		} else {
			header('content-type: text/plain; charset=utf-8');
			echo $message;
		}
	}
}
new BlueArchive();