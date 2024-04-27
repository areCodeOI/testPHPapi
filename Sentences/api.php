<?
namespace api\Sentences;
require_once('../tools.php');
require_once('../operate.php');

use tools\operate;
use tools\tools;

class Sentences {
	public function __construct() {
		$this->main();
	}
	private function main() {
		$file = DIR . '/古诗词.json';
		$data = operate::get($file, true);
		$sentence = $data[array_rand($data)];
		tools::send(1, "{$sentence['content']}		——{$sentence['author']}《{$sentence['works']}》", $sentence);
	}
}