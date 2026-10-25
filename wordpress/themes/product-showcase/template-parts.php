<?php
/** Reusable product card. */
if (!defined('ABSPATH')) { exit; }
function showcase_render_product_card($post_id) {
    $title = get_the_title($post_id);
    $description = get_the_excerpt($post_id);
    $price = get_post_meta($post_id, 'showcase_price', true);
    $emoji = get_post_meta($post_id, 'showcase_icon', true) ?: '📦';
    $url = get_permalink($post_id);
    ?>
    <article class="product-card">
      <a class="product-art" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($title); ?>">
        <?php if (has_post_thumbnail($post_id)) { echo get_the_post_thumbnail($post_id, 'medium_large'); }
        else { echo '<span>' . esc_html($emoji) . '</span>'; } ?>
      </a>
      <div class="product-content">
        <h3><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a></h3>
        <p><?php echo esc_html(wp_trim_words($description, 15)); ?></p>
        <div class="price"><?php echo esc_html($price ?: 'Liên hệ'); ?></div>
        <p style="margin-top:14px"><a class="small-link" href="<?php echo esc_url($url); ?>">Xem chi tiết →</a></p>
      </div>
    </article>
    <?php
}
