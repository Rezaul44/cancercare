import axios from 'axios';

export default function hospitalDirectory(config = {}) {
    return {
        baseUrl: config.baseUrl || '/hospitals',
        selectedCapabilities: config.capabilities || [],
        selectedTypes: config.types || [],
        selectedDivisions: config.divisions || [],
        selectedFacilities: config.facilities || [],
        districtId: config.districtId || null,
        sort: config.sort || 'relevance',
        loading: false,

        init() {
            this.bindResultsListeners();

            window.addEventListener('popstate', () => {
                const urlParams = new URLSearchParams(window.location.search);
                this.selectedCapabilities = urlParams.getAll('capabilities[]');
                this.selectedTypes = urlParams.getAll('types[]');
                this.selectedDivisions = urlParams.getAll('division_ids[]').map(Number);
                this.selectedFacilities = urlParams.getAll('facilities[]');
                this.sort = urlParams.get('sort') || 'relevance';
                this.fetchAndSwap(window.location.href, false);
            });
        },

        setSort(sortVal) {
            this.sort = sortVal;
            this.updateFilters();
        },

        clearFilters() {
            this.selectedCapabilities = [];
            this.selectedTypes = [];
            this.selectedDivisions = [];
            this.selectedFacilities = [];
            this.districtId = null;
            this.sort = 'relevance';
            this.updateFilters();
        },

        updateFilters() {
            const params = new URLSearchParams();

            this.selectedCapabilities.forEach((cap) => {
                params.append('capabilities[]', cap);
            });

            this.selectedTypes.forEach((type) => {
                params.append('types[]', type);
            });

            this.selectedDivisions.forEach((divId) => {
                params.append('division_ids[]', divId);
            });

            this.selectedFacilities.forEach((fac) => {
                params.append('facilities[]', fac);
            });

            if (this.districtId) {
                params.append('district_id', this.districtId);
            }

            if (this.sort && this.sort !== 'relevance') {
                params.append('sort', this.sort);
            }

            const queryString = params.toString();
            const targetUrl = queryString ? `${this.baseUrl}?${queryString}` : this.baseUrl;

            this.fetchAndSwap(targetUrl, true);
        },

        async fetchAndSwap(targetUrl, pushHistory = true) {
            this.loading = true;

            try {
                const response = await axios.get(targetUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                });

                const resultsContainer = this.$refs.results;
                if (resultsContainer) {
                    let htmlToInsert = response.data;

                    if (typeof htmlToInsert === 'string' && htmlToInsert.includes('<!DOCTYPE html>')) {
                        const doc = new DOMParser().parseFromString(htmlToInsert, 'text/html');
                        const parsedResults = doc.querySelector('[x-ref="results"]');
                        if (parsedResults) {
                            htmlToInsert = parsedResults.innerHTML;
                        }
                    }

                    resultsContainer.innerHTML = htmlToInsert;
                    this.bindResultsListeners();
                }

                if (pushHistory) {
                    window.history.pushState({}, '', targetUrl);
                }
            } catch (error) {
                console.error('Failed to load hospitals:', error);
                window.location.href = targetUrl;
            } finally {
                this.loading = false;
            }
        },

        bindResultsListeners() {
            const resultsContainer = this.$refs.results;
            if (!resultsContainer) return;

            // Handle pagination links
            resultsContainer.querySelectorAll('nav[role="navigation"] a').forEach((link) => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.fetchAndSwap(link.href, true);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        },
    };
}
