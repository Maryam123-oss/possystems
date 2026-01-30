    <footer class="footer">
        <div class="container footer-grid">
            <div>
                <h4>POS Systems</h4>
                <p><?= $footer_description ?? 'حلول نقاط بيع متكاملة للأجهزة والبرمجيات والدعم الفني.'; ?></p>
            </div>
            <div>
                <h4>روابط</h4>
                <a href="index.php">الرئيسية</a>
                <a href="services.php">الخدمات</a>
                <a href="products.php">المنتجات</a>
                <a href="news.php">الأخبار</a>
                <a href="about.php">عن النظام</a>
            </div>
            <div>
                <h4>تواصل</h4>
                <p>هاتف: <?= $about['phone']; ?></p>
                <p>البريد: <?= $about['email']; ?></p>
                <p>العنوان: <?= $about['address']; ?></p>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>جميع الحقوق محفوظة © <?= date('Y'); ?> POS Systems</span>
        </div>
    </footer>
    <script src="assets/js/main.js"></script>
</body>
</html>


