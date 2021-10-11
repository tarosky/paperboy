<?php

namespace Tarosky\PaperBoy\Utility;

/**
 * Shortcode helper.
 *
 * @package paperboy
 */
trait ShortcodeHelper {

	/**
	 * Invalidate shortcode.
	 *
	 * If you don't need "gallery" for example, run to remove it inside register_hook() method.
	 */
	public function invalidate_shortcode( $shortcode ) {
		remove_shortcode( $shortcode );
		add_shortcode( $shortcode, function(){
			return '';
		} );
	}
}
