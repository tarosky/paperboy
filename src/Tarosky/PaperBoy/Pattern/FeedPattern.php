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
		// Add hook.
		add_filter( 'the_content_feed', [ $this, 'convert_feed_content' ], 10, 2 );
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
	public function convert_feed_content( $content, $feed_type ) {
		return apply_filters( 'paperboy_feed_content', $content, $this->slug(), get_called_class() );
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
	 * @param string $context Default is empty. If multiple fields are supported, this works.
	 * @return string
	 */
	protected function get_ads( $context = '' ) {
		return apply_filters( 'paperboy_rss_advertisement', '', $this->slug(), $context, get_called_class() );
	}

	/**
	 * Get analytics code.
	 *
	 * @param string $context Default is empty. If multiple fields are supported, this works.
	 * @return string
	 */
	protected function get_analytics( $context = '' ) {
		return apply_filters( 'paperboy_rss_analytics', '', $this->slug(), $context, get_called_class() );
	}

	/**
	 * Get RSS title.
	 *
	 * @return string
	 */
	protected function rss_title() {
		return apply_filters( 'wp_title_rss', get_wp_title_rss() );
	}

	/**
	 * Get bloginfo.
	 *
	 * @param string $show Property name.
	 * @return string
	 */
	protected function rss_bloginfo( $show ) {
		return apply_filters( 'bloginfo_rss', get_bloginfo_rss( $show ), $show );
	}

	/**
	 * Get language code.
	 *
	 * @return string
	 */
	protected function lang_code() {
		list( $lang ) = explode( '_', get_locale() );
		return $lang;
	}

	/**
	 * Get default thumbnail url
	 *
	 * @return string
	 */
	protected function get_default_thumbnail() {
		return apply_filters( 'paperboy_default_thumbnail_url', '', $this->slug(), get_called_class() );
	}

	/**
	 * Get mime type from url.
	 *
	 * @param string $url URL of file.
	 * @return string
	 */
	protected function get_mime_from_extension( $url ) {
		$file = explode( '.', basename( $url ) );
		$mime = strtolower( $file[ count( $file ) - 1 ] );
		$type = 'image';
		switch ( $mime ) {
			case 'mp4':
			case 'mov':
			case 'wav':
				$type = 'video';
				break;
			case 'jpg':
				$ext = 'jpeg';
				break;
		}
		return sprintf( '%s/%s', $type, $ext );
	}

	/**
	 * Get post status. default is 'active'. 'deleted' stops publishing on Gunosy.
	 *
	 * @return string
	 */
	public function get_status() {
		return apply_filters( 'paperboy_media_status', 'active', $this->slug(), get_post() );
	}

	/**
	 * Do something at the last of item.
	 *
	 * @param string $context Context
	 */
	protected function after_item( $context = '' ) {
		do_action( 'paperboy_after_item', $this->slug(), $context );
	}
}
