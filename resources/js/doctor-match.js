/**
 * "এই ডাক্তার কি আমার জন্য" ম্যাচ ইঞ্জিন — docs/prototypes/doctor_profile.html-এর mc()/set() ফাংশনের
 * প্রায় সরাসরি পোর্ট (docs/CCB_system_documentation.md ধারা ১)। মূল পার্থক্য: prototype-এ ডেটা
 * client-side হার্ডকোড ছিল (const db = {...}); এখানে প্রতিটি নির্বাচনে
 * GET /ajax/doctors/{slug}/match?cancer=..&stage=..&treatment= থেকে আসে (DoctorProfileController::match)।
 *
 * note_bn/title টেক্সট CCB স্টাফ লেখে (doctor_cancer_type_stages/doctor_treatment_specialties, ডাক্তার
 * নিজে সম্পাদনা করতে পারেন না) — তবু innerHTML-এ বসানোর আগে esc() দিয়ে escape করা হয়, যাতে ভবিষ্যতে
 * ইনপুট সোর্স বদলালেও stored-XSS ঝুঁকি না থাকে।
 */
function esc(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';

    return div.innerHTML;
}

const STAGE_LABELS = { 1: 'স্টেজ ১', 2: 'স্টেজ ২', 3: 'স্টেজ ৩', 4: 'স্টেজ ৪' };

export default function doctorMatch(doctorSlug) {
    return {
        // $refs.cancer/stage/treatment (select elements) থেকে সরাসরি .value পড়া হয় — x-model ব্যবহার
        // করলে একই <select>-এ x-model আর @change দুটো লিসেনার race করে, মাঝেমধ্যে onChange() পুরনো
        // মান পড়ে ফেলত (একটা স্টেল/ফাঁকা ফলাফল দেখাত)। DOM value সবসময় change ইভেন্টের আগেই আপডেট
        // হয়ে যায়, তাই এভাবে পড়লে race থাকে না।
        async onChange() {
            this.cancer = this.$refs.cancer.value;
            this.stage = this.$refs.stage.value;
            this.treatment = this.$refs.treatment.value;

            if (!this.cancer && !this.stage && !this.treatment) {
                this.showEmpty();

                return;
            }

            const params = new URLSearchParams();
            if (this.cancer) params.set('cancer', this.cancer);
            if (this.stage) params.set('stage', this.stage);
            if (this.treatment) params.set('treatment', this.treatment);

            try {
                const response = await window.axios.get(`/ajax/doctors/${doctorSlug}/match?${params.toString()}`);
                this.render(response.data);
            } catch (error) {
                console.error('Match engine request failed:', error);
            }
        },

        showEmpty() {
            this.$refs.empty.style.display = 'block';
            this.$refs.result.style.display = 'none';
        },

        render(data) {
            this.$refs.empty.style.display = 'none';
            this.$refs.result.style.display = 'block';
            this.$refs.cta.style.display = 'none';
            this.$refs.grid.innerHTML = '';

            if (data.ok === null) {
                this.setCard('#F7F7F5', '#5A656D', 'ti-arrow-up', 'ক্যান্সারের ধরন বেছে নিন', 'ধরন নির্বাচন করলে বিস্তারিত ফলাফল দেখাবে।');

                return;
            }

            if (data.ok === 0) {
                const targetUrl = data.redirect_url || '/doctors';
                this.setCard('#FBE3E3', '#A32D2D', 'ti-x', 'এই ডাক্তার আপনার জন্য উপযুক্ত নন', data.message || 'এই ডাক্তার এই ধরনের ক্যান্সারে বিশেষজ্ঞ নন। সঠিক বিশেষজ্ঞ দেখানোই নিরাপদ।');
                this.$refs.cta.style.display = 'flex';
                this.$refs.cta.innerHTML = `<i class="ti ti-arrow-right"></i><span>আমাদের তালিকা থেকে এই ক্যান্সারের বিশেষজ্ঞ খুঁজুন — <a href="${esc(targetUrl)}" class="underline text-ink">ডাক্তার দেখুন</a></span>`;

                return;
            }

            if (data.ok === 2) {
                this.setCard('#FDF4E3', '#C98A1E', 'ti-alert-circle', 'নিশ্চিত করা যাচ্ছে না', 'ক্যান্সারের ধরন নির্দিষ্ট করে জানালে সঠিক ফলাফল বলা সম্ভব হবে।');

                return;
            }

            this.renderMatch(data);
        },

        renderMatch(data) {
            const rows = [];
            let partial = false;

            if (data.stage) {
                const isStage4 = this.stage === '4';
                if (isStage4) partial = true;

                rows.push({
                    icon: isStage4 ? 'ti-alert-circle' : 'ti-check',
                    bg: isStage4 ? '#FDF4E3' : '#DCF2ED',
                    fg: isStage4 ? '#C98A1E' : '#0B6E5C',
                    title: this.stage && STAGE_LABELS[this.stage]
                        ? `${STAGE_LABELS[this.stage]} — ${data.stage.case_count}টি কেস`
                        : `এই ক্যান্সারে ${data.stage.case_count}+ কেস`,
                    desc: data.stage.note_bn,
                });
            }

            if (data.treatment) {
                const refers = data.treatment.role === 'refers';
                if (refers) partial = true;

                rows.push({
                    icon: refers ? 'ti-alert-circle' : 'ti-check',
                    bg: refers ? '#FDF4E3' : '#DCF2ED',
                    fg: refers ? '#C98A1E' : '#0B6E5C',
                    title: 'চিকিৎসার ধরন',
                    desc: data.treatment.note_bn,
                });
            }

            let title, bg, fg, icon;
            if (!data.stage && !data.treatment) {
                [title, bg, fg, icon] = ['ক্যান্সারের ধরন মিলেছে', '#DCF2ED', '#0B6E5C', 'ti-check'];
            } else if (partial) {
                [title, bg, fg, icon] = ['ভালো মিল — কিছু বিষয় বিবেচনা করুন', '#FDF4E3', '#C98A1E', 'ti-alert-circle'];
            } else {
                [title, bg, fg, icon] = ['ভালো মিল — এই ডাক্তার আপনার জন্য উপযুক্ত', '#DCF2ED', '#0B6E5C', 'ti-check'];
            }

            this.setCard(bg, fg, icon, title, data.stage ? data.stage.note_bn : 'স্টেজ ও চিকিৎসার ধরন বেছে নিলে আরও নির্দিষ্ট ফলাফল দেখাবে।');

            this.$refs.grid.innerHTML = rows.map((row) => `
                <div class="flex gap-3 items-start px-4 py-3 border border-line rounded-xl">
                    <div class="w-6 h-6 rounded-[7px] flex items-center justify-center shrink-0 mt-0.5" style="background:${row.bg}">
                        <i class="ti ${row.icon}" style="font-size:13px;color:${row.fg}"></i>
                    </div>
                    <div>
                        <div class="font-bn text-[13px] font-semibold mb-0.5">${esc(row.title)}</div>
                        <div class="font-bn text-[12.5px] text-slate-500 leading-[1.55]">${esc(row.desc)}</div>
                    </div>
                </div>
            `).join('');

            if (partial && data.treatment && data.treatment.role === 'refers') {
                this.$refs.cta.style.display = 'flex';
                this.$refs.cta.innerHTML = '<i class="ti ti-users"></i><span>এই চিকিৎসার জন্য আরেকজন বিশেষজ্ঞও লাগবে — <a href="/doctors" class="underline text-ink">অন্য বিশেষজ্ঞ দেখুন</a></span>';
            }
        },

        setCard(bg, fg, icon, title, desc) {
            this.$refs.iconWrap.style.background = bg;
            this.$refs.icon.className = 'ti ' + icon;
            this.$refs.icon.style.color = fg;
            this.$refs.title.textContent = title;
            this.$refs.title.style.color = fg;
            this.$refs.desc.textContent = desc;
        },
    };
}
