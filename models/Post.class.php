<?php
class Post extends Formen
{
	protected $_idField = 'post_id';
	protected $_table = 'posts';

	protected $_relationMap = array(
		'post_id' => array('var' => 'id', 'type' => PDO::PARAM_INT, 'unsafe' => true),
		'posted' => array('var' => 'posted'),
		'status' => array('var' => 'status'),
		'user_id' => array('var' => 'userId', 'unsafe' => true),
		'title' => array('var' => 'title'),
		'slug' => array('var' => 'slug'),
		'content' => array('var' => 'content')
	);

	protected $_modelRelations = array(
		'user' => array(
			'type' => 'N:1',
			'relationField' => 'userId',
			'class' => 'User'
		),
		'comments' => array(
			'type' => '1:N',
			'relationField' => 'postId',
			'class' => 'Comment'
		),
		'categories' => array(
			'type' => 'N:M',
			'relationField' => 'id',
			'class' => 'Category',
			'auxTable' => 'AUX_posts_categories',
			'auxMyField' => 'posts_post_id',
			'auxRelationField' => 'categories_category_id'
		)
	);

	public function onBeforeSave()
	{
		if(!isset($this->_id))
			$this->userId = App::$WebUser->GetUser()->id;
	}
}
?>