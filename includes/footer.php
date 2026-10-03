            </div><!-- End flex-grow-1 -->

            <!-- Footer مختبر الجوكر الأمني -->
            <footer class="mt-5 py-4 border-top border-secondary text-center rounded-3 shadow" style="background-color: #0e1424;">
                <div class="container-fluid">
                    <p class="mb-1 text-light fw-bold fs-6">
                        ⚔️ مختبر الجوكر الأمني لاختبار اختراق تطبيقات الويب (Joker Security Lab v2.0)
                    </p>
                    <p class="mb-2 text-info small">
                        منصة تعليمية وتدريبية متكاملة لشرح وتطبيق أشهر ثغرات الويب وكيفية ترقيعها برمجياً
                    </p>
                    <div class="mt-2 py-2 px-4 d-inline-flex align-items-center rounded-pill bg-black border border-warning shadow">
                        <img src="../assets/images/joker_logo.png" alt="الجوكر الفلسطيني" class="rounded-circle border border-warning me-2" style="width: 32px; height: 32px; object-fit: cover;">
                        <span class="text-white-50 me-1">إعداد وتطوير: </span>
                        <strong class="text-warning fs-5">الجوكر الفلسطيني احمد سليم</strong>
                        <span class="text-danger ms-1">🇵🇸</span>
                    </div>
                    <div class="mt-2 small text-light text-opacity-75">
                        جميع الحقوق محفوظة للأغراض التعليمية والأمن الأخلاقي &copy; <?= date('Y') ?>
                    </div>
                </div>
            </footer>
        </main>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JavaScript -->
<script src="../assets/js/main.js"></script>
<?php render_celebration(false); ?>
</body>
</html>
