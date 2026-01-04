<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-content">
            <div class="footer-info">
                <h3><?php bloginfo('name'); ?></h3>
                <p><?php bloginfo('description'); ?></p>
            </div>
            
            <div class="footer-menu">
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer',
                    'menu_class' => 'footer-nav',
                    'container' => false,
                    'fallback_cb' => false
                ]);
                ?>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
            <p>제작자: 아로스</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
