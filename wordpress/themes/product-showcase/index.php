<?php get_header(); ?>
<main class="container"><article class="entry">
  <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <div class="entry-content"><?php the_excerpt(); ?></div>
  <?php endwhile; else: ?><p>Chưa có nội dung.</p><?php endif; ?>
</article></main>
<?php get_footer(); ?>
