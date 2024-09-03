<?php
$phone = $_REQUEST['phone'] ?? '';

$length = mb_strlen(strval($phone));
if (!is_numeric($phone) || ($length < 7 || $length > 11)) {
	die(json_encode(['code' => -1, 'error' => '无效的手机号或区号！'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

$phone = substr(strval($phone), 0, 7);

$ISP = [
	1 => '移动',
	2 => '联通',
	3 => '电信',
	4 => '中国电信虚拟运营商',
	5 => '中国联通虚拟运营商',
	6 => '中国移动虚拟运营商',
	7 => '中国广电',
	8 => '中国广电虚拟运营商'
];

$file = __DIR__ . '/phone.dat';

$handle = fopen($file, 'r');
$size = filesize($file);

$item = null;


fseek($handle, 4);
$offset = fread($handle, 4);
$indexBegin = intval(implode('', unpack('L', $offset)));
$total = ($size - $indexBegin) / 9;
$position = $leftPos = 0;
$rightPos = $total;

while ($leftPos < $rightPos - 1) {
	$position = $leftPos + (($rightPos - $leftPos) >> 1);
	fseek($handle, ($position * 9) + $indexBegin);
	$idx = implode('', unpack('L', fread($handle, 4)));
	if ($idx < $phone) {
		$leftPos = $position;
	} elseif ($idx > $phone) {
		$rightPos = $position;
	} else {
		fseek($handle, ($position * 9 + 4) + $indexBegin);
		$itemIdx = unpack('Lidx_pos/ctype', fread($handle, 5));
		$itemPos = $itemIdx['idx_pos'];
		$type = $itemIdx['type'];
		fseek($handle, $itemPos);
		$itemStr = '';
		while (($tmp = fread($handle, 1)) != chr(0)) {
			$itemStr .= $tmp;
		}
		$typeStr = $ISP[$type] ?? '未知运营商';
		$itemArr = explode('|', $itemStr);
		$itemArr[] = $typeStr;
		$item = $itemArr;
		break;
	}
}

if (!is_null($item)) {
	die(json_encode(['code' => 200, 'data' => [
		'province' => $item[0],
		'city' => $item[1],
		'postcode' => $item[2],
		'area' => $item[3],
		'isp' => $item[4],
	]], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}
die(json_encode(['code' => -1, 'error' => '查询失败，可能是无效的手机号或区号！'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));