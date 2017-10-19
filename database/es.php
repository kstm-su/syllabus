<?php

include_once('../lib/util.php');

$db = new DBAdmin();

$q = $db->selectAll('summary');
$data = '';
$count = 0;
for ($i = 0; $row = $q->fetch_assoc(); $i++) {
	$department = $db->single('SELECT * FROM `department`
		WHERE `department_id` = ?', $row['department_id']);
	$icode = $db->single('SELECT `internal_code` FROM `list`
		WHERE `id` = ?', $row['id']);
	$teacher = array_map(function($t){ return [
			'id'=> (int)$t['staff_id'],
			'name' => $t['name'],
			'main' => (bool)$t['main'],
		]; },
		$db->query('SELECT `staff`.*, `teacher`.`main` FROM `teacher`
		INNER JOIN `staff` ON `staff`.`staff_id` = `teacher`.`staff_id`
		WHERE `id` = ? ORDER BY `teacher`.`main` DESC',
		$row['id'])->fetch_all(MYSQLI_ASSOC));
	$semester = NULL;
	if (is_null($row['semester_id']) === FALSE) {
		$semester = array_map(function($t) {
			return [
				'id' => (int)$t['semester_id'],
				'first' => (bool)$t['first'],
				'second' => (bool)$t['second'],
				'intensive' => (bool)$t['intensive'],
				'description' => $t['description'],
			];
		}, $db->query('SELECT * FROM `semester` WHERE `semester_id` = ?', $row['semester_id'])->fetch_all(MYSQLI_ASSOC))[0];
	}
	$schedule = array_map(
		function($a){ return array(
			'day' => is_null($a['day']) ? NULL : (int)$a['day'],
			'period' => is_null($a['period']) ? NULL : (int)$a['period'],
			'early' => (bool)$a['early'],
			'late' => (bool)$a['late'],
			'intensive' => (bool)$a['intensive'],
			'irregular' => (bool)$a['irregular'],
			'description' => $a['description'],
		); },
		$db->query(
			'SELECT * FROM `schedule` WHERE `id` = ?', $row['id']
		)->fetch_all(MYSQLI_ASSOC));
	$classrooms = array_map(function($t) {
		return [
			'id' => (int)$t['id'],
			'name' => $t['name'],
			'department' => [
				'id' => (int)$t['did'],
				'name' => $t['dname'],
				'code' => $t['dcode'],
			],
		];
	}, $db->query('SELECT
			`room`.`room_id` as `id`,
			`room`.`name` as `name`,
			`department`.`department_id` as `did`,
			`department`.`name` as `dname`,
			`department`.`department_code` as `dcode`
		FROM `classroom`
		INNER JOIN `room` ON `room`.`room_id` = `classroom`.`room_id`
		INNER JOIN `department` ON `department`.`department_id` = `room`.`department_id`
		WHERE `classroom`.`id` = ?', $row['id'])->fetch_all(MYSQLI_ASSOC));
	$t = $db->query('SELECT `key`, `value` FROM `textdata` WHERE `id` = ?', $row['id'])->fetch_all(MYSQLI_ASSOC);
	$textdata = [];
	foreach ($t as $v) {
		if ($v['key'] === '') {
			continue;
		}
		$textdata[$v['key']] = $v['value'];
	}
	$data .= json_encode([
		'index' => [
			'_index' => 'syllabus',
			'_type' => $row['year'],
			'_id' => $row['id'],
		],
	]) . "\n";
	$data .= json_encode([
		'id' => (int)$row['id'],
		'year' => (int)$row['year'],
		'code' => $row['code'],
		'query' => "?NENDO={$row['year']}&BUKYOKU={$department['department_code']}&CODE=$icode",
		'department' => [
			'id' => (int)$row['department_id'],
			'name' => $department['name'],
			'code' => $department['department_code'],
		],
		'title_ja' => $row['title'],
		'title_en' => $row['title_english'],
		'teachers' => $teacher,
		'semester' => $semester,
		'schedules' => $schedule,
		'classrooms' => $classrooms,
		'credit' => (float)$row['credit'],
		'target' => $row['target'],
		'style' => $row['style'],
		'note' => $row['note'],
		'public' => (bool)$row['public'],
		'ches' => (bool)$row['ches'],
		'text' => $textdata,
	]) . "\n";
	if ($i % 1000 == 999) {
		$res = file_get_contents($argv[1], false, stream_context_create([
			'http' => [
				'method' => 'POST',
				'header' => implode("\r\n", [
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data),
				]),
				'content' => $data,
			],
		]));
		$data = '';
		$count += count(json_decode($res)->items);
		echo  "$count\n";
	}
}
$db->close();
$res = file_get_contents($argv[1], false, stream_context_create([
	'http' => [
		'method' => 'POST',
		'header' => implode("\r\n", [
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data),
		]),
		'content' => $data,
	],
]));
$data = '';
$count += count(json_decode($res)->items);
echo  "$count\n";
