@extends('layouts.field')

@section('title', 'রেটিং জমা দিন — মাঠকর্মী পোর্টাল')

@section('content')
<div
    x-data="{
        pendingCount: 0,
        submitting: false,
        submitted: false,
        errorMessage: null,
        doctorQuery: '',
        doctorResults: [],
        recentDoctors: JSON.parse(localStorage.getItem('field_recent_doctors') || '[]'),
        selectedDoctor: null,
        phone: '',
        answers: {},
        freeComment: '',
        photoFile: null,
        photoName: '',
        searchTimer: null,

        init() {
            this.refreshPendingCount();
            window.addEventListener('online', () => this.syncQueue());
        },

        searchDoctors() {
            clearTimeout(this.searchTimer);
            if (this.doctorQuery.trim().length < 2) { this.doctorResults = []; return; }
            this.searchTimer = setTimeout(async () => {
                try {
                    const res = await axios.get('{{ route('field.rating.doctors.search') }}', { params: { q: this.doctorQuery } });
                    this.doctorResults = res.data;
                } catch (e) { this.doctorResults = []; }
            }, 250);
        },

        pickDoctor(doctor) {
            this.selectedDoctor = doctor;
            this.doctorQuery = '';
            this.doctorResults = [];
        },

        rememberDoctor(doctor) {
            let list = this.recentDoctors.filter(d => d.id !== doctor.id);
            list.unshift(doctor);
            this.recentDoctors = list.slice(0, 5);
            localStorage.setItem('field_recent_doctors', JSON.stringify(this.recentDoctors));
        },

        onPhotoChange(event) {
            this.photoFile = event.target.files[0] || null;
            this.photoName = this.photoFile ? this.photoFile.name : '';
        },

        canSubmit() {
            return this.selectedDoctor && this.phone.length === 11 && Object.keys(this.answers).length === {{ $criteria->count() }};
        },

        async submitRating() {
            this.errorMessage = null;
            if (! this.canSubmit()) { this.errorMessage = 'ডাক্তার, ফোন নম্বর ও সবগুলো প্রশ্নের উত্তর দিন।'; return; }

            const payload = {
                doctor_id: this.selectedDoctor.id,
                doctor_name: this.selectedDoctor.name_bn,
                phone: this.phone,
                answers: this.answers,
                free_comment_bn: this.freeComment,
            };

            this.submitting = true;

            if (! navigator.onLine) {
                this.queueOffline(payload);
                this.submitting = false;
                return;
            }

            try {
                const formData = new FormData();
                formData.append('doctor_id', payload.doctor_id);
                formData.append('phone', payload.phone);
                formData.append('free_comment_bn', payload.free_comment_bn || '');
                Object.entries(payload.answers).forEach(([key, value]) => formData.append('answers[' + key + ']', value ? 1 : 0));
                if (this.photoFile) { formData.append('photo', this.photoFile); }

                await axios.post('{{ route('field.rating.store') }}', formData, {
                    headers: { 'Accept': 'application/json' },
                });

                this.rememberDoctor(this.selectedDoctor);
                this.resetForm();
                this.submitted = true;
            } catch (error) {
                if (error.response && error.response.status === 422 && error.response.data.message) {
                    this.errorMessage = error.response.data.message;
                } else {
                    // নেটওয়ার্ক ব্যর্থ হলে অফলাইন কিউতে রাখা হচ্ছে (ছবি ছাড়া)
                    this.queueOffline(payload);
                }
            } finally {
                this.submitting = false;
            }
        },

        queueOffline(payload) {
            const queue = JSON.parse(localStorage.getItem('field_rating_queue') || '[]');
            queue.push({ id: Date.now() + '-' + Math.random().toString(36).slice(2), ...payload });
            localStorage.setItem('field_rating_queue', JSON.stringify(queue));
            this.rememberDoctor(this.selectedDoctor);
            this.resetForm();
            this.refreshPendingCount();
            this.submitted = true;
        },

        async syncQueue() {
            let queue = JSON.parse(localStorage.getItem('field_rating_queue') || '[]');
            if (! queue.length) return;

            const remaining = [];
            for (const item of queue) {
                try {
                    await axios.post('{{ route('field.rating.store') }}', {
                        doctor_id: item.doctor_id,
                        phone: item.phone,
                        answers: item.answers,
                        free_comment_bn: item.free_comment_bn,
                    }, { headers: { 'Accept': 'application/json' } });
                } catch (e) {
                    remaining.push(item); // ব্যর্থ হলে (যেমন সেশন শেষ) কিউতে রেখে দেওয়া হলো
                }
            }
            localStorage.setItem('field_rating_queue', JSON.stringify(remaining));
            this.refreshPendingCount();
        },

        refreshPendingCount() {
            this.pendingCount = JSON.parse(localStorage.getItem('field_rating_queue') || '[]').length;
        },

        resetForm() {
            this.selectedDoctor = null;
            this.phone = '';
            this.answers = {};
            this.freeComment = '';
            this.photoFile = null;
            this.photoName = '';
        },

        newSubmission() { this.submitted = false; },
    }"
>
    <div class="flex items-center justify-between mb-5">
        <div class="font-serif text-[22px] font-medium"><span class="font-bn">নতুন রেটিং</span></div>
        <span x-show="pendingCount > 0" class="font-bn text-[12px] font-semibold px-3 py-1.5 rounded-full bg-[#FDF4E3] text-[#8A6416]">
            <span x-text="pendingCount"></span>টি সিঙ্কের অপেক্ষায়
        </span>
    </div>

    <div x-show="pendingCount > 0" class="mb-5">
        <button type="button" @click="syncQueue()" class="font-bn text-[13px] text-teal-700 underline">এখনই সিঙ্ক করুন</button>
    </div>

    <template x-if="submitted">
        <div class="bg-teal-50 border border-[#BFE5DC] rounded-[16px] px-6 py-8 text-center">
            <div class="font-bn text-[16px] font-semibold text-teal-700 mb-2">ধন্যবাদ! রেটিং জমা হয়েছে</div>
            <button type="button" @click="newSubmission()" class="font-bn mt-3 text-[14px] px-6 py-2.5 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700">আরেকটি জমা দিন</button>
        </div>
    </template>

    <div x-show="! submitted" class="bg-white border border-line rounded-[16px] px-6 py-6">
        <div x-show="errorMessage" class="mb-4 rounded-[11px] border border-red-200 bg-red-50 px-4 py-3">
            <p class="font-bn text-[13px] text-red-700" x-text="errorMessage"></p>
        </div>

        <template x-if="! selectedDoctor">
            <div class="mb-5">
                <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">ডাক্তার খুঁজুন</label>
                <input type="text" x-model="doctorQuery" @input="searchDoctors()" placeholder="নাম লিখুন..."
                    class="font-bn w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">

                <div x-show="doctorResults.length" class="mt-2 border border-line rounded-[11px] divide-y divide-line overflow-hidden">
                    <template x-for="doctor in doctorResults" :key="doctor.id">
                        <button type="button" @click="pickDoctor(doctor)" class="font-bn w-full text-left px-4 py-3 hover:bg-mist">
                            <div class="text-[14px] font-semibold" x-text="doctor.name_bn"></div>
                            <div class="text-[12px] text-slate-500" x-text="doctor.degrees_line_bn"></div>
                        </button>
                    </template>
                </div>

                <template x-if="recentDoctors.length && ! doctorQuery">
                    <div class="mt-4">
                        <div class="font-bn text-[11.5px] font-semibold text-slate-500 uppercase tracking-[0.07em] mb-2">সাম্প্রতিক</div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="doctor in recentDoctors" :key="doctor.id">
                                <button type="button" @click="pickDoctor(doctor)" class="font-bn text-[13px] px-3.5 py-2 rounded-full border border-line bg-white hover:border-slate-300" x-text="doctor.name_bn"></button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="selectedDoctor">
            <div class="mb-5 flex items-center justify-between rounded-[11px] border border-line bg-mist px-4 py-3">
                <div class="font-bn text-[14px] font-semibold" x-text="selectedDoctor.name_bn"></div>
                <button type="button" @click="selectedDoctor = null" class="font-bn text-[12.5px] text-slate-500 underline">বদলান</button>
            </div>
        </template>

        <div class="mb-5">
            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">রোগীর মোবাইল নম্বর</label>
            <input type="text" x-model="phone" maxlength="11" placeholder="01712345678"
                class="w-full px-4 py-3 border border-line rounded-[11px] text-[14.5px] text-ink bg-white focus:outline-none focus:border-slate-700">
        </div>

        <div class="mb-5 space-y-4">
            @foreach ($criteria as $criterion)
                <div>
                    <div class="font-bn text-[14px] font-medium mb-2">{{ $criterion->label_bn }}</div>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="answers['{{ $criterion->key }}'] = true"
                            :class="answers['{{ $criterion->key }}'] === true ? 'bg-teal-700 text-white border-teal-700' : 'bg-white text-slate-700 border-line'"
                            class="font-bn py-3.5 rounded-[11px] border text-[15px] font-semibold">হ্যাঁ</button>
                        <button type="button" @click="answers['{{ $criterion->key }}'] = false"
                            :class="answers['{{ $criterion->key }}'] === false ? 'bg-red-600 text-white border-red-600' : 'bg-white text-slate-700 border-line'"
                            class="font-bn py-3.5 rounded-[11px] border text-[15px] font-semibold">না</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mb-5" x-show="navigator.onLine">
            <label class="font-bn text-[12.5px] font-semibold text-slate-500 mb-2 block">প্রেসক্রিপশনের ছবি (ঐচ্ছিক)</label>
            <label class="relative flex flex-col items-center gap-1 rounded-[13px] border-2 border-dashed border-line bg-[#FCFCFB] p-5 text-center cursor-pointer hover:border-slate-300">
                <input type="file" accept="image/*" capture="environment" class="absolute inset-0 opacity-0 cursor-pointer" @change="onPhotoChange($event)">
                <i class="ti ti-camera text-xl text-slate-500"></i>
                <div class="font-bn text-sm font-semibold" x-text="photoName || 'ছবি তুলুন'"></div>
            </label>
            <p class="font-bn text-[11.5px] text-[#8E979D] mt-1.5">অফলাইনে জমা দিলে ছবি সংযুক্ত হবে না — পরে সংযোগ পেলে সিঙ্ক হবে শুধু তথ্য।</p>
        </div>

        <button type="button" @click="submitRating()" :disabled="submitting"
            class="font-bn w-full text-[15px] px-7 py-3.5 rounded-[9px] font-medium bg-slate-900 text-white hover:bg-slate-700 disabled:opacity-50">
            জমা দিন
        </button>
    </div>
</div>
@endsection
