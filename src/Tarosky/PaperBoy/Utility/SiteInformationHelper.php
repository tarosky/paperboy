<?php

namespace Tarosky\PaperBoy\Utility;

/**
 * Site information for feed.
 *
 * @package paperboy
 */
trait SiteInformationHelper {


	/**
	 * Get copy right name.
	 *
	 * @todo Retrieve from option.
	 * @return string
	 */
	public function get_copy_right() {
		return '&copy; 2021 WordPress';
	}

	/**
	 * Get site icon URL.
	 *
	 * @todo Get url from site icon.
	 * @return string
	 */
	public function get_site_icon() {
		return 'https://example.com';
	}
}
