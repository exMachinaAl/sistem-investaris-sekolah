(() => {
    const hoverDelay = 650;
    const hideDelay = 180;
    const tips = [...document.querySelectorAll('[data-help-tip]')];

    const position = (root) => {
        const trigger = root.querySelector('.help-tip-trigger');
        const bubble = root.querySelector('.help-tip-bubble');
        if (!trigger || !bubble || bubble.hidden) return;

        const triggerRect = trigger.getBoundingClientRect();
        const bubbleRect = bubble.getBoundingClientRect();
        const margin = 12;
        let left = triggerRect.left + (triggerRect.width / 2) - (bubbleRect.width / 2);
        left = Math.max(margin, Math.min(left, window.innerWidth - bubbleRect.width - margin));
        let top = triggerRect.bottom + 9;
        if (top + bubbleRect.height > window.innerHeight - margin) {
            top = Math.max(margin, triggerRect.top - bubbleRect.height - 9);
        }
        bubble.style.left = `${left}px`;
        bubble.style.top = `${top}px`;
    };

    const hide = (root) => {
        clearTimeout(root._helpShowTimer);
        clearTimeout(root._helpHideTimer);
        const trigger = root.querySelector('.help-tip-trigger');
        const bubble = root.querySelector('.help-tip-bubble');
        if (!trigger || !bubble) return;
        bubble.hidden = true;
        root.dataset.open = 'false';
        root.dataset.pinned = 'false';
        trigger.setAttribute('aria-expanded', 'false');
    };

    const closeOthers = (except = null) => tips.forEach((tip) => {
        if (tip !== except) hide(tip);
    });

    const show = (root, pinned = false) => {
        clearTimeout(root._helpShowTimer);
        clearTimeout(root._helpHideTimer);
        closeOthers(root);
        const trigger = root.querySelector('.help-tip-trigger');
        const bubble = root.querySelector('.help-tip-bubble');
        if (!trigger || !bubble) return;
        bubble.hidden = false;
        root.dataset.open = 'true';
        root.dataset.pinned = pinned ? 'true' : 'false';
        trigger.setAttribute('aria-expanded', 'true');
        requestAnimationFrame(() => position(root));
    };

    tips.forEach((root) => {
        const trigger = root.querySelector('.help-tip-trigger');
        root.dataset.open = 'false';
        root.dataset.pinned = 'false';

        root.addEventListener('mouseenter', () => {
            clearTimeout(root._helpHideTimer);
            if (root.dataset.open === 'true') return;
            root._helpShowTimer = setTimeout(() => show(root), hoverDelay);
        });
        root.addEventListener('mouseleave', () => {
            clearTimeout(root._helpShowTimer);
            if (root.dataset.pinned === 'true') return;
            root._helpHideTimer = setTimeout(() => hide(root), hideDelay);
        });
        trigger?.addEventListener('focus', () => show(root));
        trigger?.addEventListener('blur', () => {
            if (root.dataset.pinned !== 'true') root._helpHideTimer = setTimeout(() => hide(root), hideDelay);
        });
        trigger?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (root.dataset.pinned === 'true') hide(root);
            else show(root, true);
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-help-tip]')) closeOthers();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeOthers();
    });
    window.addEventListener('resize', () => tips.forEach((tip) => {
        if (tip.dataset.open === 'true') position(tip);
    }));
    window.addEventListener('scroll', () => tips.forEach((tip) => {
        if (tip.dataset.open === 'true') position(tip);
    }), true);

    const faqSearch = document.querySelector('[data-faq-search]');
    if (faqSearch) {
        const items = [...document.querySelectorAll('[data-faq-item]')];
        const empty = document.querySelector('[data-faq-empty]');
        faqSearch.addEventListener('input', () => {
            const query = faqSearch.value.trim().toLocaleLowerCase('id');
            let visible = 0;
            items.forEach((item) => {
                const matches = !query || item.textContent.toLocaleLowerCase('id').includes(query);
                item.hidden = !matches;
                if (matches) visible++;
            });
            if (empty) empty.hidden = visible !== 0;
        });
    }
})();
