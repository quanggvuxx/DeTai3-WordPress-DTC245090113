<?php if (!defined('ABSPATH')) { exit; } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header"><div class="container head-inner">
  <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">NOVA<span>STORE</span></a>
  <nav aria-label="Điều hướng chính">
    <a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a>
    <a href="<?php echo esc_url(home_url('/#san-pham')); ?>">Sản phẩm</a>
    <a href="<?php echo esc_url(home_url('/#gioi-thieu')); ?>">Giới thiệu</a>
  </nav>
</div></header>
