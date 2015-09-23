<?php

class DBGuest extends mysqli {

	public function __construct() {
		parent::__construct('localhost', GUEST_USER, GUEST_PASSWD);
		$this->set_charset('utf8');
		$this->select_db(DB_NAME);
	}

	/* 文字列をエスケープして返す */
	public function escape($str) {
		return $this->real_escape_string($str);
	}

	/* 指定したテーブルからすべての行をSELECT */
	public function selectAll($table) {
		$table = $this->escape($table);
		return $this->query("SELECT * FROM `$table`");
	}

	/* SQLを実行 */
	public function query($sql, ...$values) {
		$i = 0;
		$sql = preg_replace_callback('/(?<=\s)\?\??/', function($m) use ($values, &$i) {
			$value = $values[$i++];
			if (is_array($value) === FALSE) {
				$value = array($value);
			}
			$quote = $m[0] === '?' ? "'" : '`';
			$list = array();
			foreach ($value as $v) {
				if (is_null($v)) {
					$list[] = NULL;
					continue;
				}
				$list[] = $quote . $this->escape((string)$v) . $quote;
			}
			return implode(', ', $list);
		}, $sql);
		return parent::query($sql);
	}

	/* SQLを実行して1行だけ返す */
	public function single($sql, ...$values) {
		$q = $this->query($sql, ...$values);
		if ($q === FALSE) {
			return FALSE;
		}
		$res = $q->fetch_assoc();
		if (count($res) === 1){
			/* 行が一つだけの場合は文字列に変換して返す */
			return implode($res);
		}
		return $res;
	}

}

class DBAdmin extends DBGuest {

	public function __construct() {
		mysqli::__construct('localhost', ADMIN_USER, ADMIN_PASSWD);
		$this->set_charset('utf8');
		$this->select_db(DB_NAME);
	}

	/* トランザクション開始 */
	public function begin($flags = MYSQLI_TRANS_START_READ_WRITE) {
		return $this->begin_transaction($flags);
	}

	/* 指定したテーブルにデータを挿入 */
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

	/* insertメソッドに加えて、同じ行が存在する場合は置き換え */
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

	/* テーブルを空にする */
	public function truncate($table) {
		$table = $this->escape($table);
		return $this->query("TRUNCATE `$table`");
	}

}
