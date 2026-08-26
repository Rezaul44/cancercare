/**
 * /doctors পাতার ফিল্টার/সর্ট/পেজিনেশন — axios দিয়ে শুধু results অংশ replace করে,
 * তারপর history.pushState() দিয়ে URL বদলায় (SEO/deep-link এর জন্য বাধ্যতামূলক, দেখুন docs/CLAUDE.md)।
 *
 * ফলাফলের partial (_results.blade.php)-এ কোনো Alpine directive থাকে না — Alpine innerHTML দিয়ে বসানো
 * HTML নিজে থেকে স্ক্যান করে না, তাই swap-এর পর এখানে plain addEventListener দিয়ে আবার বাইন্ড করা হয়।
 * JS বন্ধ থাকলে ফর্ম/লিংক স্বাভাবিক GET হিসেবেই কাজ করে (progressive enhancement)।
 */
export default function doctorDirectory() {
    return {
        loading: false,

        init() {
            this.bindResultsListeners();
            window.addEventListener('popstate', () => {
                this.syncFormFromUrl(window.location.href);
                this.fetchAndSwap(window.location.href, false);
            });
        },

        handleFormChange() {
            this.submitFilters();
        },

        setSort(sortValue) {
            if (this.$refs.sortInput) {
                this.$refs.sortInput.value = sortValue;
            }
            this.submitFilters();
        },

        clearFilters() {
            const form = this.$refs.form;
            if (!form) return;

            form.querySelectorAll('select').forEach((select) => {
                select.value = '';
            });

            form.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                checkbox.checked = false;
            });

            if (this.$refs.sortInput) {
                this.$refs.sortInput.value = 'relevance';
            }

            this.submitFilters();
        },

        submitFilters(event) {
            const form = this.$refs.form;
            if (!form) return;

            const formData = new FormData(form, event?.submitter ?? null);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value !== '' && value !== null && value !== undefined) {
                    if (key === 'sort' && value === 'relevance') {
                        continue;
                    }
                    params.append(key, value);
                }
            }

            const queryString = params.toString();
            const url = form.action + (queryString ? '?' + queryString : '');

            this.fetchAndSwap(url, true);
        },

        fetchAndSwap(url, pushHistory) {
            this.loading = true;

            window.axios.get(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, application/xhtml+xml',
                }
            })
            .then((response) => {
                if (this.$refs.results) {
                    let html = response.data;

                    // Safeguard: if response contains full HTML document (e.g. from browser cache), extract only the results section
                    if (typeof html === 'string' && (html.includes('<!DOCTYPE') || html.includes('<html') || html.includes('<body'))) {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        const resultsEl = doc.querySelector('[x-ref="results"]');
                        if (resultsEl) {
                            html = resultsEl.innerHTML;
                        }
                    }

                    this.$refs.results.innerHTML = html;
                    this.bindResultsListeners();
                }

                if (pushHistory) {
                    window.history.pushState(null, '', url);
                }

                const currentUrl = new URL(url, window.location.origin);
                const sortParam = currentUrl.searchParams.get('sort') || 'relevance';
                if (this.$refs.sortInput) {
                    this.$refs.sortInput.value = sortParam;
                }
            })
            .catch((error) => {
                console.error('Failed to load doctor directory results:', error);
            })
            .finally(() => {
                this.loading = false;
            });
        },

        bindResultsListeners() {
            const results = this.$refs.results;
            if (!results) return;

            results.querySelectorAll('[data-sort]').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    const sortValue = button.getAttribute('data-sort');
                    this.setSort(sortValue);
                });
            });

            results.querySelectorAll('[data-pagination] a').forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    this.fetchAndSwap(link.href, true);
                    if (this.$refs.root) {
                        this.$refs.root.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        },

        syncFormFromUrl(url) {
            const form = this.$refs.form;
            if (!form) return;

            const parsed = new URL(url, window.location.origin);
            const params = parsed.searchParams;

            const cancer = params.get('cancer') || '';
            const district = params.get('district') || '';
            const sort = params.get('sort') || 'relevance';

            const cancerSelect = form.querySelector('select[name="cancer"]');
            if (cancerSelect) cancerSelect.value = cancer;

            const districtSelect = form.querySelector('select[name="district"]');
            if (districtSelect) districtSelect.value = district;

            if (this.$refs.sortInput) {
                this.$refs.sortInput.value = sort;
            }

            const doctorTypes = params.getAll('doctor_type[]');
            form.querySelectorAll('input[name="doctor_type[]"]').forEach((cb) => {
                cb.checked = doctorTypes.includes(cb.value);
            });

            const hospitalTypes = params.getAll('hospital_type[]');
            form.querySelectorAll('input[name="hospital_type[]"]').forEach((cb) => {
                cb.checked = hospitalTypes.includes(cb.value);
            });

            const feeBuckets = params.getAll('fee[]');
            form.querySelectorAll('input[name="fee[]"]').forEach((cb) => {
                cb.checked = feeBuckets.includes(cb.value);
            });

            const genders = params.getAll('gender[]');
            form.querySelectorAll('input[name="gender[]"]').forEach((cb) => {
                cb.checked = genders.includes(cb.value);
            });

            const facilities = params.getAll('facility[]');
            form.querySelectorAll('input[name="facility[]"]').forEach((cb) => {
                cb.checked = facilities.includes(cb.value);
            });
        }
    };
}
