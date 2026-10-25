<?php get_header(); ?>
<main class="container"><article class="entry">
<?php while(have_posts()):the_post(); ?><h1><?php the_title(); ?></h1><div class="entry-content"><?php the_content(); ?></div><?php endwhile; ?>
</article></main>
<?php get_footer(); ?>
