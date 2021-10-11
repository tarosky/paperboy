<?php

namespace Tarosky\PaperBoy\Utility;

/**
 * Yahoo Media helper.
 *
 * @package paperboy
 * @see https://www.feedforall.com/mediarss.htm
 */
trait YahooMediaHelper {

	/**
	 * Yahoo media namespace.
	 *
	 * @return string
	 */
	protected function yahoo_media_namespace() {
		return 'xmlns:media="http://search.yahoo.com/mrss/"';
	}
}
