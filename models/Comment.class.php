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
		/**
		 * This is crap. It's witholding our users from posting rich text
		 * comments, and that's plain bad. However, it's the simplest way of
		 * cleaning potential XSS attacks (not all, though).
		 *
		 * A much better way is to use Edward Yang's awsome HTML Purifier.
		 * Why didn't I use it? Well, because it's written in PHP, hence
		 * counting towards the line count - and it's a big ass library...
		 *
		 * Anyway, for educational purposes, this simple control suffices.
		 */
		$this->content = htmlentities($this->content, ENT_QUOTES, 'UTF-8');

		if(!isset($this->_id))
			$this->posted = date_format(date_create(), "Y-m-d H:i:s");
	}
}
?>