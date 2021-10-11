<?php

namespace Tarosky\PaperBoy\Controller;


use Tarosky\PaperBoy\Pattern\Singleton;

/**
 * Rewrite rule controller.
 */
class RewriteController extends Singleton {

	/**
	 * @inheritDoc
	 */
	protected function init() {
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
	}

	/**
	 * Add query vars.
	 *
	 * @param string[] $vars Query vars.
	 * @return string[]
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'deliver_to';
		return $vars;
	}
}
