<?php
/**
 * Uses CRC32 to hash a value into a signed 32bit int address space.
 * Under 32bit PHP this (safely) overflows into negatives ints.
 *
 * @author Paul Annesley
 * @package Flexihash
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Q_Core_Flexi_Crc32Hasher implements Q_Core_Flexi_Hasher {
	
	/* (non-phpdoc)
	 * @see Flexihash_Hasher::hash()
	 */
	public function hash($string) {
		return crc32($string);
	}

}
