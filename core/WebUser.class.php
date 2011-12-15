<?php

/**
 * This class handles sessions in a database driven manner. It's faster than
 * PHP native session handling, by means of the database access itself (PHP
 * native session handler uses flatfiles) and using a row for each value,
 * whereas PHP uses an enormous serialized array (and PHP is not known for it's
 * string performance). It's also safer, since sessions are usually placed in a
 * server directory where, in shared hostings, everybody has writing
 * privileges. It's not easy to retrieve a session identifier from that
 * directory, but it's yet another attack vector.
 *
 * It has a few bad things, though. The preferred way to do this it's not with
 * databases, which is allready an improvement over native sessions, and
 * certainly not with PDO, which has a slight performance hit; the optimal way
 * would be with memory managment alone, using memcache or similar.
 *
 * Ultimately, my decision to make it with database and PDO was made taking
 * into account both the fact that this is just a proof of concept and the
 * reduced availability of memcache in regular hosting providers, more so on
 * shared hosting.
 *
 * @author Marco Amado <mjamado@dreamsincode.com>
 * @since 2011-11-25 [last review: 2011-11-29]
 * @license Creative Commons 2.5 Portugal BY-NC-SA
 * @link http://www.dreamsincode.com/
 */
class WebUser
{
	/**
	 * The original, PHP-style session id. It should be a 32 char string.
	 *
	 * @var string
	 */
	private $_phpSid;
	/**
	 * The native (as in native of the database table) session id.
	 * Integer, way faster than strings.
	 *
	 * @var int
	 */
	private $_nativeSid;
	/**
	 * @var PDO
	 */
	private $_db;
	/**
	 * Whether the current user is logged in.
	 *
	 * @var bool
	 */
	private $_loggedIn;
	/**
	 * @var User
	 */
	private $_user;
	/**
	 * Time of inactivity until the user is considered to have
	 * left the site.
	 *
	 * @var string
	 */
	private $_sessionTimeout = "600 seconds";
	/**
	 * Time of inactivity until the session expires.
	 *
	 * @var string
	 */
	private $_sessionLifespan = "3600 seconds";

	/**
	 * Ctor; the only really interesting part is the override of the default
	 * session handlers.
	 */
	public function __construct()
	{
		$this->_db = PDOSingleton::GetObj();

		/* This will override the native session handlers
		 * The referenced functions are down there
		 */
		session_set_save_handler(
			array(&$this, 'Open'),
			array(&$this, 'Close'),
			array(&$this, 'Read'),
			array(&$this, 'Write'),
			array(&$this, 'Destroy'),
			array(&$this, 'Gc')
		);

		/* I shall say this only once: verifying if the user agent is consistent
		 * between requests is another layer of security. One easily broken,
		 * sure, but it shouldn't be the only one. However, I'm not confortable
		 * fetching and logging user's IP adresses, at least not in this proof
		 * of concept scenario.
		 *
		 * The rest should be pretty self explanatory.
		 */
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		if(isset($_COOKIE['PHPSESSID']))
		{
			$this->_phpSid = $_COOKIE['PHPSESSID'];

			$sql = "SELECT COUNT(*) FROM `sessions`
					WHERE `php_sid` = :php_sid
						AND ((NOW() - `created`) < :lifespan)
						AND user_agent = :user_agent
						AND ((NOW() - `activity`) <= :timeout
							OR `activity` IS NULL)";
			$stmt = $this->_db->prepare($sql);
			$stmt->execute(array(
				':php_sid' => $this->_phpSid,
				':lifespan' => $this->_sessionLifespan,
				':user_agent' => $userAgent,
				':timeout' => $this->_sessionTimeout
			));

			if($stmt->fetchColumn() == 0)
			{
				$sql = "DELETE `sessions`, `session_variables`
						FROM `sessions`
						LEFT JOIN `session_variables` ON (`session_variables`.`session_id` = `sessions`.`session_id`)
						WHERE `sessions`.`php_sid` = :php_sid
							OR NOW() - `sessions`.`created` > :lifespan";
				$stmt = $this->_db->prepare($sql);
				$stmt->execute(array(
					':php_sid' => $this->_phpSid,
					':lifespan' => $this->_sessionLifespan
				));

				unset($_COOKIE['PHPSESSID']);
			}
		}

		session_set_cookie_params($this->_sessionLifespan);
		session_start();
	}

	/**
	 * This method should be called whenever the user requests a page. In this
	 * context, it's called right at the router of the app, therefore beeing
	 * called even if the user's request is invalid. Could be controlled, I was
	 * willing to take the tradeof between simplicity and another security
	 * layer.
	 */
	public function Touch()
	{
		if(isset($this->_nativeSid))
		{
			$stmt = $this->_db->prepare("UPDATE `sessions` SET `activity` = NOW() WHERE `session_id` = :sid");
			$stmt->execute(array(':sid' => $this->_nativeSid));
		}
	}

	/**
	 * Whether the user is logged in
	 *
	 * @return bool
	 */
	public function LoggedIn()
	{
		return $this->_loggedIn;
	}

	/**
	 * Returns the user model of the current user, null if there's no logged in
	 * user.
	 *
	 * @return mixed
	 */
	public function GetUser()
	{
		if(isset($this->_loggedIn) && ($this->_loggedIn == true))
			return $this->_user;
		else
			return null;
	}

	/**
	 * Logs in the user represented by $username, verifying the provided password.
	 * Nothing fancy, business as usual.
	 *
	 * @param strin $username
	 * @param string $password
	 *
	 * @return bool
	 */
	public function Login($username, $password)
	{
		$obj = new User();
		$user = $obj->GetAll(array(array('username', '=', $username)));
		if(count($user) > 0)
		{
			$this->_user = $user[0];

			$pwd = sha1($this->_user->salt . sha1($password));
			if($pwd == $this->_user->pwd)
			{
				$this->_loggedIn = true;

				$sql = "UPDATE `sessions`
						SET `logged_in` = 1,
							`user_id` = :user_id
						WHERE `session_id` = :sid";
				$stmt = $this->_db->prepare($sql);
				$stmt->bindValue(':user_id', $this->_user->id, PDO::PARAM_INT);
				$stmt->bindValue(':sid', $this->_nativeSid, PDO::PARAM_INT);
				$stmt->execute();

				return true;
			}
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Logs out the user. Simple stuff.
	 *
	 * @return bool
	 */
	public function LogOut()
	{
		if(isset($this->_loggedIn) && ($this->_loggedIn == true))
		{
			$sql = "UPDATE `sessions`
					SET `logged_in` = 0,
						`user_id` = 0
					WHERE `session_id` = :sid";
			$stmt = $this->_db->prepare($sql);
			$stmt->bindValue(':sid', $this->_nativeSid, PDO::PARAM_INT);
			$stmt->execute();

			$this->_loggedIn = false;
			$this->_user = null;

			return true;
		}
		else
			return false;
	}

	/**
	 * Getter for session variables. Returns the stored value or false if
	 * there's no variable stored with the requested name.
	 *
	 * @param string $var
	 *
	 * @return mixed
	 */
	public function __get($var)
	{
		$sql = "SELECT `var_value`
				FROM `session_variables`
				WHERE `session_id` = :sid
					AND `var_name` = :var";
		$stmt = $this->_db->prepare($sql);
		$stmt->bindValue(':sid', $this->_nativeSid, PDO::PARAM_INT);
		$stmt->bindValue(':var', $var);
		$stmt->execute();
		$val = $stmt->fetchColumn();

		if($val !== false)
			return unserialize($stmt->fetchColumn());
		else
			return false;
	}

	/**
	 * Setter for session variables. Just a plain old insert statement.
	 *
	 * @param string $var
	 * @param mixed $val
	 */
	public function __set($var, $val)
	{
		$val = serialize($val);

		$sql = "INSERT INTO `session_variables`
				SET `session_id` = :sid,
					`var_name` = :var,
					`var_value` = :val";
		$stmt = $this->_db->prepare($sql);
		$stmt->bindValue(':sid', $this->_nativeSid, PDO::PARAM_INT);
		$stmt->bindValue(':var', $var);
		$stmt->bindValue(':val', $val);
		$stmt->execute();
	}

	/**
	 * Emulates the session reading. Actually, it controls whether the session
	 * is valid and if there's a logged in user. Always returns an empty string
	 * (it has to, as per the official documentation - don't ask me why).
	 *
	 * @param string $id
	 *
	 * @return string
	 */
	public function Read($id)
	{
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$this->_phpSid = $id;
		$sql = "SELECT	`session_id`,
						`logged_in`,
						`user_id`
				FROM `sessions`
				WHERE `php_sid` = :php_sid";
		$stmt = $this->_db->prepare($sql);
		$stmt->execute(array(':php_sid' => $this->_phpSid));
		if(($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false)
		{
			$this->_nativeSid = $row['session_id'];
			if($row['logged_in'] == 1)
			{
				$this->_loggedIn = true;
				$this->_user = new User($row['user_id']);
			}
			else
				$this->_loggedIn = false;
		}
		else
		{
			$this->_loggedIn = false;
			$this->_user = null;

			$sql = "INSERT INTO `sessions`
					SET `php_sid` = :php_sid,
						`logged_in` = 0,
						`user_id` = 0,
						`created` = NOW(),
						`user_agent` = :user_agent";
			$stmt = $this->_db->prepare($sql);
			$stmt->execute(array(
				':php_sid' => $this->_phpSid,
				':user_agent' => $userAgent
			));

			$sql = "SELECT `session_id`
					FROM `sessions`
					WHERE `php_sid` = :php_sid";
			$stmt = $this->_db->prepare($sql);
			$stmt->execute(array(':php_sid' => $this->_phpSid));

			$this->_nativeSid = $stmt->fetchColumn();
		}

		return "";
	}

	/**
	 * Kills the session, effectively deleting the session from the database.
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function Destroy($id)
	{
		$sql = "DELETE `sessions`, `session_variables`
				FROM `sessions`
				LEFT JOIN `session_variables` ON (`session_variables`.`session_id` = `sessions`.`session_id`)
				WHERE `php_sid` = :php_sid";
		$stmt = $this->_db->prepare($sql);
		return $stmt->execute(array(':php_sid' => $this->_phpSid));
	}

	/**
	 * Nothing happens below this point. The following methods are nothing more
	 * than placeholders, just for compliance with the overriding of PHP
	 * default session handlers.
	 *
	 * Maybe someone finds use for them. I didn't.
	 */

	public function Open($savePath, $sessionName)
	{
		return true;
	}

	public function Write($id, $sessionData)
	{
		return true;
	}

	public function Close()
	{
		return true;
	}

	private function Gc($maxLifeTime)
	{
		return true;
	}
}
?>