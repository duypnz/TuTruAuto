document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('.main-nav');
    if (toggle && nav) toggle.addEventListener('click', () => nav.classList.toggle('is-open'));

    const accountTrigger = document.querySelector('.account-trigger');
    const accountMenu = document.querySelector('.account-menu');
    if (accountTrigger && accountMenu) {
        let closeTimer;
        let clickOpen = false;
        const setOpen = (open) => {
            accountMenu.classList.toggle('is-open', open);
            accountTrigger.setAttribute('aria-expanded', String(open));
        };

        accountTrigger.setAttribute('aria-expanded', 'false');
        accountMenu.addEventListener('mouseenter', () => {
            window.clearTimeout(closeTimer);
            setOpen(true);
        });
        accountMenu.addEventListener('mouseleave', () => {
            if (!clickOpen) {
                closeTimer = window.setTimeout(() => setOpen(false), 200);
            }
        });
        accountTrigger.addEventListener('click', (event) => {
            event.stopPropagation();
            clickOpen = !clickOpen;
            setOpen(clickOpen || accountMenu.matches(':hover'));
        });

        document.addEventListener('click', (event) => {
            if (!accountMenu.contains(event.target)) {
                clickOpen = false;
                window.clearTimeout(closeTimer);
                setOpen(false);
            }
        });

    }
});
