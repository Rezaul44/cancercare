<?php

namespace App\Http\Controllers;

use App\Enums\ChamberType;
use App\Models\CancerType;
use App\Models\District;
use App\Models\Doctor;
use App\Models\DoctorType;
use App\Services\DoctorRankingService;
use App\Support\DoctorReasonTags;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorDirectoryController extends Controller
{
    /**
     * @var list<string>
     */
    private const SORTS = ['relevance', 'nearest', 'fee', 'rating', 'wait'];

    public function index(Request $request, DoctorRankingService $rankingService): View
    {
        $filters = $this->resolveFilters($request);
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'relevance';

        $doctors = $rankingService->rank($filters, $sort, 20);
        $doctors = $doctors->withQueryString();

        $doctors->getCollection()->transform(function (Doctor $doctor) use ($filters) {
            $doctor->reason_tags = DoctorReasonTags::for($doctor, $filters);

            return $doctor;
        });

        $data = [
            'doctors' => $doctors,
            'sort' => $sort,
            'filters' => $filters,
            'doctorTypes' => DoctorType::orderBy('label_bn')->get(),
            'cancerTypes' => CancerType::orderBy('sort_order')->get(),
            'districts' => District::orderBy('name_bn')->get(),
            'totalPublishedDoctors' => Doctor::published()->count(),
        ];

        if ($request->ajax()) {
            return view('pages.doctors._results', $data);
        }

        return view('pages.doctors.index', $data);
    }

    /**
     * ভুল/পুরনো লিংক থেকে আসা অপ্রত্যাশিত মান নীরবে বাদ দেওয়া হয় — এটা একটা পাবলিক SEO পাতা,
     * ভুল query string-এ error দেখানো যাবে না।
     *
     * @return array{
     *     cancer_type_id: int|null,
     *     district_id: int|null,
     *     doctor_type_ids: list<int>,
     *     chamber_types: list<string>,
     *     fee_buckets: list<string>,
     *     gender: list<string>,
     *     facilities: list<string>,
     * }
     */
    private function resolveFilters(Request $request): array
    {
        $cancerSlug = (string) $request->query('cancer', '');
        $districtSlug = (string) $request->query('district', '');

        $doctorTypeKeys = array_filter((array) $request->query('doctor_type', []));

        $chamberTypes = array_values(array_intersect(
            (array) $request->query('hospital_type', []),
            array_column(ChamberType::cases(), 'value')
        ));

        $feeBuckets = array_values(array_intersect(
            (array) $request->query('fee', []),
            array_keys(DoctorRankingService::FEE_BUCKETS)
        ));

        $genders = array_values(array_intersect(
            (array) $request->query('gender', []),
            ['male', 'female']
        ));

        $facilities = array_values(array_intersect(
            (array) $request->query('facility', []),
            ['whatsapp', 'second_opinion']
        ));

        return [
            'cancer_type_id' => $cancerSlug !== '' ? CancerType::where('slug', $cancerSlug)->value('id') : null,
            'district_id' => $districtSlug !== '' ? District::where('slug', $districtSlug)->value('id') : null,
            'doctor_type_ids' => $doctorTypeKeys === [] ? [] : DoctorType::whereIn('key', $doctorTypeKeys)->pluck('id')->all(),
            'chamber_types' => $chamberTypes,
            'fee_buckets' => $feeBuckets,
            'gender' => $genders,
            'facilities' => $facilities,
            // ফর্মের select/checkbox-এ আগের নির্বাচন আবার দেখানোর জন্য raw মান —
            // DoctorRankingService এই কী-গুলো পড়ে না, নিরাপদে উপেক্ষা করে।
            'cancer_slug' => $cancerSlug,
            'district_slug' => $districtSlug,
            'doctor_type_keys' => $doctorTypeKeys,
        ];
    }
}
