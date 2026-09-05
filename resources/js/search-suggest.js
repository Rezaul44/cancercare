/**
 * হোমপেজের সার্চ বক্সের ড্রপডাউন — GET /ajax/search/suggest থেকে আসা ফলাফল
 * ৪টি গ্রুপে (ডাক্তার/হাসপাতাল/গাইড/রোগীর সহায়তা) দেখায়। doctor-match.js-এর
 * esc()-escape করা template-literal + $refs.innerHTML প্যাটার্ন অনুসরণ করে —
 * টাইটেল/এক্সসার্প্ট DB থেকে আসা টেক্সট, তাই stored-XSS ঠেকাতে escape বাধ্যতামূলক।
 */
function esc(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';

    return div.innerHTML;
}

const GROUP_ICONS = {
    doctors: { bg: '#E3EEF9', fg: '#1C5E9E', icon: 'ti-stethoscope' },
    hospitals: { bg: '#DCF2ED', fg: '#0B6E5C', icon: 'ti-building-hospital' },
    guides: { bg: '#EFEEFC', fg: '#5B4FB5', icon: 'ti-book-2' },
    patient_cases: { bg: '#FFE1EC', fg: '#DE0159', icon: 'ti-heart-handshake' },
};

const EMPTY_RESULTS = { doctors: [], hospitals: [], guides: [], patient_cases: [] };

export default function searchSuggest() {
    return {
        query: '',
        focused: false,
        open: false,
        results: { ...EMPTY_RESULTS },
        debounceTimer: null,
        placeholders: [
            'যা জানতে চান লিখুন — ডাক্তার, হাসপাতাল, খরচ',
            'স্তন ক্যান্সারের ভালো ডাক্তার কে',
            'NICRH-এ কী কী চিকিৎসা হয়',
            'কেমোথেরাপিতে আসলে কত টাকা লাগে',
            'সরকারি হাসপাতালে রেডিওথেরাপি কোথায়',
        ],
        placeholderIndex: 0,

        init() {
            setInterval(() => {
                if (!this.focused && this.query === '') {
                    this.placeholderIndex = (this.placeholderIndex + 1) % this.placeholders.length;
                }
            }, 3400);
        },

        get totalResults() {
            return Object.values(this.results).reduce((sum, group) => sum + group.length, 0);
        },

        onInput() {
            this.open = this.query.length > 0;
            clearTimeout(this.debounceTimer);

            if (this.query.trim().length < 2) {
                this.results = { ...EMPTY_RESULTS };
                this.renderGroups();

                return;
            }

            this.debounceTimer = setTimeout(() => this.fetchSuggestions(), 250);
        },

        async fetchSuggestions() {
            try {
                const response = await window.axios.get('/ajax/search/suggest', { params: { q: this.query } });
                this.results = { ...EMPTY_RESULTS, ...response.data };
            } catch (error) {
                console.error('Search suggest request failed:', error);
                this.results = { ...EMPTY_RESULTS };
            } finally {
                this.renderGroups();
            }
        },

        renderGroups() {
            for (const key of Object.keys(GROUP_ICONS)) {
                const ref = this.$refs[key + 'List'];
                if (!ref) continue;
                ref.innerHTML = (this.results[key] || []).map((item) => this.itemHtml(key, item)).join('');
            }
        },

        itemHtml(group, item) {
            const cfg = GROUP_ICONS[group];

            return `
                <a href="${esc(item.url)}" class="flex items-center gap-[13px] px-5 py-[11px] cursor-pointer hover:bg-mist">
                    <div class="w-[34px] h-[34px] rounded-[10px] flex items-center justify-center shrink-0" style="background:${cfg.bg}">
                        <i class="ti ${cfg.icon}" style="font-size:18px;color:${cfg.fg}"></i>
                    </div>
                    <div>
                        <div class="font-bn text-[14.5px] font-semibold">${esc(item.title)}</div>
                        <div class="font-bn text-[12.5px] text-[#8E979D] mt-0.5">${esc(item.excerpt)}</div>
                    </div>
                    <i class="ti ti-arrow-right ml-auto text-[#8E979D] text-base"></i>
                </a>
            `;
        },

        clear() {
            this.query = '';
            this.open = false;
            this.results = { ...EMPTY_RESULTS };
            this.$refs.input.focus();
        },

        goToResults() {
            const trimmed = this.query.trim();
            if (trimmed.length === 0) return;

            window.location.href = '/search?q=' + encodeURIComponent(trimmed);
        },
    };
}
