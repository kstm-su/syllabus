<?php

include_once('./util.php');

$db = new DBAdmin();

echo 'Updating `response` table ... ';
$q = $db->selectAll('summary');
$db->begin();
while ($row = $q->fetch_assoc()) {
	$department = $db->single('SELECT * FROM `department`
		WHERE `department_id` = ?', $row['department_id']);
	$icode = $db->single('SELECT `internal_code` FROM `list`
		WHERE `id` = ?', $row['id']);
	$teacher = json_encode(array_map(function($t){ return implode($t); },
		$db->query('SELECT `staff`.`name` FROM `teacher`
		INNER JOIN `staff` ON `staff`.`staff_id` = `teacher`.`staff_id`
		WHERE `id` = ? ORDER BY `teacher`.`main` DESC',
		$row['id'])->fetch_all(MYSQL_ASSOC)));
	$semester = $db->single('SELECT `description` FROM `semester`
		WHERE `semester_id` = ?', $row['semester_id']);
	$schedule = json_encode(array_map(
		function($a){
			return array_map(function($s){ return (int)$s; }, $a);
		},
		$db->query(
			'SELECT `day`, `period`, `early`, `late`, `intensive`, `irregular`
			FROM `schedule` WHERE `id` = ?', $row['id']
		)->fetch_all(MYSQL_ASSOC)));
	$classroom = json_encode(
		$db->query('SELECT `department`.`name` as `place`,
		`room`.`name` as `name` FROM `classroom`
		INNER JOIN `room` ON `room`.`room_id` = `classroom`.`room_id`
		INNER JOIN `department`
		ON `department`.`department_id` = `room`.`department_id`
		WHERE `classroom`.`id` = ?', $row['id'])->fetch_all(MYSQL_ASSOC));
	$db->replace('response', array(
		'id' => $row['id'],
		'year' => $row['year'],
		'department' => $department['name'],
		'code' => $row['code'],
		'query' => "\\?NENDO={$row['year']}&BUKYOKU={$department['department_id']}&CODE=$icode",
		'title' => $row['title'],
		'title_english' => $row['title_english'],
		'teacher' => $teacher,
		'semester' => $semester,
		'schedule' => $schedule,
		'classroom' => $classroom,
		'credit' => $row['credit'],
		'target' => $row['target'],
		'style' => $row['style'],
		'note' => $row['note']
	));
	echo "\033[31G\033[K{$row['id']}";
}
$db->commit();
$db->close();
echo " " . PRINT_OK . PHP_EOL;
