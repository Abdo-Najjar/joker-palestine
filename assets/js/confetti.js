/**
 * مختبر الجوكر الأمني - محرك الاحتفال والكونفيتي والتأثيرات الصوتية (Confetti & Victory Celebration)
 * إعداد وتطوير: الجوكر الفلسطيني احمد سليم 🇵🇸
 */

(function () {
    'use strict';

    // 1. مؤثر صوتي نغمي للاحتفال والانتصار (Web Audio API Fanfare)
    function playVictoryChime() {
        try {
            var AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            var ctx = new AudioCtx();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            var notes = [
                { freq: 523.25, time: 0.00, dur: 0.12 }, // C5
                { freq: 659.25, time: 0.11, dur: 0.12 }, // E5
                { freq: 783.99, time: 0.22, dur: 0.14 }, // G5
                { freq: 1046.50, time: 0.35, dur: 0.55 }, // C6
                { freq: 1318.51, time: 0.42, dur: 0.50 }  // E6 Harmonics
            ];

            notes.forEach(function (n) {
                var osc = ctx.createOscillator();
                var gain = ctx.createGain();

                osc.type = 'triangle';
                osc.frequency.setValueAtTime(n.freq, ctx.currentTime + n.time);

                gain.gain.setValueAtTime(0.001, ctx.currentTime + n.time);
                gain.gain.exponentialRampToValueAtTime(0.3, ctx.currentTime + n.time + 0.03);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + n.time + n.dur);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(ctx.currentTime + n.time);
                osc.stop(ctx.currentTime + n.time + n.dur + 0.05);
            });
        } catch (e) {
            console.log('Audio autoplay prevented or unsupported:', e);
        }
    }

    // 2. محرك الكونفيتي المخصص والخفيف فائق الأداء (HTML5 Canvas Confetti)
    function launchConfetti(options) {
        options = options || {};
        var duration = options.duration || 3500;
        var particleCount = options.particleCount || 130;
        var colors = options.colors || ['#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#ffffff'];

        var canvas = document.createElement('canvas');
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100vw';
        canvas.style.height = '100vh';
        canvas.style.pointerEvents = 'none';
        canvas.style.zIndex = '999999';
        document.body.appendChild(canvas);

        var ctx = canvas.getContext('2d');
        var width = (canvas.width = window.innerWidth);
        var height = (canvas.height = window.innerHeight);

        var particles = [];
        for (var i = 0; i < particleCount; i++) {
            // انطلاق من زوايا الشاشة والوسط
            var originX = options.originX !== undefined ? options.originX : (i % 3 === 0 ? 0.2 : (i % 3 === 1 ? 0.8 : 0.5)) * width;
            var originY = options.originY !== undefined ? options.originY : (height * 0.7);

            var angle = (Math.random() * Math.PI) - (Math.PI / 2); // متفرقة لأعلى
            var speed = Math.random() * 14 + 10;

            particles.push({
                x: originX,
                y: originY,
                vx: Math.cos(angle) * speed + (Math.random() - 0.5) * 6,
                vy: -Math.abs(Math.sin(angle)) * speed - (Math.random() * 8 + 4),
                size: Math.random() * 8 + 5,
                color: colors[Math.floor(Math.random() * colors.length)],
                rotation: Math.random() * 360,
                rotationSpeed: (Math.random() - 0.5) * 12,
                opacity: 1,
                decay: Math.random() * 0.008 + 0.005,
                shape: Math.random() > 0.4 ? 'rect' : 'circle',
                flutter: Math.random() * 0.1
            });
        }

        var startTime = performance.now();

        function render(now) {
            var elapsed = now - startTime;
            ctx.clearRect(0, 0, width, height);

            var activeCount = 0;
            for (var j = 0; j < particles.length; j++) {
                var p = particles[j];
                if (p.opacity <= 0) continue;

                p.x += p.vx;
                p.y += p.vy;
                p.vy += 0.35; // جاذبية
                p.vx *= 0.985; // مقاومة هواء
                p.rotation += p.rotationSpeed;
                p.opacity -= p.decay;

                if (p.opacity > 0) {
                    activeCount++;
                    ctx.save();
                    ctx.translate(p.x, p.y);
                    ctx.rotate((p.rotation * Math.PI) / 180);
                    ctx.globalAlpha = Math.max(0, p.opacity);
                    ctx.fillStyle = p.color;

                    if (p.shape === 'rect') {
                        ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * (0.6 + Math.sin(now * 0.01 + j) * 0.4));
                    } else {
                        ctx.beginPath();
                        ctx.arc(0, 0, p.size / 2, 0, Math.PI * 2);
                        ctx.fill();
                    }
                    ctx.restore();
                }
            }

            if (activeCount > 0 && elapsed < duration) {
                requestAnimationFrame(render);
            } else {
                if (canvas && canvas.parentNode) {
                    canvas.parentNode.removeChild(canvas);
                }
            }
        }

        requestAnimationFrame(render);
    }

    // 3. إطلاق موجات احتفالية متعددة (Cannons Left, Right & Center)
    function fullCelebrationBlast() {
        playVictoryChime();

        // موجة أولى من الوسط
        launchConfetti({ particleCount: 90, duration: 4000 });

        // موجة ثانية من اليسار
        setTimeout(function () {
            launchConfetti({ particleCount: 70, originX: window.innerWidth * 0.1, originY: window.innerHeight * 0.8, duration: 3500 });
        }, 250);

        // موجة ثالثة من اليمين
        setTimeout(function () {
            launchConfetti({ particleCount: 70, originX: window.innerWidth * 0.9, originY: window.innerHeight * 0.8, duration: 3500 });
        }, 500);
    }

    // 4. الدالة العامة المتاحة لكل الصفحات
    window.triggerConfettiCelebration = function (challengeTitle, flagValue) {
        fullCelebrationBlast();

        var modalEl = document.getElementById('celebrationModal');
        if (modalEl) {
            if (challengeTitle) {
                var titleEl = document.getElementById('celebrationChallengeTitle');
                if (titleEl) titleEl.textContent = challengeTitle;
            }
            if (flagValue) {
                var flagEl = document.getElementById('celebrationFlagText');
                if (flagEl) flagEl.textContent = flagValue;
                var copyBtn = document.getElementById('celebrationCopyBtn');
                if (copyBtn) copyBtn.setAttribute('data-flag', flagValue);
            }
            var bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            bsModal.show();
        }
    };

    window.playVictoryChime = playVictoryChime;
    window.launchConfetti = launchConfetti;
    window.fullCelebrationBlast = fullCelebrationBlast;

    // 5. التشغيل التلقائي عند وجود احتفال ممرر من السيرفر
    document.addEventListener('DOMContentLoaded', function () {
        var autoCelebrateData = document.getElementById('autoCelebrateData');
        if (autoCelebrateData) {
            var title = autoCelebrateData.getAttribute('data-title') || '';
            var flag = autoCelebrateData.getAttribute('data-flag') || '';
            setTimeout(function () {
                window.triggerConfettiCelebration(title, flag);
            }, 300);
        }
    });

})();
