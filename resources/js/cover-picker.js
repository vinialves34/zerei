export default ({ searchUrl, title, coverUrl, rawgId }) => ({
    title,
    coverUrl,
    rawgId,
    results: [],
    open: false,
    loading: false,
    unavailable: false,
    timer: null,
    controller: null,

    onTitleInput() {
        clearTimeout(this.timer);
        this.controller?.abort();

        if (this.title.trim().length < 2) {
            this.results = [];
            this.open = false;

            return;
        }

        this.timer = setTimeout(() => this.search(), 300);
    },

    async search() {
        this.controller = new AbortController();
        this.loading = true;
        this.unavailable = false;

        try {
            const response = await fetch(`${searchUrl}?q=${encodeURIComponent(this.title.trim())}`, {
                headers: { Accept: 'application/json' },
                signal: this.controller.signal,
            });

            if (! response.ok) {
                throw new Error(response.status);
            }

            this.results = await response.json();
            this.open = true;
        } catch (error) {
            if (error.name !== 'AbortError') {
                this.results = [];
                this.unavailable = true;
                this.open = true;
            }
        } finally {
            this.loading = false;
        }
    },

    choose(result) {
        this.title = result.name;
        this.coverUrl = result.cover_url ?? '';
        this.rawgId = result.id;
        this.open = false;
    },

    onCoverInput() {
        this.rawgId = '';
    },

    clearCover() {
        this.coverUrl = '';
        this.rawgId = '';
    },
});
