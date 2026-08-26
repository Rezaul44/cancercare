@php
    $schemas = [];

    // Global Medical Organization Schema
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'MedicalOrganization',
        'name' => 'CancerCare Bangladesh',
        'url' => url('/'),
        'logo' => asset('images/logo.png'),
        'description' => 'বাংলাদেশে ক্যান্সার চিকিৎসায় বিশ্বস্ত পথপ্রদর্শক — নিরপেক্ষ তথ্য, বিশেষজ্ঞ ডাক্তার ও হাসপাতাল ডিরেক্টরি।',
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => '+8809611777888',
            'contactType' => 'customer service',
            'availableLanguage' => ['Bengali', 'English'],
        ],
    ];

    // Global Website Schema with SearchAction
    $schemas[] = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'CancerCare Bangladesh',
        'url' => url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => url('/guide').'?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];

    // 1. Doctor Profile Schema (Physician)
    if (isset($doctor) && $doctor instanceof \App\Models\Doctor) {
        $doctorSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Physician',
            'name' => $doctor->name_bn,
            'image' => $doctor->photo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($doctor->photo_path) : null,
            'url' => route('doctors.show', $doctor->slug),
            'medicalSpecialty' => $doctor->doctorTypes->pluck('label_en')->filter()->values(),
            'description' => trim(($doctor->current_position_bn ?? '').' · '.($doctor->degrees_line_bn ?? ''), ' ·'),
            'address' => $doctor->chambers->map(fn ($chamber) => [
                '@type' => 'MedicalClinic',
                'name' => $chamber->name_bn,
                'address' => $chamber->address_bn,
            ])->values(),
        ];

        if ($doctor->ratingSummary && $doctor->ratingSummary->is_published) {
            $doctorSchema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $doctor->ratingSummary->overall_score,
                'reviewCount' => $doctor->ratingSummary->total_count,
                'bestRating' => '5',
            ];
        }

        $schemas[] = array_filter($doctorSchema);
    }

    // 2. Guide Detail Schema (MedicalWebPage, FAQPage, DefinedTermSet)
    if (isset($guide) && $guide instanceof \App\Models\Guide) {
        $medicalWebPage = [
            '@context' => 'https://schema.org',
            '@type' => 'MedicalWebPage',
            'name' => $guide->title_bn,
            'headline' => $guide->title_bn,
            'description' => $guide->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($guide->intro_bn), 160),
            'url' => route('guides.show', $cancerType->slug ?? ($guide->cancerType->slug ?? '')),
            'inLanguage' => 'bn-BD',
            'about' => [
                '@type' => 'MedicalCondition',
                'name' => ($cancerType->name_bn ?? ($guide->cancerType->name_bn ?? '')).' ক্যান্সার',
                'alternateName' => $cancerType->name_en ?? ($guide->cancerType->name_en ?? ''),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'CancerCare Bangladesh',
                'url' => url('/'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png'),
                ],
            ],
        ];

        if ($guide->published_at) {
            $medicalWebPage['datePublished'] = $guide->published_at->toIso8601String();
        }

        if ($guide->last_updated_at) {
            $medicalWebPage['dateModified'] = $guide->last_updated_at->toIso8601String();
        }

        if ($guide->reviewedByDoctor) {
            $medicalWebPage['reviewedBy'] = [
                '@type' => 'Physician',
                'name' => $guide->reviewedByDoctor->name_bn,
                'jobTitle' => $guide->reviewedByDoctor->current_position_bn,
                'medicalSpecialty' => $guide->reviewedByDoctor->doctorTypes->pluck('label_en')->filter()->values(),
            ];
        }

        $schemas[] = array_filter($medicalWebPage);

        // Guide FAQPage Schema
        if ($guide->faqs && $guide->faqs->isNotEmpty()) {
            $faqEntities = $guide->faqs->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq->question_bn,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq->answer_bn,
                ],
            ])->values()->all();

            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqEntities,
            ];
        }

        // Guide DefinedTermSet for Report Terms
        if ($guide->terms && $guide->terms->isNotEmpty()) {
            $termEntities = $guide->terms->map(fn ($term) => [
                '@type' => 'DefinedTerm',
                'termCode' => $term->code,
                'name' => $term->code.($term->hint_bn ? ' ('.$term->hint_bn.')' : ''),
                'description' => $term->plain_explanation_bn,
                'url' => route('guides.show', $cancerType->slug ?? ($guide->cancerType->slug ?? '')).'#'.$term->slug,
            ])->values()->all();

            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'DefinedTermSet',
                'name' => ($cancerType->name_bn ?? ($guide->cancerType->name_bn ?? '')).' ক্যান্সার রিপোর্ট ডিকোডার টার্মস',
                'hasDefinedTerm' => $termEntities,
            ];
        }
    }
@endphp

@foreach ($schemas as $schemaItem)
    <script type="application/ld+json">
        {!! json_encode($schemaItem, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endforeach
