<?php

namespace Tarosky\PaperBoy\Delivery;


use Tarosky\PaperBoy\Pattern\PrivateFeedPattern;

/**
 * LINE news
 *
 * @package paperboy
 */
class Line extends PrivateFeedPattern {

	/**
	 * @inheritDoc
	 */
	protected function slug() {
		return 'line';
	}

	/**
	 * @inheritDoc
	 */
	protected function feed_pattern_name() {
		return 'line';
	}

	/**
	 * @inheritDoc
	 */
	protected function render_head() {
		$this->line( '<rss version="2.0" xmlns:oa="http://news.line.me/rss/1.0/oa">' );
		$this->line( '<channel>', 1 );
		$this->line( [
			sprintf( '<title><![CDATA[ %s ]]></title>', $this->rss_title() ),
			sprintf( '<link>%s</link>', esc_url( $this->rss_bloginfo( 'url' ) ) ),
			sprintf( '<description><![CDATA[ %s ]]></description>', $this->rss_bloginfo( 'description' ) ),
			sprintf( '<language>%s</language>', $this->lang_code() )
		], 2 );
	}

	/**
	 * @inheritDoc
	 */
	protected function render_footer() {
		$this->line( '</channel>', 1 );
		$this->line( '</rss>' );
	}

	/**
	 * @inheritDoc
	 */
	function render_item() {
		if ( has_post_thumbnail() ) {
			$attachment = wp_get_attachment_image_url( get_post_thumbnail_id(), $this->thumbnail_size() );
			$img_mime   = get_post_mime_type( get_post_thumbnail_id() );
		} else {
			$attachment = $this->get_default_thumbnail();
			$img_mime   = '';
			if ( $attachment ) {
				$file = explode( '.', basename( $attachment ) );
				$ext  = strtolower( $file[ count( $file ) - 1 ] );
				if ( 'jpg' === $ext ) {
					$ext = 'jpeg';
				}
				$img_mime ='image/' . $ext;
			}
		}
		?>
		<item>
			<guid><?php the_permalink_rss() ?></guid>
			<title><![CDATA[ <?php the_title_rss(); ?> ]]></title>
			<link><?php the_permalink_rss() ?></link>
			<description><![CDATA[
				<?php the_content_feed( $this->slug() ); ?>
			]]></description>
			<?php if ( $attachment && $img_mime ) : ?>
				<enclosure url="<?php echo esc_url( $attachment ); ?>" type="<?php esc_attr( $img_mime ); ?>" />
			<?php endif; ?>
			<pubDate><?php the_time( \DateTime::RFC822 ); ?></pubDate>
			<oa:lastPubDate><?php the_modified_date( \DateTimeImmutable::RFC822 ); ?></oa:lastPubDate>
			<oa:pubStatus><?php echo esc_html( $this->get_news_status() ); ?></oa:pubStatus>
			<oa:category><?php echo esc_html( $this->get_news_category() ); ?></oa:category>
			<?php
			$related = $this->get_related_links();
			if ( ! empty( $related ) ) {
				foreach ( $related as $rel ) {
					?>
					<oa:reflink>
						<oa:refTitle><![CDATA[ <?php echo esc_xml( $rel['title'] ); ?> ]]> </oa:refTitle>
						<oa:refUrl><?php echo esc_url( $rel['url'] ); ?></oa:refUrl>
					</oa:reflink>
					<?php
				}
			}
			?>
			<?php $this->after_item(); ?>
		</item>
		<?php
	}

	/**
	 * Get LINE news category.
	 *
	 * @return int
	 */
	protected function get_news_category() {
		return apply_filters( 'paperboy_line_news_category', 3, get_post() );
	}

	/**
	 * Get LINE news status.
	 *
	 * @return int
	 */
	protected function get_news_status() {
		return apply_filters( 'paperboy_line_news_status', ( 'publish' !== get_post_status() ? 0 : 2 ), get_post() );
	}
}
