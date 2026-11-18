
document.addEventListener('DOMContentLoaded', () => {

    // Mở và đóng menu trên điện thoại
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.main-nav');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('is-open');
        });
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
    if (orderForm) {
        const priceElement = document.querySelector('#detail-price');
        const variantSelect = orderForm.querySelector('#car-variant');
        const optionInputs = orderForm.querySelectorAll('.extra-options input[type="checkbox"]');
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
        if (variantSelect) variantSelect.addEventListener('change', updatePrice);
        optionInputs.forEach((option) => option.addEventListener('change', updatePrice));

        orderForm.addEventListener('submit', (event) => {
            const submitter = event.submitter;
            if (!submitter || submitter.value === 'cart') return;
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
        voucherButton.addEventListener('click', async () => {
            const result = document.querySelector('#voucher-result');
            const code = document.querySelector('#voucher-code');
            const csrf = orderForm.querySelector('[name="_csrf"]');
            const quantity = Number(orderForm.querySelector('[name="quantity"]').value || 1);
            const price = updateCarPrice(orderForm) * quantity;
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
            } catch (error) {
                result.textContent = 'Không thể kiểm tra mã lúc này. Vui lòng thử lại.';
                result.classList.add('is-error');
            }
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
                const compare = JSON.parse(localStorage.getItem('tuTruCompare') || '[]');
                const id = String(button.dataset.compareCar);
                const next = compare.includes(id) ? compare.filter((item) => item !== id) : [...compare, id].slice(-3);
                localStorage.setItem('tuTruCompare', JSON.stringify(next));
                button.setAttribute('aria-pressed', String(next.includes(id)));
                button.textContent = next.includes(id) ? '✓ Đã chọn so sánh' : '⇄ So sánh';
            } catch (error) {
                window.alert('Trình duyệt không cho phép lưu danh sách so sánh.');
            }
        });
    });

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
