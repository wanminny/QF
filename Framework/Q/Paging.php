<?php
/**
 * 分页基类
 *
 * example：
 * <pre>
 * $paging = new Q_Paging();
 * $paging->setTotal(10)->setCursor(1);
 * </pre>
 *
 * @name Q_Paging
 * @version 2.0 (2016-03-28 下午08:57:00)
 * @package Q.Paging
 * @author parasol.zhang i@zhanglirong.cn
 * @since 2.0
 */

class Q_Paging {
	
	/**
	 * 总分页数
	 *
	 * @var Integer
	 */
	private $total;
	
	/**
	 * 每页记录数
	 *
	 * @var Integer
	 */
	private $size;
	
	/**
	 * 当前页
	 * @var Integer
	 */
	protected $currentPage = 1;
	
	/**
	 * 光标
	 *
	 * @var Integer
	 */
	private $cursor = 0;
	
	/**
	 * 当前页
	 * @return Integer
	 */
	public function getCurrent() {
		return $this->currentPage;
	}
	
	/**
	 * 设置当前页
	 * @param Integer $pageNo
	 * @return Q_Paging
	 */
	public function setCurrent( $pageNo ) {
		$cur = (int) intval($pageNo);
		if ($cur <= 0) {
			$cur = 1;
		}
		$this->currentPage = $cur;
		return $this;
	}
	
	/**
	 * 下一页
	 * @return Integer
	 */
	public function getNext() {
		$pageNum = $this->getPageNum();
		$current = $this->getCurrent();
		return $current < $pageNum ? ($current + 1) : $pageNum;
	}
	
	/**
	 * 上一页
	 * @return Integer
	 */
	public function getPrev() {
		$current = $this->getCurrent();
		return $current > 1 ? ($current - 1) : 1;
	}
	
	/**
	 * 取得记录开始的偏移量
	 * @return Integer
	 */
	public function getOffset() {
		$offset = $this->getSize() * abs($this->getCurrent() - 1);
		if ($offset >= $this->getTotal()) {
			$offset = 0;
			if ($this->getTotal() > 0 && $this->getTotal() > $this->getSize()) {
				$offset = $this->getSize() * abs($this->getPageNum() - 1);
			}
		}
		return (int) abs($offset);
	}
	
	/**
	 * 设置总记录数
	 *
	 * @param Integer $total
	 * @return Q_Paging
	 */
	public function setTotal( $total ) {
		$this->total = (int) intval($total);
		return $this;
	}
	/**
	 * 获取总数
	 * @return Integer
	 */
	public function getTotal() {
		return (int) intval($this->total);
	}
	
	/**
	 * 获取每页显示数
	 * @return Integer
	 */
	public function getSize() {
		return (int) intval($this->size);
	}
	
	/**
	 * 设置每页记录数
	 *
	 * @param Integer $size
	 * @return Q_Paging
	 */
	public function setSize( $size ) {
		if ($size > 0) {
			$this->size = (int) intval($size);
		}
		return $this;
	}
	
	/**
	 * 获取起始数
	 * @return Integer
	 **/
	public function getStarting() {
		return $this->getOffset();
	}
	
	/**
	 * 获取终点数
	 * @return Integer
	 **/
	public function getEnding() {
		return $this->getOffset() + $this->getSize();
	}
	
	/**
	 * 光标
	 *
	 * @return Integer
	 */
	public function getCursor() {
		return $this->cursor;
	}
	
	/**
	 * 设置光标
	 *
	 * @param Integer $cursor
	 * @return Q_Paging
	 */
	public function setCursor( $cursor ) {
		$this->cursor = intval($cursor);
		return $this;
	}
	
	/**
	 * 取得总分页数
	 *
	 * @return Integer
	 */
	public function getPageNum() {
		if ($this->getSize() == 0) {
			return 0;
		}
		return ceil($this->getTotal() / $this->getSize());
	}
}