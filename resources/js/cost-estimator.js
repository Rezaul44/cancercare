import axios from 'axios';

export default function costEstimator(config = {}) {
    return {
        type: config.initialInputs?.type || 'breast',
        stage: String(config.initialInputs?.stage || '2'),
        dist: config.initialInputs?.dist || 'far',
        att: Number(config.initialInputs?.att || 2),
        loss: config.initialInputs?.loss || 'yes',
        hosp: config.initialInputs?.hosp || 'govt',
        treat: config.initialInputs?.treat || {
            surgery: true,
            chemo: true,
            radiation: true,
            targeted: false,
        },
        estimate: config.initialEstimate || null,
        loading: false,
        debounceTimer: null,

        init() {
            // Initial data is already injected server-side
        },

        setCancerType(val) {
            this.type = val;
            this.recalculate();
        },

        setStage(val) {
            this.stage = String(val);
            this.recalculate();
        },

        setDist(val) {
            this.dist = val;
            this.recalculate();
        },

        setAtt(val) {
            this.att = Number(val);
            this.recalculate();
        },

        setLoss(val) {
            this.loss = val;
            this.recalculate();
        },

        setHosp(val) {
            this.hosp = val;
            this.recalculate();
        },

        toggleTreat(key) {
            this.treat[key] = !this.treat[key];
            this.recalculate();
        },

        recalculate() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.fetchEstimate();
            }, 80);
        },

        async fetchEstimate() {
            this.loading = true;

            try {
                const payload = {
                    type: this.type,
                    stage: this.stage,
                    dist: this.dist,
                    att: this.att,
                    loss: this.loss,
                    hosp: this.hosp,
                    treat: this.treat,
                };

                const response = await axios.post('/ajax/cost-estimate', payload, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (response.data) {
                    this.estimate = response.data;
                }
            } catch (error) {
                console.error('Cost calculation failed:', error);
            } finally {
                this.loading = false;
            }
        },

        bnNum(num) {
            const bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
            return String(num).replace(/[0-9]/g, (w) => bnDigits[+w]);
        },

        fmt(n) {
            if (n === null || n === undefined || isNaN(n)) return '—';
            const rounded = Math.round(Number(n) / 1000) * 1000;
            const localized = rounded.toLocaleString('en-IN');
            return '৳' + this.bnNum(localized);
        },

        printEstimate() {
            window.print();
        },
    };
}
