<?php

/* データベースに接続 */
include_once('./db.php');
$db = new DBAdmin();

/* 初期化 */
$db->truncate('schedule');

$table = $db->selectAll('htmldata');

$db->begin();
while ($row = $table->fetch_assoc()) {
	$schedules = trim(mb_convert_kana($row['schedule'], 'asKV'));
	foreach(array_unique(explode(' ', $schedules)) as $schedule) {
		if ($schedule) {
			preg_match('/^(?:(集)|(日|月|火|水|木|金|土))?(?:(?:(\d+)|(不定))(後)?(?:～(\d+)(前)?)?)?$/', $schedule, $m);
			if ($m) {
				$m = array_pad($m, 8, 0);
				for ($i = (int)$m[3]; $i <= max((int)$m[6], (int)$m[3]); $i++) {
					$data = array(
						NULL,
						$row['id'],
						$m[2] ? mb_strpos(' 日月火水木金土', $m[2]) : 0,
						$i,
						$i === (int)$m[6] && $m[7],
						$i === (int)$m[3] && $m[5],
						(bool)$m[1],
						(bool)$m[4],
						$schedule);
					$db->insert('schedule', $data);
				}
			} else {
				echo "$schedule\n";
			}
		}
	}
}
$db->commit();
$db->close();
