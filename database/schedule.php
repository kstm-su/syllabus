<?php

/* データベースに接続 */
include_once('./util.php');
include_once('./db.php');
$db = new DBAdmin();
$db->truncate('schedule');

$table = $db->selectAll('htmldata');
$db->begin();
while ($row = $table->fetch_assoc()) {
	$schedules = trim(kana($row['schedule']));
	foreach(explode(' ', $schedules) as $schedule) {
		if ($schedule) {
			preg_match('/^(?:(集)|(日|月|火|水|木|金|土))?(?:(?:(\d+)|(不定))(後)?(?:～(\d+)(前)?)?)?$/', $schedule, $m);
			if ($m) {
				$m = array_pad($m, 8, 0);
				for ($i = (int)$m[3]; $i <= max((int)$m[6], (int)$m[3]); $i++) {
					$data = array(
						'id' => $row['id'],
						'day' => $m[2] ? mb_strpos(' 日月火水木金土', $m[2]) : 0,
						'period' => $i,
						'early' => $i == $m[6] && (bool)$m[7],
						'late' => $i == $m[3] && (bool)$m[5],
						'intensive' => (bool)$m[1],
						'irregular' => (bool)$m[4],
						'description' => $schedule);
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
