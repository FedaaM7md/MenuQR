/*
 * MenuQR front-end interactions.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

import Alpine from '@alpinejs/csp';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('itemDialog', () => ({
        open: false,
        qty: 1,
        selected: [],
        warning: '',
        basePrice: 0,
        deltas: {},
        currency: '',
        limitMessage: '',

        init() {
            this.basePrice = Number(this.$el.dataset.basePrice || 0);
            this.deltas = JSON.parse(this.$el.dataset.deltas || '{}');
            this.currency = this.$el.dataset.currency || '';
            this.limitMessage = this.$el.dataset.limitMessage || '';
        },

        openDialog() {
            this.lastFocus = document.activeElement;
            this.open = true;
            this.warning = '';
            document.body.classList.add('overflow-hidden');
            this.$nextTick(() => {
                this.$refs.dialog?.querySelector('input, button')?.focus();
            });
        },

        closeDialog() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
            this.lastFocus?.focus?.();
        },

        increase() {
            this.qty = Math.min(99, this.qty + 1);
        },

        decrease() {
            this.qty = Math.max(1, this.qty - 1);
        },

        optionsDelta() {
            return this.selected.reduce((sum, id) => sum + (Number(this.deltas[id]) || 0), 0);
        },

        get unitMinor() {
            return Number(this.basePrice) + this.optionsDelta();
        },

        get totalMinor() {
            return this.unitMinor * this.qty;
        },

        get totalLabel() {
            return (this.totalMinor / 100).toFixed(2) + ' ' + this.currency;
        },

        enforce(event) {
            const input = event.target;
            const groupId = input.dataset.group;
            const max = Number(input.dataset.max || 1);
            const inputs = Array.from(this.$root.querySelectorAll('input[data-group="' + groupId + '"]'));
            const checked = inputs.filter((item) => item.checked);

            if (checked.length > max) {
                input.checked = false;
                this.selected = this.selected.filter((value) => String(value) !== String(input.value));
                this.warning = this.limitMessage;
            } else {
                this.warning = '';
            }
        },
    }));

    Alpine.data('kitchenBoard', () => ({
        feedUrl: '',
        locale: 'ar',
        todayLabel: '',
        loadingLabel: '',
        orders: [],
        counts: { new: 0, preparing: 0, ready: 0 },
        loading: true,
        error: '',
        busy: false,
        lastUpdated: '',
        timer: null,

        init() {
            this.feedUrl = this.$el.dataset.feedUrl || '/staff/kitchen/feed';
            this.locale = this.$el.dataset.locale || 'ar';
            this.todayLabel = this.$el.dataset.todayLabel || '';
            this.loadingLabel = this.$el.dataset.loadingLabel || '';
            this.refresh();
            this.timer = setInterval(() => this.refresh(), 10000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    this.refresh();
                }
            });
        },

        async refresh() {
            try {
                const response = await fetch(this.feedUrl, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store',
                });

                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }

                const payload = await response.json();
                this.orders = payload.orders;
                this.counts = payload.counts;
                this.error = '';
                this.lastUpdated = this.formatClock(new Date());
            } catch (error) {
                this.error = 'offline';
            } finally {
                this.loading = false;
            }
        },

        formatClock(date) {
            const formatter = new Intl.DateTimeFormat(this.locale === 'ar' ? 'ar-EG-u-nu-latn' : 'en-GB', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
            });
            const parts = formatter.formatToParts(date);
            const hour = parts.find((part) => part.type === 'hour')?.value || '';
            const minute = parts.find((part) => part.type === 'minute')?.value || '';
            const period = parts.find((part) => part.type === 'dayPeriod')?.value || '';
            const marker = this.locale === 'ar' ? (period === 'AM' ? 'ص' : 'م') : period.toUpperCase();

            return hour + ':' + minute + ' ' + marker;
        },

        get lastUpdatedLabel() {
            return this.lastUpdated ? this.todayLabel + ' ' + this.lastUpdated : this.loadingLabel;
        },

        filtered(status) {
            return this.orders.filter((order) => order.status === status);
        },

        async updateStatus(event) {
            const form = event.target;
            this.busy = true;

            try {
                await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
            } finally {
                this.busy = false;
                this.refresh();
            }
        },
    }));

    Alpine.data('copyLink', () => ({
        copied: false,
        async copy() {
            const url = this.$el.dataset.copyUrl || '';
            try {
                await navigator.clipboard.writeText(url);
                this.copied = true;
                setTimeout(() => (this.copied = false), 1600);
            } catch (error) {
                this.copied = false;
            }
        },
    }));

    Alpine.data('menuNav', () => ({
        active: 'top',
        init() {
            const sections = Array.from(document.querySelectorAll('[data-menu-section]'));
            if (!sections.length) {
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

                if (visible) {
                    this.setActive(visible.target.dataset.menuSection, false);
                }
            }, { rootMargin: '-38% 0px -52% 0px', threshold: [0.05, 0.2, 0.5] });

            sections.forEach((section) => observer.observe(section));
        },
        setActive(id, scrollChip = true) {
            this.active = id;
            if (scrollChip) {
                this.$nextTick(() => this.centerChip());
            }
        },
        go(id) {
            const section = document.getElementById(id);
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            this.setActive(id);
        },
        chipClass(id) {
            return this.active === id ? 'chip-active' : '';
        },
        centerChip() {
            const chip = this.$refs.scroller?.querySelector(`[data-nav="${this.active}"]`);
            chip?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        },
    }));

    Alpine.data('disclosure', () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
    }));

    Alpine.data('shell', () => ({
        sidebar: false,
        openSidebar() {
            this.sidebar = true;
        },
        closeSidebar() {
            this.sidebar = false;
        },
        get sidebarClass() {
            return this.sidebar ? 'sidebar-open' : '';
        },
    }));

    Alpine.store('network', {
        online: navigator.onLine,
        init() {
            window.addEventListener('online', () => (this.online = true));
            window.addEventListener('offline', () => (this.online = false));
        },
    });
});

Alpine.start();

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Service worker registration is a progressive enhancement.
        });
    });
}

window.addEventListener('online', () => document.documentElement.classList.remove('is-offline'));
window.addEventListener('offline', () => document.documentElement.classList.add('is-offline'));

// Quick search: "/" focuses the first visible search input.
document.addEventListener('keydown', (event) => {
    if (event.key !== '/' || event.metaKey || event.ctrlKey || event.altKey) {
        return;
    }

    const active = document.activeElement?.tagName?.toLowerCase();
    if (['input', 'textarea', 'select'].includes(active)) {
        return;
    }

    const search = document.querySelector('[data-search-input]');
    if (search) {
        event.preventDefault();
        search.focus();
        search.select?.();
    }
});


function setNetworkState() {
    const online = navigator.onLine;
    document.getElementById('offline-banner')?.classList.toggle('hidden', online);
    document.getElementById('checkout-offline')?.classList.toggle('hidden', online);
    const submit = document.querySelector('[data-checkout-submit]');
    if (submit) {
        submit.disabled = !online;
    }
}

function closeSheet(button) {
    const root = button.closest('[data-sheet]');
    root?.querySelector('[data-sheet-panel]')?.classList.add('hidden');
}

document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-sheet-open]');
    if (opener) {
        const root = opener.closest('[data-sheet]');
        root?.querySelector('[data-sheet-panel]')?.classList.remove('hidden');
        return;
    }

    const closer = event.target.closest('[data-sheet-close]');
    if (closer) {
        closeSheet(closer);
        return;
    }

    const editor = event.target.closest('[data-editor-toggle]');
    if (editor) {
        const panel = editor.closest('[data-editor]')?.querySelector('[data-editor-panel]');
        panel?.classList.toggle('hidden');
        return;
    }

    const copy = event.target.closest('[data-copy]');
    if (copy) {
        const url = copy.closest('[data-copy-url]')?.dataset.copyUrl || '';
        const label = copy.querySelector('[data-copy-label]');
        const showCopied = () => {
            if (!label) {
                return;
            }
            label.textContent = label.dataset.copyCopied || label.textContent;
            setTimeout(() => {
                label.textContent = label.dataset.copyDefault || label.textContent;
            }, 1600);
        };
        const fallback = () => {
            const area = document.createElement('textarea');
            area.value = url;
            area.style.position = 'fixed';
            area.style.opacity = '0';
            document.body.appendChild(area);
            area.select();
            try { document.execCommand('copy'); showCopied(); } catch {}
            area.remove();
        };

        if (navigator.clipboard?.writeText) {
            navigator.clipboard.writeText(url).then(showCopied).catch(fallback);
        } else {
            fallback();
        }
        return;
    }

    const toastClose = event.target.closest('[data-toast-close]');
    if (toastClose) {
        toastClose.closest('[data-toast]')?.remove();
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-confirm-submit]');
    if (form && !window.confirm(form.dataset.confirm || '')) {
        event.preventDefault();
        return;
    }

    const checkout = event.target.closest('[data-checkout-form]');
    if (checkout) {
        const label = checkout.querySelector('[data-submit-label]');
        const button = checkout.querySelector('[data-checkout-submit]');
        if (label) {
            label.textContent = checkout.dataset.submittingLabel || label.textContent;
        }
        if (button) {
            button.disabled = true;
        }
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        document.querySelectorAll('[data-sheet-panel]').forEach((panel) => panel.classList.add('hidden'));
        document.querySelectorAll('[data-editor-panel]').forEach((panel) => panel.classList.add('hidden'));
    }
});

function boot() {
    setNetworkState();
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        setTimeout(() => toast.remove(), 5000);
    });
}

window.addEventListener('online', setNetworkState);
window.addEventListener('offline', setNetworkState);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
