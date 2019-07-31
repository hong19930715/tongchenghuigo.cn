<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://1024ok.cn/ for more details.
 */

class UnigroupTable extends We7Table {

	protected $tableName = 'uni_group';
	protected $primaryKey = 'id';

	
	public function uniaccounts() {
		return $this->belongsMany('account', 'uniacid', 'id', 'uni_account_group', 'uniacid', 'groupid');
	}


}