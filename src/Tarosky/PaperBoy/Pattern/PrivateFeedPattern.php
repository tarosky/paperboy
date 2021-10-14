<?php

namespace Tarosky\PaperBoy\Pattern;

/**
 * Original feed.
 *
 * @package paperboy
 */
abstract class PrivateFeedPattern extends FeedPattern {

	/**
	 * RSS type. rss, rss2, atom, rdf are supported.
	 *
	 * @return string
	 */
	protected function rss_type() {
		return 'rss-http';
	}

	/**
	 * @inheritDoc
	 */
	protected function init() {
		parent::init();
		add_action( 'do_feed_' . $this->feed_pattern_name(), [ $this, 'render_whole_feed' ] );
	}


	/**
	 * Feed pattern name.
	 *
	 * @return string
	 */
	abstract protected function feed_pattern_name();

	/**
	 * Feed type.
	 *
	 * @return string
	 */
	protected function feed_type() {
		return $this->feed_pattern_name();
	}

	/**
	 * Render feed item.
	 *
	 * @return void
	 */
	abstract protected function render_head();

	/**
	 * Render feed footer.
	 *
	 * @return void
	 */
	abstract protected function render_footer();

	/**
	 * Invoked inside loop.
	 *
	 * @return void
	 */
	abstract function render_item();

	/**
	 * Render feed content.
	 */
	public function render_whole_feed() {
		$this->xml_header();
		$this->render_head();
		while( have_posts() ) {
			the_post();
			$this->render_item();
		}
		$this->render_footer();
	}

	/**
	 * Output XML headers.
	 */
	protected function xml_header() {
		header( sprintf( 'Content-Type: %s; charset=%s', feed_content_type( $this->rss_type() ), get_option( 'blog_charset' ) ), true );
		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	}
}
