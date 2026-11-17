
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

});
