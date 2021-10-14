<?php

namespace Tarosky\PaperBoy\Delivery;


use Tarosky\PaperBoy\Pattern\FeedPattern;
use Tarosky\PaperBoy\Utility\SiteInformationHelper;
use Tarosky\PaperBoy\Utility\YahooMediaHelper;

/**
 * SmartNews feed.
 *
 * @package paperboy
 * @see https://publishers.smartnews.com/hc/ja/categories/360001838493
 */
class SmartNews extends FeedPattern {

	use YahooMediaHelper,
		SiteInformationHelper;

	/**
	 * @inheritDoc
	 */
	protected function slug() {
		return 'smartnews';
	}

	/**
	 * @inheritDoc
	 */
	protected function register_hooks() {
		add_action( 'rss2_ns', [ $this, 'add_namespace' ] );
		add_action( 'rss2_item', [ $this, 'add_rss_items' ] );
		add_action( 'rss2_head', [ $this, 'add_rss_head' ] );
	}

	/**
	 * Register namespace.
	 */
	public function add_namespace() {
		$this->line( [
			$this->yahoo_media_namespace(),
			'xmlns:snf="http://www.smartnews.be/snf"'
		], 1 );
	}

	/**
	 * Add RSS2 header.
	 */
	public function add_rss_head() {
		$date = mysql2date( \DateTime::ATOM, $this->get_latest_gmt_date_in_feed() );
		$lines = [ sprintf( '<pubDate>%s</pubDate>', $date ) ];
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
			$lines[] = sprintf( '<snf:logo><url>%s</url></snf:logo>', esc_xml( $media ) );
		}
		$this->line( $lines, 1, "\t" );
	}

	/**
	 * Add RSS2 items.
	 */
	public function add_rss_items() {
		$lines = [];
		if ( has_post_thumbnail() ) {
			$attachment = wp_get_attachment_image_url( get_post_thumbnail_id(), $this->thumbnail_size() );
		} else {
			$attachment = $this->get_default_thumbnail();
		}
		// If thumbnail exists, add thumbnail.
		if ( $attachment ) {
			$lines[]    = sprintf( '<media:thumbnail url="%s" />', esc_url( $attachment ) );
		}
		$related_links = $this->get_related_links();
		if ( $related_links ) {
			foreach ( $related_links as $link ) {
				if ( $link['media'] ) {
					$lines[] = sprintf(
						'<snf:relatedLink title="%s" link="%s" thumbnail="%s" />',
						esc_attr( $link['title'] ),
						esc_url( $link['url'] ),
						esc_url( $link['media'] )
					);
				} else {
					$lines[] = sprintf(
						'<snf:relatedLink title="%s" link="%s" />',
						esc_attr( $link['title'] ),
						esc_url( $link['url'] )
					);
				}
			}
		}
		$ad = $this->get_ads();
		if ( ! empty( $ad ) ) {
			$lines[] = sprintf( "<snf:advertisement>\n\t\t\t%s\n\t\t</snf:advertisement>", $ad );
		}
		// Analytics.
		$analytics = $this->get_analytics();
		if ( ! empty( $analytics ) ) {
			$lines[] = $this->analytics_script( $analytics );
		}
		// Active flag.
		$lines[] = '<media:status>active</media:status>';
		$this->line( $lines, 2 );
	}

	/**
	 * Get ad link for smart news.
	 *
	 * @param string $title      Title.
	 * @param string $url        URL for sponsor.
	 * @param string $advertiser Advertiser name.
	 * @param string $media      Attachment URL.
	 *
	 * @return string
	 */
	public function ad_link( $title, $url, $advertiser, $media = '' ) {
		if ( $media ) {
			return sprintf(
				'<snf:sponsoredLink title="%s" link="%s" advertiser="%s" thumbnail="%s" />',
				esc_attr( $title ),
				esc_url( $url ),
				esc_attr( $advertiser ),
				esc_url( $media )
			);
		} else {
			return sprintf(
				'<snf:sponsoredLink title="%s" link="%s" advertiser="%s" />',
				esc_attr( $title ),
				esc_url( $url ),
				esc_attr( $advertiser )
			);
		}
	}

	/**
	 * Get ad script XML.
	 *
	 * @param string $script Script tag to output.
	 * @return string
	 */
	public function ad_script( $script ) {
		$markup = <<<XML
			<snf:adcontent><![CDATA[
				%s
			]]></snf:adcontent>
XML;
		return sprintf( $markup, $script );
	}

	/**
	 * Get analytics script.
	 *
	 * @param string $script Script tag to render.
	 * @return string
	 */
	public function analytics_script( $script ) {
		$markup = <<<XML
<snf:analytics><![CDATA[
			%s
		]]></snf:analytics>
XML;
		return sprintf( $markup, $script );
	}
}
