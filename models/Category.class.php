<?php
class Category extends Formen
{
	protected $_idField = 'category_id';
	protected $_table = 'categories';

	protected $_relationMap = array(
		'category_id' => array('var' => 'id', 'type' => PDO::PARAM_INT, 'unsafe' => true),
		'title' => array('var' => 'title'),
		'slug' => array('var' => 'slug')
	);

	protected $_modelRelations = array(
		'posts' => array(
			'type' => 'N:M',
			'relationField' => 'id',
			'class' => 'Post',
			'auxTable' => 'AUX_posts_categories',
			'auxMyField' => 'categories_category_id',
			'auxRelationField' => 'posts_post_id'
		)
	);
}
?>