// تمرير سلس للأقسام داخل الصفحة الرئيسية
document.addEventListener('DOMContentLoaded', () => {
  // تمرير سلس للأقسام داخل الصفحة
  const links = document.querySelectorAll('a[href^="#"]');
  links.forEach(link => {
    link.addEventListener('click', e => {
      const target = document.querySelector(link.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // شريط العروض: الانتقال كل 3 ثوانٍ بمقدار بطاقة واحدة مع تدوير العناصر
  const marquee = document.querySelector('.promo-strip .marquee');
  const track = marquee ? marquee.querySelector('.track') : null;
  const btnNext = marquee ? marquee.querySelector('.arrow.right') : null;
  const btnPrev = marquee ? marquee.querySelector('.arrow.left') : null;
  const indicators = marquee ? marquee.querySelector('.indicators') : null;
  let currentIndex = 0;
  const total = track ? track.children.length : 0;
  const updateDots = () => {
    if (!indicators) return;
    const dots = indicators.querySelectorAll('.dot');
    dots.forEach((d, i) => d.classList.toggle('active', i === currentIndex));
  };
  const buildDots = () => {
    if (!indicators) return;
    indicators.innerHTML = '';
    for (let i = 0; i < total; i++) {
      const dot = document.createElement('span');
      dot.className = 'dot';
      indicators.appendChild(dot);
    }
    updateDots();
  };
  if (track && track.children.length > 1) {
    buildDots();
    const STEP_INTERVAL_MS = 3000; // الزمن بين كل نقلة
    // ملاحظة: مدة الانتقال الفعلية تُضبط في CSS عبر transition على .track

    const getGap = () => {
      const cs = getComputedStyle(track);
      let g = parseFloat(cs.columnGap);
      if (Number.isNaN(g)) g = parseFloat(cs.gap);
      return Number.isNaN(g) ? 0 : g;
    };

    let isAnimating = false;

    const stepForward = () => {
      if (isAnimating) return;
      const first = track.children[0];
      if (!first) return;
      const gap = getGap();
      const amount = first.getBoundingClientRect().width + gap;
      if (amount <= 0) return; // انتظر حتى تُحمّل الصور
      isAnimating = true;
      track.style.transform = `translateX(${-amount}px)`;
      const onEnd = () => {
        track.removeEventListener('transitionend', onEnd);
        track.classList.add('no-anim');
        track.appendChild(first);
        track.style.transform = 'translateX(0)';
        // force reflow
        void track.offsetWidth;
        track.classList.remove('no-anim');
        isAnimating = false;
        currentIndex = (currentIndex + 1) % total;
        updateDots();
      };
      track.addEventListener('transitionend', onEnd, { once: true });
    };

    const stepBackward = () => {
      if (isAnimating) return;
      const last = track.children[track.children.length - 1];
      if (!last) return;
      const gap = getGap();
      const width = last.getBoundingClientRect().width;
      const amount = width + gap;
      if (amount <= 0) return;
      isAnimating = true;
      // ضع الأخير في البداية بدون أنيميشن وحرّك للخلف بمقدار عرضه ثم ارجع للصفر مع أنيميشن
      track.classList.add('no-anim');
      track.insertBefore(last, track.children[0]);
      track.style.transform = `translateX(${-amount}px)`;
      void track.offsetWidth; // reflow
      track.classList.remove('no-anim');
      track.style.transform = 'translateX(0)';
      const onEnd = () => {
        track.removeEventListener('transitionend', onEnd);
        isAnimating = false;
        currentIndex = (currentIndex - 1 + total) % total;
        updateDots();
      };
      track.addEventListener('transitionend', onEnd, { once: true });
    };

    // ضمان الوضع الابتدائي بدون حركة
    track.classList.add('no-anim');
    track.style.transform = 'translateX(0)';
    void track.offsetWidth;
    track.classList.remove('no-anim');
    updateDots();

    let timer = setInterval(stepForward, STEP_INTERVAL_MS);

    // إيقاف مؤقت عند مرور المؤشر واستئناف عند الخروج
    marquee.addEventListener('mouseenter', () => { clearInterval(timer); });
    marquee.addEventListener('mouseleave', () => { timer = setInterval(stepForward, STEP_INTERVAL_MS); });

    // أزرار التالي/السابق
    const resetTimer = () => { clearInterval(timer); timer = setInterval(stepForward, STEP_INTERVAL_MS); };
    if (btnNext) btnNext.addEventListener('click', () => { stepForward(); resetTimer(); });
    if (btnPrev) btnPrev.addEventListener('click', () => { stepBackward(); resetTimer(); });

    // إعادة حساب العرض تلقائياً عند تغيير الحجم (نحسبه ديناميكياً داخل step)
    window.addEventListener('resize', () => {
      // لا حاجة لفعل شيء هنا لأننا نحسب العرض في كل خطوة
    });
  }
  // إخفاء الأسهم إذا لم يوجد سوى بطاقة واحدة
  if (marquee && (!track || track.children.length <= 1)) {
    const r = marquee.querySelector('.arrow.right');
    const l = marquee.querySelector('.arrow.left');
    if (r) r.style.display = 'none';
    if (l) l.style.display = 'none';
    buildDots();
  }
});


