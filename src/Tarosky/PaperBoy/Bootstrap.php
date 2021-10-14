<?php

namespace Tarosky\PaperBoy;


use Tarosky\PaperBoy\Controller\RewriteController;
use Tarosky\PaperBoy\Delivery\Line;
use Tarosky\PaperBoy\Delivery\SmartNews;
use Tarosky\PaperBoy\Pattern\Singleton;

/**
 * Bootstrap file for the plugin.
 */
class Bootstrap extends Singleton {

	/**
	 * @inheritDoc
	 */
	protected function init() {
		// Register controller.
		RewriteController::get_instance();

		// Register deliveries.
		SmartNews::get_instance();
		Line::get_instance();
	}
}
