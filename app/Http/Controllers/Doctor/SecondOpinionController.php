<?php

namespace App\Http\Controllers\Doctor;

use App\Enums\SecondOpinionStatus;
use App\Http\Controllers\Controller;
use App\Models\SecondOpinionRequest;
use App\Services\FileVaultService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecondOpinionController extends Controller
{
    public function index(): View
    {
        $doctor = Auth::user()->doctor;

        $requests = $doctor->secondOpinionRequests()
            ->whereIn('status', [SecondOpinionStatus::Submitted, SecondOpinionStatus::Accepted, SecondOpinionStatus::Answered])
            ->with('cancerType', 'district')
            ->latest()
            ->paginate(15);

        return view('doctor.second-opinions.index', ['requests' => $requests]);
    }

    public function show(SecondOpinionRequest $secondOpinionRequest, FileVaultService $fileVault): View
    {
        $this->authorizeOwnership($secondOpinionRequest);

        $secondOpinionRequest->load('cancerType', 'district', 'files', 'response');

        $fileUrls = $secondOpinionRequest->files->mapWithKeys(fn ($file) => [
            $file->id => $fileVault->secondOpinionFileUrl(Auth::user(), $file),
        ]);

        return view('doctor.second-opinions.show', [
            'request' => $secondOpinionRequest,
            'fileUrls' => $fileUrls,
        ]);
    }

    public function respond(Request $request, SecondOpinionRequest $secondOpinionRequest): RedirectResponse
    {
        $this->authorizeOwnership($secondOpinionRequest);

        abort_if($secondOpinionRequest->response()->exists(), 422, 'ইতিমধ্যে উত্তর দেওয়া হয়েছে।');

        $validated = $request->validate([
            'response_bn' => ['required', 'string', 'max:5000'],
            'call_made' => ['nullable', 'boolean'],
            'call_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $secondOpinionRequest->response()->create([
            'doctor_id' => $secondOpinionRequest->doctor_id,
            'response_bn' => $validated['response_bn'],
            'call_made' => (bool) ($validated['call_made'] ?? false),
            'call_note' => $validated['call_note'] ?? null,
            'answered_at' => now(),
        ]);

        $secondOpinionRequest->update([
            'status' => SecondOpinionStatus::Answered,
            'answered_at' => now(),
        ]);

        return redirect()->route('doctor.second-opinions.index')->with('status', 'উত্তর জমা হয়েছে।');
    }

    private function authorizeOwnership(SecondOpinionRequest $secondOpinionRequest): void
    {
        abort_unless(
            $secondOpinionRequest->doctor_id === Auth::user()->doctor->id,
            403,
        );
    }
}
