<?php class Paginator {
	private $_numItems;
	private $_curPage;
	private $_numItemsPage;
	public function __construct($numItems, $curPage = 1, $numItemsPage = 20) {
		$this->_numItems = $numItems;
		$this->_curPage = $curPage;
		$this->_numItemsPage = $numItemsPage;
	}
	public function __get($var) {
		$lastPage = ceil($this->_numItems / $this->_numItemsPage);
		switch($var) {
			case 'buttons':
				$btns = array();
				for($i = 1; $i <= $lastPage; $i++) $btns[] = array('page' => $i, 'active' => ($this->_curPage != $i), 'alt' => $i);
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
} ?>