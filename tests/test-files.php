<?php
/**
 * API_Cache_Pro_File_Checks
 *
 * @package Api_Cache_Pro
 */

/**
 * API_Cache_Pro_File_Checks
 */
class API_Cache_Pro_File_Checks extends WP_UnitTestCase {

	/**
	 * Verify Readme Exists.
	 *
	 * @access public
	 */
	public function test_readme_md_exists() {
		$this->assertFileExists( 'README.md' );
	}

	/**
	 * Verify Uninstall File Exists.
	 *
	 * @access public
	 */
	public function test_uninstall_exists() {
		$this->assertFileExists( 'uninstall.php' );
	}

	/**
	 * Verify API Cache Pro Class File Exists.
	 *
	 * @access public
	 */
	public function test_class_api_cache_pro_exists() {
		$this->assertFileExists( 'class-api-cache-pro.php' );
	}

}
