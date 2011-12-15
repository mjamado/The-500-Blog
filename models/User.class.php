<?php
class User extends Formen
{
	protected $_idField = 'user_id';
	protected $_table = 'users';

	protected $_relationMap = array(
		'user_id' => array('var' => 'id', 'type' => PDO::PARAM_INT, 'unsafe' => true),
		'registration' => array('var' => 'registration', 'unsafe' => true),
		'last_login' => array('var' => 'lastLogin', 'unsafe' => true),
		'username' => array('var' => 'username'),
		'email' => array('var' => 'email'),
		'pwd' => array('var' => 'pwd', 'unsafe' => true),
		'salt' => array('var' => 'salt', 'unsafe' => true),
		'fullname' => array('var' => 'fullName'),
		'screenname' => array('var' => 'screenName')
	);

	protected $_modelRelations = array(
		'posts' => array(
			'type' => '1:N',
			'relationField' => 'user_id',
			'class' => 'Post'
		)
	);

	public $password;
	public $re_password;

	public function onBeforeSave()
	{
		if(!isset($this->_id))
		{
			$this->registration = date("Y-m-d H:i");
			$this->salt = chr(rand(32, 126)) . chr(rand(32, 126)) . chr(rand(32, 126));
		}

		if(isset($this->password) && isset($this->re_password) && ($this->password == $this->re_password))
			$this->pwd = sha1($this->salt . sha1($this->password));
	}
}
?>