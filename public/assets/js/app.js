
document.addEventListener('DOMContentLoaded', () => {

    const carModelViewer = document.querySelector('#car-3d-model');
    const openCar3dButton = document.querySelector('[data-open-car-3d]');
    const car3dPanel = document.querySelector('[data-car-3d-panel]');
    if (openCar3dButton && car3dPanel && carModelViewer) {
        openCar3dButton.addEventListener('click', () => {
            const opening = car3dPanel.hidden;
            car3dPanel.hidden = !opening;
            openCar3dButton.setAttribute('aria-expanded', String(opening));
            openCar3dButton.textContent = opening ? 'Ẩn mô hình 3D' : 'Xem mô hình 3D 360°';
            if (opening && !carModelViewer.src) carModelViewer.src = carModelViewer.dataset.modelSrc;
        });
    }

    document.querySelectorAll('[data-car-model-mode]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!carModelViewer) return;
            carModelViewer.src = button.dataset.modelSrc;
            const interior = button.dataset.modelView === 'interior';
            carModelViewer.setAttribute('camera-target', interior ? '0.41m 1.05m -0.05m' : '0m 0.8m 0m');
            carModelViewer.setAttribute('camera-orbit', interior ? '180deg 84deg 0.35m' : '35deg 68deg 120%');
            if (interior) {
                carModelViewer.setAttribute('max-camera-orbit', 'auto auto 1.2m');
                carModelViewer.removeAttribute('auto-rotate');
            } else {
                carModelViewer.removeAttribute('max-camera-orbit');
                carModelViewer.setAttribute('auto-rotate', '');
            }
            document.querySelectorAll('[data-car-model-mode]').forEach((mode) => {
                const active = mode === button;
                mode.classList.toggle('is-active', active);
                mode.setAttribute('aria-pressed', String(active));
            });
        });
    });

    // Mở và đóng menu trên điện thoại
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.main-nav');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('is-open');
        });
    }

    const heroCarousel = document.querySelector('[data-hero-carousel]');
    if (heroCarousel) {
        const slides = Array.from(heroCarousel.querySelectorAll('.hero-slide'));
        const dots = Array.from(document.querySelectorAll('[data-hero-dot]'));
        let currentSlide = slides.findIndex((slide) => slide.classList.contains('is-active'));
        if (currentSlide < 0) currentSlide = 0;
        const showSlide = (index) => {
            currentSlide = (index + slides.length) % slides.length;
            slides.forEach((slide, slideIndex) => {
                const active = slideIndex === currentSlide;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', String(!active));
            });
            dots.forEach((dot, dotIndex) => dot.setAttribute('aria-current', String(dotIndex === currentSlide)));
        };
        document.querySelector('[data-hero-prev]')?.addEventListener('click', () => showSlide(currentSlide - 1));
        document.querySelector('[data-hero-next]')?.addEventListener('click', () => showSlide(currentSlide + 1));
        dots.forEach((dot) => dot.addEventListener('click', () => showSlide(Number(dot.dataset.heroDot))));
        if (slides.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            let timer = window.setInterval(() => showSlide(currentSlide + 1), 5500);
            heroCarousel.addEventListener('mouseenter', () => window.clearInterval(timer));
            heroCarousel.addEventListener('mouseleave', () => {
                window.clearInterval(timer);
                timer = window.setInterval(() => showSlide(currentSlide + 1), 5500);
            });
        }
    }

    // Menu tài khoản
    const accountMenu = document.querySelector('.account-menu');
    const accountTrigger = document.querySelector('.account-trigger');

    if (accountMenu && accountTrigger) {

        let closeTimer;
        let clickOpen = false;

        // Hàm mở hoặc đóng menu
        const setOpen = (open) => {
            accountMenu.classList.toggle('is-open', open);

            accountTrigger.setAttribute(
                'aria-expanded',
                String(open)
            );
        };

        accountTrigger.setAttribute('aria-expanded', 'false');

        // Khi rê chuột vào menu
        accountMenu.addEventListener('mouseenter', () => {
            window.clearTimeout(closeTimer);
            setOpen(true);
        });

        // Khi rê chuột ra khỏi menu
        accountMenu.addEventListener('mouseleave', () => {

            if (!clickOpen) {
                closeTimer = window.setTimeout(() => {
                    setOpen(false);
                }, 200);
            }

        });

        // Khi click vào nút tài khoản
        accountTrigger.addEventListener('click', (event) => {

            event.stopPropagation();

            clickOpen = !clickOpen;

            setOpen(
                clickOpen || accountMenu.matches(':hover')
            );

        });

        // Click bên ngoài để đóng menu
        document.addEventListener('click', (event) => {

            if (!accountMenu.contains(event.target)) {

                clickOpen = false;

                window.clearTimeout(closeTimer);

                setOpen(false);
            }

        });

        // Nhấn ESC để đóng menu
        document.addEventListener('keydown', (event) => {

            if (event.key === 'Escape') {

                clickOpen = false;

                window.clearTimeout(closeTimer);

                setOpen(false);
            }

        });

    }

    // Car detail gallery and configuration
    const mainCarImage = document.querySelector('#car-main-image');
    document.querySelectorAll('.detail-thumbnail').forEach((thumbnail) => {
        thumbnail.addEventListener('click', () => {
            if (!mainCarImage) return;
            mainCarImage.src = thumbnail.dataset.image;
            document.querySelectorAll('.detail-thumbnail').forEach((item) => {
                item.classList.toggle('is-active', item === thumbnail);
                item.setAttribute('aria-pressed', String(item === thumbnail));
            });
        });
    });

    const orderForm = document.querySelector('#car-order-form');
    let appliedVoucherDiscount = 0;
    if (orderForm) {
        const priceElement = document.querySelector('#detail-price');
        const variantSelect = orderForm.querySelector('#car-variant');
        const optionInputs = orderForm.querySelectorAll('.extra-options input[type="checkbox"]');
        const depositPreview = orderForm.querySelector('[data-deposit-preview] strong');
        const updatePrice = () => {
            if (!priceElement) return 0;
            const selectedVariant = variantSelect ? variantSelect.selectedOptions[0] : null;
            const delta = Number(selectedVariant ? selectedVariant.dataset.delta : 0)
                + Array.from(optionInputs).reduce((total, option) => total + (option.checked ? Number(option.dataset.delta || 0) : 0), 0);
            const amount = Number(priceElement.dataset.basePrice || 0) + delta;
            const formatted = new Intl.NumberFormat('vi-VN').format(amount) + ' ₫';
            const priceText = priceElement.querySelector('small, .sale-label');
            priceElement.firstChild.textContent = formatted + ' ';
            if (priceText) priceElement.appendChild(priceText);
            return amount;
        };
        const updateDepositPreview = () => {
            const amount = Math.max(0, updatePrice() - appliedVoucherDiscount);
            if (depositPreview) depositPreview.textContent = new Intl.NumberFormat('vi-VN').format(Math.round(amount * 0.1)) + ' ₫';
        };
        if (variantSelect) variantSelect.addEventListener('change', () => {
            appliedVoucherDiscount = 0;
            updateDepositPreview();
        });
        optionInputs.forEach((option) => option.addEventListener('change', () => {
            appliedVoucherDiscount = 0;
            updateDepositPreview();
        }));

        orderForm.addEventListener('submit', (event) => {
            const submitter = event.submitter;
            if (!submitter || submitter.value !== 'deposit') return;
            const name = orderForm.querySelector('[name="customer_name"]');
            const phone = orderForm.querySelector('[name="phone"]');
            if (!name.value.trim() || !phone.value.trim()) {
                event.preventDefault();
                window.alert('Vui lòng nhập họ tên và số điện thoại để chúng tôi liên hệ.');
                (name.value.trim() ? phone : name).focus();
            }
        });
    }

    const voucherButton = document.querySelector('#check-voucher');
    if (voucherButton && orderForm) {
        const depositPreview = orderForm.querySelector('[data-deposit-preview] strong');
        voucherButton.addEventListener('click', async () => {
            const result = document.querySelector('#voucher-result');
            const code = document.querySelector('#voucher-code');
            const csrf = orderForm.querySelector('[name="_csrf"]');
            const price = updateCarPrice(orderForm);
            result.textContent = 'Đang kiểm tra mã...';
            result.classList.remove('is-success', 'is-error');
            try {
                const response = await fetch(voucherButton.dataset.endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                    body: new URLSearchParams({ _csrf: csrf.value, code: code.value.trim(), price: String(price) })
                });
                const data = await response.json();
                result.textContent = data.message;
                result.classList.add(data.valid ? 'is-success' : 'is-error');
                appliedVoucherDiscount = data.valid ? Number(data.discount) : 0;
                if (depositPreview) {
                    const discountedPrice = Math.max(0, price - appliedVoucherDiscount);
                    depositPreview.textContent = new Intl.NumberFormat('vi-VN').format(Math.round(discountedPrice * 0.1)) + ' ₫';
                }
            } catch (error) {
                appliedVoucherDiscount = 0;
                const depositPreview = orderForm.querySelector('[data-deposit-preview] strong');
                if (depositPreview) depositPreview.textContent = new Intl.NumberFormat('vi-VN').format(Math.round(price * 0.1)) + ' ₫';
                result.textContent = 'Không thể kiểm tra mã lúc này. Vui lòng thử lại.';
                result.classList.add('is-error');
            }
        });
    }

    const installmentCalculator = document.querySelector('[data-installment-calculator]');
    if (installmentCalculator) {
        const result = installmentCalculator.querySelector('[data-installment-result]');
        const termSelect = installmentCalculator.querySelector('[name="term_months"]');
        const downPaymentInput = installmentCalculator.querySelector('[name="down_payment_amount"]');
        const rateResult = installmentCalculator.querySelector('[data-installment-rate]');
        const rateRanges = {
            12: [6, 8], 24: [6, 9], 36: [7, 10], 48: [7, 10],
            60: [8, 11], 72: [8, 11], 84: [8, 12]
        };
        const formatMoney = (value) => new Intl.NumberFormat('vi-VN').format(Math.round(value)) + ' ₫';
        const updateRateLabel = () => {
            const term = Number(termSelect.value);
            const [minRate, maxRate] = rateRanges[term] || [0, 0];
            if (rateResult) rateResult.textContent = 'Biên độ lãi suất: ' + minRate + '–' + maxRate + '%/năm';
            const price = orderForm ? updateCarPrice(orderForm) : 0;
            if (downPaymentInput && price) {
                const down = Number(downPaymentInput.value || 0);
                const hint = installmentCalculator.querySelector('[data-down-payment-hint]');
                if (hint) hint.textContent = price > 0 ? 'Tương đương ' + (down / price * 100).toFixed(1).replace('.0', '') + '% giá xe' : '';
            }
        };
        termSelect?.addEventListener('change', updateRateLabel);
        downPaymentInput?.addEventListener('input', updateRateLabel);
        updateRateLabel();
        installmentCalculator.querySelector('[data-calculate-installment]')?.addEventListener('click', () => {
            const downPayment = Number(downPaymentInput?.value || 0);
            const months = Number(termSelect?.value || 0);
            const price = orderForm ? updateCarPrice(orderForm) : Number(document.querySelector('#detail-price')?.dataset.basePrice || 0);
            const [minRate, maxRate] = rateRanges[months] || [0, 0];
            if (!Number.isFinite(downPayment) || downPayment < 0 || downPayment >= price || !price || !months) {
                result.textContent = 'Số tiền cọc phải nhỏ hơn giá xe. Vui lòng nhập lại.';
                result.classList.add('is-error');
                return;
            }
            const annualRate = (minRate + maxRate) / 2;
            const principal = price - downPayment;
            const monthlyRate = annualRate / 1200;
            const monthlyPayment = monthlyRate === 0
                ? principal / months
                : principal * monthlyRate / (1 - Math.pow(1 + monthlyRate, -months));
            const totalPayment = monthlyPayment * months;
            const totalInterest = totalPayment - principal;
            result.innerHTML =
                '<strong>Còn cần thanh toán: ' + formatMoney(principal) + '</strong><br>' +
                'Tổng lãi dự kiến (' + annualRate + '%/năm): ' + formatMoney(totalInterest) + '<br>' +
                'Tổng số tiền sau lãi: ' + formatMoney(totalPayment) + '<br>' +
                'Trả khoảng <strong>' + formatMoney(monthlyPayment) + '/tháng</strong> trong ' + months + ' tháng.';
            result.classList.remove('is-error');
        });
    }

    function updateCarPrice(form) {
        const priceElement = document.querySelector('#detail-price');
        if (!priceElement) return 0;
        const selectedVariant = form.querySelector('#car-variant')?.selectedOptions[0];
        const options = form.querySelectorAll('.extra-options input[type="checkbox"]:checked');
        const delta = Number(selectedVariant ? selectedVariant.dataset.delta : 0)
            + Array.from(options).reduce((total, option) => total + Number(option.dataset.delta || 0), 0);
        return Number(priceElement.dataset.basePrice || 0) + delta;
    }

    // Accessible description tabs
    document.querySelectorAll('[data-car-tabs]').forEach((tabs) => {
        const buttons = tabs.querySelectorAll('[role="tab"]');
        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                buttons.forEach((tab) => {
                    const active = tab === button;
                    tab.setAttribute('aria-selected', String(active));
                    tabs.querySelector('#' + tab.getAttribute('aria-controls')).hidden = !active;
                });
            });
        });
    });

    document.querySelectorAll('[data-compare-car]').forEach((button) => {
        button.addEventListener('click', () => {
            try {
                const stored = JSON.parse(localStorage.getItem('tuTruCompare') || '[]');
                const compare = Array.isArray(stored) ? stored.map(String).filter((item) => /^\d+$/.test(item)) : [];
                const id = String(button.dataset.compareCar);
                const next = compare.includes(id) ? compare.filter((item) => item !== id) : [...compare, id].slice(-3);
                localStorage.setItem('tuTruCompare', JSON.stringify(next));
                window.location.href = button.dataset.compareUrl + '?ids=' + encodeURIComponent(next.join(','));
            } catch (error) {
                window.alert('Trình duyệt không cho phép lưu danh sách so sánh.');
            }
        });
    });

    const compareForm = document.querySelector('[data-compare-select-form]');
    if (compareForm) {
        compareForm.addEventListener('submit', (event) => {
            const ids = Array.from(compareForm.querySelectorAll('[data-compare-select]'))
                .map((select) => select.value)
                .filter(Boolean);
            if (ids.length < 2 || new Set(ids).size !== ids.length) {
                event.preventDefault();
                window.alert(ids.length < 2 ? 'Hãy chọn ít nhất hai xe.' : 'Mỗi xe chỉ được chọn một lần.');
                return;
            }
            try {
                localStorage.setItem('tuTruCompare', JSON.stringify(ids));
            } catch (error) {
                window.alert('Trình duyệt không cho phép lưu danh sách so sánh.');
                event.preventDefault();
            }
        });
    }

    document.querySelectorAll('[data-share-car]').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                if (navigator.share) {
                    await navigator.share({ title: document.title, url: window.location.href });
                } else {
                    await navigator.clipboard.writeText(window.location.href);
                    button.textContent = '✓ Đã sao chép liên kết';
                }
            } catch (error) {
                if (error.name !== 'AbortError') button.textContent = 'Không thể chia sẻ lúc này';
            }
        });
    });

});
