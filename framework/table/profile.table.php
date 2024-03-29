<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://1024ok.cn/ for more details.
 */

defined('IN_IA') or exit('Access Denied');

class ProfileTable extends We7Table {
	protected $profileFields = 'profile_fields';

	public function getProfileFields() {
		return $this->query->from($this->profileFields)->getall('field');
	}
}