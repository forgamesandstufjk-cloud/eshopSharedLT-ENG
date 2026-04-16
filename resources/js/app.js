import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('favorites', {
    ids: [],

    async load() {
        try {
            const res = await fetch('/api/favorites/ids', {
                credentials: 'include',
                headers: { Accept: 'application/json' },
            });

            if (res.status === 401) {
                this.ids = [];
                return;
            }

            this.ids = res.ok ? await res.json() : [];
        } catch (e) {
            console.error('Failed to load favorites', e);
            this.ids = [];
        }
    },

    has(id) {
        return this.ids.includes(id);
    },

    async toggle(listingId) {
        const csrf = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        if (!csrf) {
            console.error('Missing CSRF token');
            return;
        }

        try {
            if (this.has(listingId)) {
                await fetch(`/api/favorite/${listingId}`, {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                });
            } else {
                const res = await fetch('/api/favorite', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ listing_id: listingId }),
                });

                if (res.status === 401) {
                    window.location.href = '/login';
                    return;
                }
            }
        } catch (e) {
            console.error('Favorite toggle failed', e);
        }

        await this.load();
    },
});

document.addEventListener('alpine:init', () => {
    const isLoggedIn = document.body.dataset.auth === '1';

    if (isLoggedIn) {
        Alpine.store('favorites').load();
    }
});

Alpine.start();
