<?php
/** Custom WordPress product catalog for the assignment. */
if (!defined('ABSPATH')) { exit; }

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('showcase-k23', get_stylesheet_uri(), [], '1.0.0');
});

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption']);
});

add_action('init', function () {
    register_post_type('showcase_product', [
        'labels' => [
            'name' => 'Sản phẩm',
            'singular_name' => 'Sản phẩm',
            'add_new_item' => 'Thêm sản phẩm mới',
            'edit_item' => 'Chỉnh sửa sản phẩm',
        ],
        'public' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-products',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'san-pham'],
    ]);
});

add_action('add_meta_boxes', function () {
    add_meta_box('showcase_price', 'Thông tin quảng bá', function ($post) {
        wp_nonce_field('showcase_save_price', 'showcase_price_nonce');
        $price = get_post_meta($post->ID, 'showcase_price', true);
        $icon = get_post_meta($post->ID, 'showcase_icon', true);
        echo '<p><label for="showcase_price">Giá tham khảo (VD: 1.990.000 ₫)</label></p>';
        echo '<input id="showcase_price" name="showcase_price" class="widefat" value="' . esc_attr($price) . '">';
        echo '<p><label for="showcase_icon">Emoji minh họa (VD: 🎧)</label></p>';
        echo '<input id="showcase_icon" name="showcase_icon" class="widefat" value="' . esc_attr($icon) . '">';
    }, 'showcase_product');
});

add_action('save_post_showcase_product', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!isset($_POST['showcase_price_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['showcase_price_nonce'])), 'showcase_save_price')) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }
    foreach (['showcase_price', 'showcase_icon'] as $key) {
        if (isset($_POST[$key])) {
            update_post_meta($post_id, $key, sanitize_text_field(wp_unslash($_POST[$key])));
        }
    }
});

// Seed demonstration data only on first activation of this custom theme.
add_action('after_switch_theme', function () {
    flush_rewrite_rules();
    if (get_option('showcase_seeded_v1')) { return; }
    $products = [
        ['Tai nghe AirWave', 'Âm thanh trong trẻo, thiết kế tối giản, kết nối nhanh.', '990.000 ₫', '🎧'],
        ['Đồng hồ TimeOne', 'Gọn nhẹ, tinh tế, đồng hành cùng nhịp sống năng động.', '1.790.000 ₫', '⌚'],
        ['Bàn phím KeyNova', 'Trải nghiệm làm việc êm ái, bố cục tiện lợi.', '1.290.000 ₫', '⌨️'],
        ['Máy ảnh PocketView', 'Lưu giữ mọi khoảnh khắc với thiết kế nhỏ gọn.', '3.990.000 ₫', '📷'],
        ['Loa MiniBeat', 'Âm nhạc linh hoạt cho mọi không gian.', '690.000 ₫', '🔊'],
        ['Đèn học LumiDesk', 'Ánh sáng dịu và phong cách hiện đại.', '490.000 ₫', '💡'],
    ];
    foreach ($products as [$title, $description, $price, $emoji]) {
        $id = wp_insert_post([
            'post_type' => 'showcase_product',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_excerpt' => $description,
            'post_content' => '<p>' . esc_html($description) . '</p><p>Đây là sản phẩm minh họa phục vụ bài thực hành triển khai hệ thống.</p>',
        ]);
        if (!is_wp_error($id)) {
            update_post_meta($id, 'showcase_price', $price);
            update_post_meta($id, 'showcase_icon', $emoji);
        }
    }
    update_option('showcase_seeded_v1', true);
});
