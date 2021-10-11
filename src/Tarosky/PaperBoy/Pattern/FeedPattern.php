<?php

namespace Tarosky\PaperBoy\Pattern;


/**
 * Feed pattern.
 *
 * @package paperboy
 */
abstract class FeedPattern extends Singleton {

	/**
	 * Register hooks.
	 */
	protected function init() {
		add_action( 'pre_get_posts', [ $this, 'prepare_query' ] );
	}

	/**
	 * Hijack query.
	 *
	 * @param \WP_Query $wp_query Query object.
	 */
	public function prepare_query( &$wp_query ) {
		if ( ! $wp_query->is_main_query() || is_admin() ) {
			// Do nothing.
			return;
		}
		$deliver_to = $wp_query->get( 'deliver_to' );
		if ( ! $deliver_to || $this->slug() !== $deliver_to ) {
			// This is not my feed.
			return;
		}
		// This is my feed, register hooks.
		$this->register_hooks();
		// Remove generator if false.
		add_filter( 'get_the_generator_' . $this->feed_type(), [ $this, 'remove_generator' ] );
		// Modify query vars.
		$this->modify_query_vars( $wp_query );
	}

	/**
	 * Register hooks for this feed.
	 */
	protected function register_hooks() {
		// Register hooks.
	}

	/**
	 * Modify query vars.
	 *
	 * @param \WP_Query $wp_query
	 */
	protected function modify_query_vars( &$wp_query ) {
		$wp_query->set( 'feed', $this->feed_type() );
		$wp_query->set( 'posts_per_rss', $this->per_page() );
		$wp_query->set( 'posts_per_page', $this->per_page() );
	}

	/**
	 * Does this feed has pagination?
	 *
	 * @return bool
	 */
	protected function pagination() {
		return true;
	}

	/**
	 * Per page of feed.
	 *
	 * @return int
	 */
	protected function per_page() {
		return apply_filters( 'paperboy_posts_per_feed', (int) get_option( 'posts_per_rss', 10 ), $this->slug(), get_called_class() );
	}

	/**
	 * Slug name.
	 *
	 * @return string
	 */
	abstract protected function slug();

	/**
	 * Thumbnail size.
	 *
	 * @return string|int[]
	 */
	protected function thumbnail_size() {
		return apply_filters( 'paperboy_thumbnail_size', 'post-thumbnail', $this->slug(), get_called_class() );
	}

	/**
	 * If generator is disallowed.
	 *
	 * @return bool
	 */
	protected function disallow_generator() {
		return (bool) apply_filters( 'paperboy_disallow_generator', false, $this->slug(), get_called_class() );
	}

	/**
	 * Feed pattern.
	 *
	 * @return string
	 */
	protected function feed_type() {
		return 'rss2';
	}

	/**
	 * Convert feed content.
	 *
	 * @return string
	 */
	protected function convert_feed_content( $content ) {
		return $content;
	}

	/**
	 * Get related articles.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array{ title: string, url:string, media:string }
	 */
	public function get_related_links( $post = null ) {
		return apply_filters( 'paperboy_related_articles', [], get_post( $post ), $this->slug(), get_called_class() );
	}

	/**
	 * Remove generator.
	 *
	 * @param string $generator Generator section.
	 * @return string
	 */
	public function remove_generator( $generator ) {
		if ( $this->disallow_generator() ) {
			$generator = '';
		}
		return $generator;
	}

	/**
	 * Get last updated in feed.
	 *
	 * @return string
	 */
	public function get_latest_gmt_date_in_feed() {
		global $wp_query;
		if ( ! $wp_query->posts ) {
			$latest_date = current_time( 'mysql', true );
		} else {
			$latest_date= '';
			foreach ( $wp_query->posts as $post ) {
				if ( ! $latest_date || $latest_date < $post->post_date_gmt ) {
					$latest_date = $post->post_date_gmt;
				}
			}
		}
		return apply_filters( 'paperboy_last_updated_in_feed', $latest_date, $this->slug(), get_called_class() );
	}

	/**
	 * Get TTL.
	 *
	 * @return int
	 */
	protected function default_ttl() {
		return (int) apply_filters( 'paperboy_default_ttl', 5, $this->slug(), get_called_class() );
	}

	/**
	 * Render line.
	 *
	 * @param string|string[] $lines    Lines to render.
	 * @param int             $tab_stop Tab stops to append before.
	 * @param string          $before   String to prepend.
	 * @param string          $after    String to append.
	 */
	public function line( $lines, $tab_stop = 0, $before = '', $after = "\n" ) {
		$lines = (array) $lines;
		if ( empty( $lines ) ) {
			return;
		}
		echo $before;
		$glue = "\n";
		for ( $i = 0; $i < $tab_stop; $i++ ) {
			$glue .= "\t";
		}
		echo implode( $glue, $lines );
		echo $after;
	}

	/**
	 * Get advertisement.
	 *
	 * @return string
	 */
	protected function get_ads() {
		return apply_filters( 'paperboy_rss_advertisement', '', $this->slug(), get_called_class() );
	}

	/**
	 * Get analytics code.
	 *
	 * @return string
	 */
	protected function get_analytics() {
		return apply_filters( 'paperboy_rss_analytics', '', $this->slug(), get_called_class() );
	}
}
