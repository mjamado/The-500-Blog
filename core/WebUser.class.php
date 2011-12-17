<?php class WebUser {
	private $_phpSid;
	private $_nativeSid;
	private $_db;
	private $_loggedIn;
	private $_user;
	private $_sessionTimeout = "600 seconds";
	private $_sessionLifespan = "3600 seconds";
	public function __construct() {
		$this->_db = PDOSingleton::GetObj();
		session_set_save_handler(array(&$this, 'Open'),array(&$this, 'Close'),array(&$this, 'Read'),array(&$this, 'Write'),array(&$this, 'Destroy'),array(&$this, 'Gc'));
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		if(isset($_COOKIE['PHPSESSID'])) {
			$this->_phpSid = $_COOKIE['PHPSESSID'];
			$sql = "SELECT COUNT(*) FROM `sessions` WHERE `php_sid` = :php_sid AND ((NOW() - `created`) < :lifespan) AND user_agent = :user_agent AND ((NOW() - `activity`) <= :timeout OR `activity` IS NULL)";
			$stmt = $this->_db->prepare($sql);
			$stmt->execute(array(':php_sid' => $this->_phpSid,':lifespan' => $this->_sessionLifespan,':user_agent' => $userAgent,':timeout' => $this->_sessionTimeout));
			if($stmt->fetchColumn() == 0) {
				$sql = "DELETE `sessions`, `session_variables` FROM `sessions` LEFT JOIN `session_variables` ON (`session_variables`.`session_id` = `sessions`.`session_id`) WHERE `sessions`.`php_sid` = :php_sid OR NOW() - `sessions`.`created` > :lifespan";
				$stmt = $this->_db->prepare($sql);
				$stmt->execute(array(':php_sid' => $this->_phpSid,':lifespan' => $this->_sessionLifespan));
				unset($_COOKIE['PHPSESSID']);
			}
		}
		session_set_cookie_params($this->_sessionLifespan);
		session_start();
	}
	public function Touch() {
		if(isset($this->_nativeSid)) {
			$stmt = $this->_db->prepare("UPDATE `sessions` SET `activity` = NOW() WHERE `session_id` = :sid");
			$stmt->execute(array(':sid' => $this->_nativeSid));
		}
	}
	public function LoggedIn() {
		return $this->_loggedIn;
	}
	public function GetUser() {
		if(isset($this->_loggedIn) && ($this->_loggedIn == true)) return $this->_user;
		else return null;
	}
	public function Login($username, $password) {
		$obj = new User();
		$user = $obj->GetAll(array(array('username', '=', $username)));
		if(count($user) > 0) {
			$this->_user = $user[0];
			$pwd = sha1($this->_user->salt . sha1($password));
			if($pwd == $this->_user->pwd)
			{
				$this->_loggedIn = true;
				$sql = "UPDATE `sessions` SET `logged_in` = 1, `user_id` = :user_id WHERE `session_id` = :sid";
				$stmt = $this->_db->prepare($sql);
				$stmt->bindValue(':user_id', $this->_user->id, PDO::PARAM_INT);
				$stmt->bindValue(':sid', $this->_nativeSid, PDO::PARAM_INT);
				$stmt->execute();
				return true;
			}
			else return false;
		} else return false;
	}
	public function LogOut() {
		if(isset($this->_loggedIn) && ($this->_loggedIn == true)) {
			$sql = "UPDATE `sessions` SET `logged_in` = 0, `user_id` = 0 WHERE `session_id` = :sid";
			$stmt = $this->_db->prepare($sql);
			$stmt->bindValue(':sid', $this->_nativeSid, PDO::PARAM_INT);
			$stmt->execute();
			$this->_loggedIn = false;
			$this->_user = null;
			return true;
		} else return false;
	}
	public function __get($var) {
		$sql = "SELECT `var_value` FROM `session_variables` WHERE `session_id` = :sid AND `var_name` = :var";
		$stmt = $this->_db->prepare($sql);
		$stmt->bindValue(':sid', $this->_nativeSid, PDO::PARAM_INT);
		$stmt->bindValue(':var', $var);
		$stmt->execute();
		$val = $stmt->fetchColumn();
		if($val !== false) return unserialize($stmt->fetchColumn());
		else return false;
	}
	public function __set($var, $val) {
		$val = serialize($val);
		$sql = "INSERT INTO `session_variables` SET `session_id` = :sid, `var_name` = :var, `var_value` = :val";
		$stmt = $this->_db->prepare($sql);
		$stmt->bindValue(':sid', $this->_nativeSid, PDO::PARAM_INT);
		$stmt->bindValue(':var', $var);
		$stmt->bindValue(':val', $val);
		$stmt->execute();
	}
	public function Read($id) {
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$this->_phpSid = $id;
		$sql = "SELECT	`session_id`, `logged_in`, `user_id` FROM `sessions` WHERE `php_sid` = :php_sid";
		$stmt = $this->_db->prepare($sql);
		$stmt->execute(array(':php_sid' => $this->_phpSid));
		if(($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
			$this->_nativeSid = $row['session_id'];
			if($row['logged_in'] == 1) {
				$this->_loggedIn = true;
				$this->_user = new User($row['user_id']);
			} else $this->_loggedIn = false;
		} else {
			$this->_loggedIn = false;
			$this->_user = null;
			$sql = "INSERT INTO `sessions` SET `php_sid` = :php_sid, `logged_in` = 0, `user_id` = 0, `created` = NOW(), `user_agent` = :user_agent";
			$stmt = $this->_db->prepare($sql);
			$stmt->execute(array(':php_sid' => $this->_phpSid,':user_agent' => $userAgent));
			$sql = "SELECT `session_id` FROM `sessions` WHERE `php_sid` = :php_sid";
			$stmt = $this->_db->prepare($sql);
			$stmt->execute(array(':php_sid' => $this->_phpSid));
			$this->_nativeSid = $stmt->fetchColumn();
		}
		return "";
	}
	public function Destroy($id) {
		$sql = "DELETE `sessions`, `session_variables` FROM `sessions` LEFT JOIN `session_variables` ON (`session_variables`.`session_id` = `sessions`.`session_id`) WHERE `php_sid` = :php_sid";
		$stmt = $this->_db->prepare($sql);
		return $stmt->execute(array(':php_sid' => $this->_phpSid));
	}
	public function Open($savePath, $sessionName) {
		return true;
	}
	public function Write($id, $sessionData) {
		return true;
	}
	public function Close() {
		return true;
	}
	private function Gc($maxLifeTime) {
		return true;
	}
} ?>