
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

});
