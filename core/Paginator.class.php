<?php

/**
 * Paginated items can be fetched with the $paginator->paginatedItems
 * attribute, the resulting buttons with $paginator->buttons and the status
 * fields with $paginator->status.
 *
 * Each button comes with two values: on key 'page' is the page number; on key
 * 'active', a flag whether the button is active or not.
 *
 * Status comes with three values: on key 'start' the first item of the current
 * page, on key 'end' the last item of the current page and on key 'count' the
 * number of total items.
 *
 * @author Marco Amado <mjamado@dreamsincode.com>
 * @since 2011-11-20 [last review: 2011-11-20]
 * @license Creative Commons 2.5 Portugal BY-NC-SA
 * @link http://www.dreamsincode.com/
 */
class Paginator
{
	private $_numItems;
	private $_curPage;
	private $_numItemsPage;

	/**
	 * Constructor; $items is mandatory, the rest is optional
	 *
	 * @param array $items
	 * @param int $curPage
	 * @param int $numItemsPage
	 * @param int $numLinks
	 */
	public function __construct($numItems, $curPage = 1, $numItemsPage = 20)
	{
		$this->_numItems = $numItems;
		$this->_curPage = $curPage;
		$this->_numItemsPage = $numItemsPage;
	}

	/**
	 * Getter; can be used to get attributes paginatedItems, buttons and status
	 *
	 * @param string $var
	 *
	 * @return array
	 */
	public function __get($var)
	{
		$lastPage = ceil($this->_numItems / $this->_numItemsPage);

		switch($var)
		{
			case 'buttons':
				$btns = array();

				for($i = 1; $i <= $lastPage; $i++)
					$btns[] = array('page' => $i, 'active' => ($this->_curPage != $i), 'alt' => $i);

				return $btns;
			break;
			case 'status':
				return array('start' => $this->_numItemsPage * ($this->_curPage - 1) + 1, 'end' => min($this->_numItems, $this->_numItemsPage * $this->_curPage), 'count' => $this->_numItems);
			break;
			case 'limitClause':
				return " LIMIT " . (($this->_curPage - 1) * $this->_numItemsPage) . ", " . $this->_numItemsPage;
			break;
			case 'totalCount':
				return $this->_numItems;
			break;
		}
	}
}
?>