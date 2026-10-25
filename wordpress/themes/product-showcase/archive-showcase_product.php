<?php get_header(); require_once get_template_directory() . '/template-parts.php'; ?>
<main class="container section"><h1>Danh sách sản phẩm</h1><div class="grid">
<?php while(have_posts()): the_post(); showcase_render_product_card(get_the_ID()); endwhile; ?>
</div></main><?php get_footer(); ?>
