<?php get_header(); require_once get_template_directory() . '/template-parts.php'; ?>
<main>
  <section class="hero"><div class="container hero-grid">
    <div>
      <div class="eyebrow">Bộ sưu tập công nghệ mới</div>
      <h1>Sản phẩm tinh tế.<br>Trải nghiệm khác biệt.</h1>
      <p class="lead">Khám phá thiết bị công nghệ được lựa chọn với thiết kế hiện đại, tính năng hữu ích và trải nghiệm tiện lợi.</p>
      <a class="btn" href="#san-pham">Khám phá sản phẩm →</a>
    </div>
    <div class="hero-visual" aria-label="Sản phẩm công nghệ minh họa"><span>🎧</span></div>
  </div></section>
  <section id="san-pham" class="section"><div class="container">
    <div class="section-heading"><div><span class="eyebrow">Sản phẩm nổi bật</span><h2>Khám phá bộ sưu tập</h2></div>
      <p>Thông tin sản phẩm được quản lý trực tiếp trong WordPress. Có thể thêm, sửa và xóa tại trang quản trị.</p>
    </div>
    <?php $products = new WP_Query(['post_type'=>'showcase_product','posts_per_page'=>12,'post_status'=>'publish']); ?>
    <?php if ($products->have_posts()): ?><div class="grid">
      <?php while($products->have_posts()): $products->the_post(); showcase_render_product_card(get_the_ID()); endwhile; ?>
    </div><?php wp_reset_postdata(); ?>
    <?php else: ?><p>Chưa có sản phẩm. Vào trang quản trị → Sản phẩm → Thêm mới hoặc kích hoạt giao diện để tạo dữ liệu demo.</p><?php endif; ?>
  </div></section>
  <section class="section" id="gioi-thieu"><div class="container"><div class="info-strip">
    <div><span class="eyebrow">Về NOVASTORE</span><h2>Giới thiệu sản phẩm rõ ràng, dễ quản lý</h2>
      <p>Website minh họa phục vụ môn Triển khai và Quản trị Hệ thống Phần mềm, không có chức năng thanh toán.</p></div>
    <a class="btn btn-outline" href="<?php echo esc_url(admin_url()); ?>">Đăng nhập quản trị</a>
  </div></div></section>
</main>
<?php get_footer(); ?>
