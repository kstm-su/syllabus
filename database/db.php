<?php

require_once(dirname(__FILE__) . '/config.php');

class DB{
	private $mysqli;

	public	function access(){
		$this->mysqli=new mysqli("localhost",ADMIN_USER,ADMIN_PASSWD);
		if($this->mysqli->connect_errno){
			//echo "DB access error\n";
			return $this->mysqli->connect_errno;
		}
		$this->mysqli->set_charset("utf8");
	}

	public function first_access(){
		if($ret=$this->access()){
			return $ret;
		}
		//$this->query("DROP DATABASE Syllabus_DB");
		//$this->query("CREATE DATABASE Syllabus_DB");
		$this->query("use syllabus");

		/*$this->query("CREATE TABLE Syllabus_table_key(
			id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			NENDO char(16) NOT NULL,
			BUKYOKU char(16) NOT NULL,
			CODE char(16) NOT NULL
		)");
		$this->query("CREATE TABLE Syllabus_table_data(
			id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			key_id INT UNSIGNED NOT NULL,
			column_name TEXT,
			value TEXT
		)");
		 */
	}
	public function query($s){
		//echo $s,PHP_EOL;
		return $this->mysqli->query($s);
	}

	public function fetch_assoc($s){
		return $s->fetch_assoc();
	}

	public function cutting(){
		$this->mysqli->close();
	}

	private $serch_char=array("%","_","　");
	private $replace_char=array(" "," "," ");	

	//検索ワードからkey_idをユニークな配列で返す
	public function serch($s){
		$serch_string=str_replace($this->serch_char,$this->replace_char,$s);
		$serch_word_array=explode(' ',$serch_string);
		$return=array();
		$first=1;	
		foreach($serch_word_array as $serch_word){
			if($serch_word!==""){
				$return_this_word=array();			
				$query="SELECT key_id from Syllabus_table_data where value LIKE '%".addslashes($serch_word)."%';";
				$result=$this->query($query);
				while($row=$this->fetch_assoc($result)){
					array_push($return_this_word,$row['key_id']);
				}
				$return_this_word=array_unique($return_this_word);
				if($first==1){
					$first=0;
					$return=$return_this_word;
				}else{
					$return=array_intersect($return, $return_this_word);
				}
			}
		}
		return $return;
	}

	//key_idからURLを生成
	public function key_id_URL($id){
		if(is_numeric($id)){
			$url_serch=$this->query("SELECT * from Syllabus_table_key where id='".addslashes($id)."';");
			return $this->fetch_assoc($url_serch);
		}else{
			return NULL;
		}
	}

	//key_idから授業名を返す関数。
	//引数は必ず配列、返り値も配列。
	public function key_ids_URL_title($key_ids){
		$key_id_string=implode("' OR key_id='",$key_ids);
	/*	$result=$this->query("SELECT * from Syllabus_table_data where ( (key_id='".$key_id_string."') AND (column_name='【授業題目】' OR column_name='【授業科目】' OR column_name='【科目名】') );");
		$return =array();
		$tmp=array();
		while($row=$this->fetch_assoc($result)){
			if(!in_array($row['key_id'],$tmp,TRUE)){
				$tmp[]=$row['key_id'];
				$row+=$this->key_id_URL($row['key_id']);
				$return[]=$row;
			}
	}*/
		$return=array();
		$result=$this->query("SELECT * from Syllabus_table_summary_prototype1 where ( (key_id='".$key_id_string."') );");
		while($row=$this->fetch_assoc($result)){
			$title=array();
			$title['value']=strlen($row['title1'])>strlen($row['title2'])?$row['title1']:$row['title2'];	
			$title['key_id']=$row['key_id'];
			$title+=$this->key_id_URL($row['key_id']);
			$return[]=$title;
		}
		return $return;	
	}

	public function mysqli_real_escape_string($s){
		return mysqli_real_escape_string($this->mysqli,$s);
	}
}

class DBGuest extends mysqli {

	public function __construct() {
		parent::__construct('localhost', GUEST_USER, GUEST_PASSWD);
		$this->set_charset('utf8');
		$this->select_db(DB_NAME);
	}

	public function escape($str) {
		return $this->real_escape_string($str);
	}

	public function selectAll($table) {
		$table = $this->escape($table);
		return $this->query("SELECT * FROM `$table`");
	}

}

class DBAdmin extends DBGuest {

	public function __construct() {
		mysqli::__construct('localhost', ADMIN_USER, ADMIN_PASSWD);
		$this->set_charset('utf8');
		$this->select_db(DB_NAME);
	}

	public function begin($flags = MYSQLI_TRANS_START_READ_WRITE) {
		return $this->begin_transaction($flags);
	}

	public function insert($table, $data) {
		$table = $this->escape($table);
		$isHash = array_values($data) !== $data;
		$col = array();
		$val = array();
		foreach ($data as $c => $v) {
			$col[] = "`" . $this->escape((string)$c) . "`";
			if (is_null($v)) {
				$val[] = 'NULL';
				continue;
			}
			$val[] = "'" . $this->escape((string)$v) . "'";
		}
		$cols = implode(', ', $col);
		$vals = implode(', ', $val);
		if ($isHash) {
			$sql = "INSERT INTO `$table` ($cols) VALUES ($vals)";
		} else {
			$sql = "INSERT INTO `$table` VALUES ($vals)";
		}
		$res = $this->query($sql);
	    return $res ? $this->insert_id : FALSE;
	}

	public function replace($table, $data) {
		$table = $this->escape($table);
		$isHash = array_values($data) !== $data;
		$col = array();
		$val = array();
		foreach ($data as $c => $v) {
			$col[] = "`" . $this->escape((string)$c) . "`";
			if (is_null($v)) {
				$val[] = 'NULL';
				continue;
			}
			$val[] = "'" . $this->escape((string)$v) . "'";
		}
		$cols = implode(', ', $col);
		$vals = implode(', ', $val);
		if ($isHash) {
			$sql = "REPLACE INTO `$table` ($cols) VALUES ($vals)";
		} else {
			$sql = "REPLACE INTO `$table` VALUES ($vals)";
		}
		$res = $this->query($sql);
	    return $res ? $this->insert_id : FALSE;
	}

	public function truncate($table) {
		$table = $this->escape($table);
		return $this->query("TRUNCATE `$table`");
	}

}
