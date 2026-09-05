<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\RatingCriteria;
use App\Services\RatingSubmissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingSubmissionController extends Controller
{
    public function create(): View
    {
        return view('field.rating.create', [
            'criteria' => RatingCriteria::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function searchDoctors(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        $doctors = Doctor::query()
            ->published()
            ->when($query !== '', fn ($q) => $q->where('name_bn', 'like', "%{$query}%"))
            ->orderBy('name_bn')
            ->limit(15)
            ->get(['id', 'name_bn', 'degrees_line_bn']);

        return response()->json($doctors);
    }

    public function store(Request $request, RatingSubmissionService $service): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'phone' => ['required', 'string', 'regex:/^01[0-9]{9}$/'],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*' => ['boolean'],
            'free_comment_bn' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $doctor = Doctor::findOrFail($validated['doctor_id']);

        try {
            $service->submit($validated, $doctor, Auth::user(), $request->file('photo'));
        } catch (\DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['phone' => $e->getMessage()])->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'রেটিং জমা হয়েছে।'], 201);
        }

        return redirect()->route('field.rating.create')->with('status', 'রেটিং জমা হয়েছে — ধন্যবাদ।');
    }
}
