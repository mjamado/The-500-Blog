<?php
class PostCategory extends Formen
{
	protected $_idField = null;
	protected $_table = 'AUX_posts_categories';

	protected $_relationMap = array(
		'posts_post_id' => array('var' => 'postId', 'type' => PDO::PARAM_INT, 'unsafe' => true),
		'categories_category_id' => array('var' => 'categoryId', 'type' => PDO::PARAM_INT, 'unsafe' => true)
	);

	protected $_modelRelations = array(
		'categories' => array(
			'type' => 'N:1',
			'relationField' => 'categories_category_id',
			'class' => 'Category'
		),
		'posts' => array(
			'type' => 'N:1',
			'relationField' => 'posts_post_id',
			'class' => 'Post'
		)
	);
}
?>
