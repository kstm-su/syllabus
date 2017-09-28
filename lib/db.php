<?php

function isHashArray($arr) {
	return array_values($arr) !== $arr;
}

class DBGuest extends mysqli {

	public function __construct() {
		parent::__construct('127.0.0.1', GUEST_USER, GUEST_PASSWD);
		$this->set_charset('utf8');
		$this->select_db(DB_NAME);
	}

	/* 文字列をエスケープして返す */
	public function escape($str) {
		return $this->real_escape_string($str);
	}

	public function begin() {
		return $this->query('BEGIN');
	}

	public function commit($flags = NULL, $name = NULL) {
		return $this->query('COMMIT');
	}

	/* 指定したテーブルからすべての行をSELECT */
	public function selectAll($table) {
		$table = $this->escape($table);
		return $this->query('SELECT * FROM ??', $table);
	}

	/* プレースホルダに値を挿入 */
	public function sql($sql, ...$values) {
		$i = 0;
		$sql = preg_replace_callback('/\\\\([\\\\?:])|(\{?)(?:(\?\??)|(::?)(\w+))(\}?)/',
			function($m) use ($values, &$i) {
				if ($m[1]) {
					return $m[1];
				}
				if (isset($m[5]) && $m[5]) {
					$quote = $m[4] === '::' ? '`' : "'";
					foreach ($values as $v) {
						if (is_array($v) && isset($v[$m[5]])) {
							$value = $v[$m[5]];
							break;
						}
					}
				} else {
					$quote = $m[3] === '??' ? '`' : "'";
					$value = $values[$i++];
				}
				if (is_array($value) === FALSE) {
					$value = array($value);
				}
				$list = array();
				foreach ($value as $v) {
					if (is_null($v)) {
						$list[] = 'NULL';
						continue;
					}
					if ($m[2] === '{' && isset($m[6])) {
						if (is_array($v)) {
							$list[] = $this->sql(...$v);
						} else {
							$list[] = $this->sql(...$value);
							break;
						}
					} else {
						if (is_bool($v)) {
							$v = (int)$v;
						}
						$list[] = $quote . $this->escape((string)$v) . $quote;
					}
				}
				return implode(', ', $list);
			}, $sql);
		$this->last = $sql;
		//print("[DEBUG] $sql\n");
		return $sql;
	}

	/* SQLを実行 */
	public function query($sql, ...$values) {
		return parent::query($this->sql($sql, ...$values));
	}

	/* SQLを実行して1行だけ返す */
	public function single($sql, ...$values) {
		$q = $this->query($sql, ...$values);
		if ($q === FALSE) {
			return FALSE;
		}
		$res = $q->fetch_assoc();
		if (count($res) === 1) {
			/* 列が一つだけの場合は文字列に変換して返す */
			return implode($res);
		}
		return $res;
	}

	/* SQLを実行してすべての行を返す */
	public function fetchAll($sql, ...$values) {
		$q = $this->query($sql, ...$values);
		if ($q === FALSE) {
			return FALSE;
		}
		$res = $q->fetch_all(MYSQLI_ASSOC);
		if (isset($res[0]) && count($res[0]) === 1) {
			return array_column($res, array_keys($res[0])[0]);
		}
		return $res;
	}

}

class DBAdmin extends DBGuest {

	public function __construct() {
		mysqli::__construct('127.0.0.1', ADMIN_USER, ADMIN_PASSWD);
		$this->set_charset('utf8');
		$this->select_db(DB_NAME);
	}

	/* 指定したテーブルにデータを挿入 */
	public function insert($table, $data) {
		$table = $this->escape($table);
		$isHash = isHashArray($data);
		$col = array();
		$val = array();
		foreach ($data as $c => $v) {
			$col[] = $c;
			$val[] = $v;
		}
		if ($isHash) {
			$res = $this->query('INSERT INTO ?? (??) VALUES (?)',
				$table, $col, $val);
		} else {
			$res = $this->query('INSERT INTO ?? VALUES (?)',
				$table, $val);
		}
		return $res ? $this->insert_id : FALSE;
	}

	/* insertメソッドに加えて、同じ行が存在する場合は置き換え */
	public function replace($table, $data) {
		$table = $this->escape($table);
		$isHash = isHashArray($data);
		$col = array();
		$val = array();
		foreach ($data as $c => $v) {
			$col[] = $c;
			$val[] = $v;
		}
		if ($isHash) {
			$res = $this->query('REPLACE INTO ?? (??) VALUES (?)',
				$table, $col, $val);
		} else {
			$res = $this->query('REPLACE INTO ?? VALUES (?)',
				$table, $val);
		}
		return $res ? $this->insert_id : FALSE;
	}

	/* テーブルを空にする */
	public function truncate($table) {
		$table = $this->escape($table);
		return $this->query('TRUNCATE ??', $table);
	}

}
