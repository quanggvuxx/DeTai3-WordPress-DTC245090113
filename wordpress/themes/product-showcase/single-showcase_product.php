<?php get_header(); ?>
<main class="container"><article class="entry">
  <?php while(have_posts()): the_post(); ?>
    <p class="eyebrow">Thông tin sản phẩm</p>
    <h1><?php the_title(); ?></h1>
    <p class="price"><?php echo esc_html(get_post_meta(get_the_ID(), 'showcase_price', true)); ?></p>
    <?php if (has_post_thumbnail()) { the_post_thumbnail('large'); } ?>
    <div class="entry-content"><?php the_content(); ?></div>
    <p><a class="btn btn-outline" href="<?php echo esc_url(home_url('/#san-pham')); ?>">← Xem các sản phẩm khác</a></p>
  <?php endwhile; ?>
</article></main>
<?php get_footer(); ?>
