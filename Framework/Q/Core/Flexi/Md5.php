<?php
/**
 * Uses CRC32 to hash a value into a 32bit binary string data address space.
 *
 * @author Paul Annesley
 * @package Flexihash
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Q_Core_Flexi_Md5 implements Q_Core_Flexi_Hasher {
	
	/* (non-phpdoc)
	 * @see Flexihash_Hasher::hash()
	 */
	public function hash($string) {
		return substr(md5($string), 0, 8); // 8 hexits = 32bit
	// 4 bytes of binary md5 data could also be used, but
	// performance seems to be the same.
	}

}