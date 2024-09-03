<?php

$month = str_pad($_REQUEST['month'] ?? date('m'), 2, '0', STR_PAD_LEFT);
$day = str_pad($_REQUEST['day'] ?? date('d'), 2, '0', STR_PAD_LEFT);

$url = "https://baike.baidu.com/cms/home/eventsOnHistory/{$month}.json";
$body = file_get_contents($url);

$json = json_decode($body, true);

$list = $json[$month][$month . $day];

$index = 0;
$data = array_map(function ($item) use (&$index) {
	return [
		'id' => $index++,
		'title' => trim(strip_tags($item['title'])),
		'cover' => $item['cover'] ? $item['pic_share'] : null,
		'desc' => trim(strip_tags($item['desc'])),
		'year' => $item['year'],
		'url' => $item['link']
	];
}, $list);

echo json_encode([
	'code' => 200,
	'date' => $month . ' - ' . $day,
	'data' => $data
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);