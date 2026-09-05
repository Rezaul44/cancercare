<?php

namespace App\Http\Controllers;

use App\Enums\HospitalStatus;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalProfileController extends Controller
{
    public function show(Request $request, Hospital $hospital): View
    {
        if ($hospital->status !== HospitalStatus::Published && ! $request->user()?->can('hospitals.view')) {
            abort(404);
        }

        $hospital->load([
            'district.division',
            'capabilities' => fn ($q) => $q->with('capability')->join('capabilities', 'hospital_capabilities.capability_id', '=', 'capabilities.id')->orderBy('capabilities.sort_order')->select('hospital_capabilities.*'),
            'waitTimes' => fn ($q) => $q->orderBy('sort_order'),
            'costs' => fn ($q) => $q->orderBy('sort_order'),
            'prepInfos' => fn ($q) => $q->orderBy('sort_order'),
            'practicalInfos' => fn ($q) => $q->orderBy('sort_order'),
            'videos' => fn ($q) => $q->orderBy('sort_order'),
            'doctors' => fn ($q) => $q->published()->with('doctorTypes')->orderBy('hospital_doctor.sort_order'),
            'experienceSummaries' => fn ($q) => $q->with('question')->join('hospital_experience_questions', 'hospital_experience_summaries.question_id', '=', 'hospital_experience_questions.id')->orderBy('hospital_experience_questions.sort_order')->select('hospital_experience_summaries.*'),
        ]);

        return view('pages.hospitals.show', [
            'hospital' => $hospital,
        ]);
    }
}
