<?php

namespace Tarosky\PaperBoy\Delivery;


use Tarosky\PaperBoy\Pattern\FeedPattern;
use Tarosky\PaperBoy\Utility\SiteInformationHelper;
use Tarosky\PaperBoy\Utility\YahooMediaHelper;

/**
 * Gunosy feed.
 *
 * @package paperboy
 * @see https://feed-validator.newspass.jp/document/
 */
class Gunosy extends FeedPattern {

	use YahooMediaHelper,
		SiteInformationHelper;

	/**
	 * @inheritDoc
	 */
	protected function slug() {
		return 'gunosy';
	}

	/**
	 * @inheritDoc
	 */
	protected function register_hooks() {
		add_action( 'rss2_ns', [ $this, 'add_namespace' ] );
		add_action( 'rss2_head', [ $this, 'add_rss_head' ] );
		add_action( 'rss2_item', [ $this, 'add_rss_items' ] );
	}

	/**
	 * Register namespace.
	 */
	public function add_namespace() {
		$this->line( [
			'xmlns:gnf="http://assets.gunosy.com/media/gnf"',
			$this->yahoo_media_namespace(),
		], 1 );
	}

	/**
	 * Add RSS2 header.
	 */
	public function add_rss_head() {
		$date = mysql2date( \DateTime::ATOM, $this->get_latest_gmt_date_in_feed() );
		$lines = [ sprintf( '<lastBuildDate>%s</lastBuildDate>', $date ) ];
		// Copyright.
		$copy = $this->get_copy_right();
		if ( $copy ) {
			$lines[] = sprintf( '<copyright>%s</copyright>', esc_xml( $copy ) );
		}
		// TTL
		$lines[] = sprintf( '<ttl>%d</ttl>', $this->default_ttl() );
		// Media.
		$media = $this->get_site_icon();
		if ( $media ) {
			$lines[] = '<image>';
			$lines[] = sprintf( "\t<title>%s</title>", $this->rss_title() );
			$lines[] = sprintf( "\t<url>%s</url>", get_bloginfo_rss( 'url' ) );
			$lines[] = sprintf( "\t<link>%s</link>", esc_url( $media ) );
			$lines[] = '</image>';
		}
		// Wide image.
		$wide_image = $this->get_wide_image();
		if ( $wide_image ) {
			$lines[] = sprintf( '<gnf:wide_image_link>%s</gnf:wide_image_link>', esc_url( $wide_image ) );
		}
		// Language
		$lines[] = sprintf( '<language>%s</language>', $this->lang_code() );
		$this->line( $lines, 1, "\t" );
	}

	/**
	 * Add RSS2 items.
	 */
	public function add_rss_items() {
		$lines = [];
		if ( has_post_thumbnail() ) {
			$attachment = wp_get_attachment_image_url( get_post_thumbnail_id(), $this->thumbnail_size() );
			$mime       = get_post_mime_type( get_post_thumbnail_id() );
			$caption    = strip_tags( get_the_post_thumbnail_caption() );
		} else {
			$attachment = $this->get_default_thumbnail();
			if ( $attachment ) {
				$caption = '';
				$mime    = $this->get_mime_from_extension( $attachment );
			}
		}
		// If thumbnail exists, add thumbnail.
		if ( $attachment ) {
			$lines[]    = sprintf( '<enclosure url="%s" type="%s" caption="%s" />', esc_url( $attachment ), esc_attr( $mime ), esc_attr( $caption ) );
		}
		// Post modified.
		$lines[] = sprintf( '<gnf:modified>%s</gnf:modified>', get_the_modified_date( \DateTime::RFC822 ) );
		// Category.
		$lines[] = sprintf( '<gnf:category>%s</gnf:category>', esc_xml( $this->get_category() ) );
		// Keywords.
		$keywords = $this->get_keywords();
		if ( ! empty( $keywords ) ) {
			$lines[] = sprintf( '<gnf:keyword>%s</gnf:keyword>', esc_xml( implode( ',', $keywords ) ) );
		}
		// Media status.
		$lines[] = sprintf( '<media:status state="%s" />', esc_attr( $this->get_status() ) );
		// Related links.
		$related_links = $this->get_related_links();
		if ( $related_links ) {
			$count = 0;
			foreach ( $related_links as $link ) {
				if ( 2 < $count ) {
					break;
				}
				if ( $link['media'] ) {
					$lines[] = sprintf(
						'<gnf:relatedLink title="%s" link="%s" thumbnail="%s" />',
						esc_attr( $link['title'] ),
						esc_url( $link['url'] ),
						esc_url( $link['media'] )
					);
				} else {
					$lines[] = sprintf(
						'<gnf:relatedLink title="%s" link="%s" />',
						esc_attr( $link['title'] ),
						esc_url( $link['url'] )
					);
				}
				$count++;
			}
		}
		// Analytics.
		foreach ( [
			'', // News pass
			'_gn', // Gunosy
			'_lc', // LUCRA
			'_st', // AU smart today
		] as $suffix ) {
			$tag = 'gnf:analytics' . $suffix;
			$analytics = $this->get_analytics( $tag );
			if ( ! empty( $analytics ) ) {
				$lines[] = sprintf( "<%1\$s><![CDATA[\n\t\t\t%2\$s\n\t\t]]></%1\$s>", $tag, $analytics );
			}
		}
		// Render.
		$this->line( $lines, 2 );
	}

	/**
	 * Get wide image.
	 *
	 * @return string
	 */
	public function get_wide_image() {
		return apply_filters( 'paperboy_gunosy_wide_image_src', '' );
	}

	/**
	 * Get post status. default is 'active'. 'deleted' stops publishing on Gunosy.
	 *
	 * @return string
	 */
	public function get_status() {
		return apply_filters( 'paperboy_gunosy_media_status', 'active', get_post() );
	}

	/**
	 * Get category.
	 *
	 * @see https://feed-validator.newspass.jp/document/#--7
	 * @return string
	 */
	public function get_category() {
		return apply_filters( 'paperboy_gunosy_category', 'column', get_post() );
	}

	/**
	 * Get category.
	 *
	 * @see https://feed-validator.newspass.jp/document/#--7
	 * @return string[]
	 */
	public function get_keywords() {
		$keywords = [];
		$tags     = get_the_tags();
		if ( $tags && ! is_wp_error( $tags ) ) {
			$keywords = array_map( function( $tag ) {
				return $tag->name;
			}, $tags );
		}
		return apply_filters( 'paperboy_gunosy_keywords', $keywords, get_post() );
	}
}
