<?php
class Comment extends Formen
{
	protected $_idField = 'comment_id';
	protected $_table = 'comments';

	protected $_relationMap = array(
		'comment_id' => array('var' => 'id', 'type' => PDO::PARAM_INT, 'unsafe' => true),
		'posted' => array('var' => 'posted', 'unsafe' => true),
		'post_id' => array('var' => 'postId', 'unsafe' => true),
		'status' => array('var' => 'status', 'unsafe' => true),
		'screenname' => array('var' => 'screenName'),
		'email' => array('var' => 'email'),
		'url' => array('var' => 'url'),
		'content' => array('var' => 'content')
	);

	protected $_modelRelations = array(
		'post' => array(
			'type' => 'N:1',
			'relationField' => 'postId',
			'class' => 'Post'
		)
	);

	public function onBeforeSave()
	{
		$this->content = htmlentities($this->content, ENT_QUOTES, 'UTF-8');

		if(!isset($this->_id))
			$this->posted = date_format(date_create(), "Y-m-d H:i:s");
	}
}
?>