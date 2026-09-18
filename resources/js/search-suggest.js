/**
 * হোমপেজের সার্চ বক্সের ড্রপডাউন — অনকোলজিস্ট ডাক্তার অনুসন্ধানের জন্য ডেডিকেটেড।
 * GET /ajax/search/suggest?type=doctors&q=... থেকে আসা ফলাফল দেখায়।
 * XSS প্রতিরোধে esc() এস্কেপ বাধ্যতামূলক।
 */
function esc(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';

    return div.innerHTML;
}

function toBnNum(num) {
    if (!num) return '';
    const en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    const bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
    return String(num).replace(/[0-9]/g, (d) => bn[d]);
}

export default function searchSuggest() {
    return {
        query: '',
        focused: false,
        open: false,
        loading: false,
        doctors: [],
        debounceTimer: null,
        placeholders: [
            'ডাক্তারের নাম বা ক্যান্সার লিখে খুঁজুন...',
            'যেমন: ডা. কামাল বা সার্জিক্যাল অনকোলজিস্ট',
            'স্তন ক্যান্সারের বিশেষজ্ঞ ডাক্তার...',
            'ফুসফুস বা রক্তের ক্যান্সার বিশেষজ্ঞ...',
            'হেমাটোলজি বা রেডিয়েশন অনকোলজিস্ট...',
        ],
        placeholderIndex: 0,

        init() {
            setInterval(() => {
                if (!this.focused && this.query === '') {
                    this.placeholderIndex = (this.placeholderIndex + 1) % this.placeholders.length;
                }
            }, 3400);
        },

        onInput() {
            const trimmed = this.query.trim();
            this.open = trimmed.length > 0;
            clearTimeout(this.debounceTimer);

            if (trimmed.length < 1) {
                this.doctors = [];
                this.renderDoctors();
                return;
            }

            this.debounceTimer = setTimeout(() => this.fetchSuggestions(), 220);
        },

        async fetchSuggestions() {
            const trimmed = this.query.trim();
            if (trimmed.length < 1) return;

            this.loading = true;
            try {
                const response = await window.axios.get('/ajax/search/suggest', {
                    params: {
                        q: trimmed,
                        type: 'doctors',
                    },
                });

                this.doctors = Array.isArray(response.data) ? response.data : [];
            } catch (error) {
                console.error('Doctor search suggest failed:', error);
                this.doctors = [];
            } finally {
                this.loading = false;
                this.renderDoctors();
            }
        },

        renderDoctors() {
            const ref = this.$refs.doctorsList;
            if (!ref) return;

            ref.innerHTML = this.doctors.map((item) => this.itemHtml(item)).join('');
        },

        itemHtml(item) {
            const photoImg = item.photo_url
                ? `<img src="${esc(item.photo_url)}" alt="${esc(item.title)}" class="w-11 h-11 rounded-full object-cover border border-line shrink-0" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                   <div class="w-11 h-11 rounded-full bg-pink-100 text-pink-700 items-center justify-center shrink-0" style="display:none"><i class="ti ti-stethoscope text-xl"></i></div>`
                : `<div class="w-11 h-11 rounded-full bg-pink-100 text-pink-700 flex items-center justify-center shrink-0"><i class="ti ti-stethoscope text-xl"></i></div>`;

            const chamberParts = [item.chamber_name, item.district].filter(Boolean).map(esc);
            const chamberStr = chamberParts.length > 0 ? chamberParts.join(' · ') : '';
            const feeStr = item.fee ? `· ৳${toBnNum(item.fee)}` : '';

            return `
                <a href="${esc(item.url)}" class="flex items-center gap-3.5 px-5 py-3 cursor-pointer hover:bg-mist transition group">
                    <div class="relative shrink-0">
                        ${photoImg}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-bn text-[14.5px] font-semibold text-ink group-hover:text-pink-600 transition leading-snug">${esc(item.title)}</span>
                            ${item.name_en ? `<span class="font-bn text-[12px] text-slate-400 font-normal">(${esc(item.name_en)})</span>` : ''}
                        </div>
                        <div class="font-bn text-[12px] text-pink-600 font-medium truncate mt-0.5">${esc(item.specialties || item.current_position || 'অনকোলজিস্ট')}</div>
                        ${chamberStr ? `
                            <div class="font-bn text-[11.5px] text-slate-400 flex items-center gap-1 mt-0.5 truncate">
                                <i class="ti ti-map-pin text-[12px]"></i>
                                <span>${chamberStr} ${feeStr}</span>
                            </div>
                        ` : ''}
                    </div>
                    <div class="shrink-0 text-slate-300 group-hover:text-pink-600 transition">
                        <i class="ti ti-chevron-right text-lg"></i>
                    </div>
                </a>
            `;
        },

        clear() {
            this.query = '';
            this.open = false;
            this.doctors = [];
            if (this.$refs.doctorsList) {
                this.$refs.doctorsList.innerHTML = '';
            }
            this.$refs.input.focus();
        },

        goToResults() {
            const trimmed = this.query.trim();
            if (trimmed.length === 0) return;

            window.location.href = '/doctors?q=' + encodeURIComponent(trimmed);
        },
    };
}
