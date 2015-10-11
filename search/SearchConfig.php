<?php

CONST IN	=0; //mySQLのIN検索
CONST NUM	=1; //数値検索
CONST STR	=2; //文字列あいまい検索
CONST MATCH	=3; //文字列一致検索
CONST SCH	=4; //スケジュール検索
CONST FLAG	=5; //0or1検索
CONST SEM	=6; //SEMESTER検索

//[検索オプション,[テーブル名,カラム名,何を取り出すか,何で検索するか]]
$SEARCHOPTIONS=[
	['id'			,[['list','id','id',NUM]]],
	['year'			,[['list','year','id',NUM]]],
	['credit'		,[['summary','credit','id',NUM]]],
	['staff'		,[['staff','name','staff_id',STR],['teacher','staff_id','id',IN]]],
	['teacher'		,[['staff','name','staff_id',STR],['teacher','staff_id','id',IN]]],
	['department'	,[['department','name','department_id',STR],['summary','department_id','id',IN]]],
	['semester'		,[['semester',SEM,'semester_id',SEM],['summary','semester_id','id',IN]]],
	['room'			,[['room','name','room_id',STR],['classroom','room_id','id',IN]]],
	['place'		,[['list','place','id',STR]]],
	['word'			,[['textdata','value','id',STR]]],
	['code'			,[['summary','code','id',STR]]],
	['title'		,[['summary','title','id',STR]]],
	['style'		,[['summary','style','id',STR]]],
	['target'		,[['summary','target','id',STR]]],
	['note'			,[['summary','note','id',STR]]],
	['schedule'		,[['schedule',SCH,'id',SCH]]]
];
