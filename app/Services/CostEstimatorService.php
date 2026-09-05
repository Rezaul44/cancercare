<?php

namespace App\Services;

use App\Models\CancerType;
use App\Models\CostBaseRate;
use App\Models\CostIndirectRate;
use App\Models\CostMultiplier;
use App\Models\CostPhaseTemplate;
use Illuminate\Support\Facades\Cache;

class CostEstimatorService
{
    /**
     * @param array{
     *     type: string,
     *     stage: string|int,
     *     dist: string,
     *     att: int|string,
     *     loss: string,
     *     hosp: string,
     *     treat: array{surgery?: bool, chemo?: bool, radiation?: bool, targeted?: bool}
     * } $inputs
     * @return array<string, mixed>
     */
    public function estimate(array $inputs): array
    {
        $cancerTypeKey = $inputs['type'] ?? 'breast';
        $stage = (string) ($inputs['stage'] ?? '2');
        $dist = $inputs['dist'] ?? 'far';
        $attendants = (int) ($inputs['att'] ?? 2);
        $loss = $inputs['loss'] ?? 'yes';
        $selectedHosp = $inputs['hosp'] ?? 'govt';

        $treat = array_merge([
            'surgery' => true,
            'chemo' => true,
            'radiation' => true,
            'targeted' => false,
        ], $inputs['treat'] ?? []);

        // Load rates from DB / cache
        $rates = $this->loadRates();

        // 1. Resolve cancer type
        $cancerTypeSlug = match ($cancerTypeKey) {
            'breast' => 'breast-cancer',
            'lung' => 'lung-cancer',
            'cervical' => 'cervical-cancer',
            'blood' => 'blood-cancer',
            'stomach' => 'stomach-cancer',
            'oral' => 'oral-cancer',
            'colon' => 'colon-cancer',
            'prostate' => 'prostate-cancer',
            default => str_contains($cancerTypeKey, '-cancer') ? $cancerTypeKey : "{$cancerTypeKey}-cancer",
        };

        $base = $rates['base_rates'][$cancerTypeSlug] ?? $rates['base_rates']['breast-cancer'];
        $stageMul = $rates['stage_multipliers'][$stage] ?? 1.00;
        $distMul = $rates['dist_multipliers'][$dist] ?? 1.00;
        $hospMul = $rates['hosp_multipliers'];

        // Stage duration multiplier
        $stageDurMul = match ($stage) {
            '1' => 0.8,
            '3' => 1.15,
            '4' => 1.25,
            default => 1.0,
        };
        $months = (int) round($base['months'] * $stageDurMul);
        $trips = ($treat['chemo'] ?? false) ? ($months * 2.2) : ($months * 1.2);

        // Calculate direct costs for all 3 hospital tiers
        $directGovt = $this->calcDirectCost($base, $treat, $stageMul, $hospMul['govt']);
        $directNpo = $this->calcDirectCost($base, $treat, $stageMul, $hospMul['npo']);
        $directPriv = $this->calcDirectCost($base, $treat, $stageMul, $hospMul['priv'] ?? $hospMul['private'] ?? 4.4);

        // Indirect costs helper for a given direct cost
        $calcIndirect = function (float $directAmount) use ($rates, $trips, $distMul, $dist, $attendants, $loss, $months) {
            $travelBase = $rates['indirect_rates']['travel']['base_amount'] ?? 1400;
            $stayBase = $rates['indirect_rates']['stay']['base_amount'] ?? 900;
            $foodBase = $rates['indirect_rates']['food']['base_amount'] ?? 350;
            $medsPercent = $rates['indirect_rates']['outside_medicine']['percent_value'] ?? 0.18;
            $incomeFar = $rates['indirect_rates']['income_loss_far']['base_amount'] ?? 9000;
            $incomeNear = $rates['indirect_rates']['income_loss_near']['base_amount'] ?? 6000;
            $miscBase = $rates['indirect_rates']['misc']['base_amount'] ?? 400;

            $travel = $trips * ($distMul * $travelBase) * ($attendants > 1 ? 1.6 : 1.0);
            $stay = ($dist === 'local') ? 0 : $trips * ($distMul * $stayBase) * 0.8;
            $food = $trips * ($attendants * $foodBase);
            $meds = $directAmount * $medsPercent;
            $lostIncome = ($loss === 'yes') ? $months * ($distMul > 0.5 ? $incomeFar : $incomeNear) : 0;
            $misc = $trips * $miscBase;

            $total = $travel + $stay + $food + $meds + $lostIncome + $misc;

            return [
                'travel' => round($travel),
                'stay' => round($stay),
                'food' => round($food),
                'meds' => round($meds),
                'lostIncome' => round($lostIncome),
                'misc' => round($misc),
                'total' => round($total),
            ];
        };

        $indGovt = $calcIndirect($directGovt);
        $indNpo = $calcIndirect($directNpo);
        $indPriv = $calcIndirect($directPriv);

        // Selected hospital figures
        $selectedDirect = match ($selectedHosp) {
            'npo' => $directNpo,
            'priv', 'private' => $directPriv,
            default => $directGovt,
        };
        $selectedInd = match ($selectedHosp) {
            'npo' => $indNpo,
            'priv', 'private' => $indPriv,
            default => $indGovt,
        };

        $totalEstimated = $selectedDirect + $selectedInd['total'];
        $minRange = round($totalEstimated * 0.85);
        $maxRange = round($totalEstimated * 1.35);
        $monthlyAvg = round($totalEstimated / max($months, 1));
        $directPercent = (int) round(($selectedDirect / max($totalEstimated, 1)) * 100);
        $indirectPercent = 100 - $directPercent;

        // 2. Build Phase Breakdown
        $currentHospMul = $hospMul[$selectedHosp] ?? ($selectedHosp === 'priv' ? ($hospMul['priv'] ?? 4.4) : 1.0);
        $phases = $this->buildPhases($base, $treat, $stageMul, $currentHospMul, $rates['phase_templates']);

        // 3. Build Monthly Cash Flow
        $monthlyFlow = $this->buildMonthlyFlow($selectedDirect, $selectedInd['total'], $months);

        // 4. Comparison Cards
        $comparisons = [
            'govt' => [
                'name_bn' => 'সরকারি',
                'badge_bn' => 'সবচেয়ে সাশ্রয়ী',
                'badge_class' => 'cb-g',
                'amount' => round($directGovt + $indGovt['total']),
                'desc_bn' => 'NICRH, মেডিকেল কলেজ · অপেক্ষা ৮–১২ সপ্তাহ',
            ],
            'npo' => [
                'name_bn' => 'অলাভজনক',
                'badge_bn' => 'মাঝামাঝি',
                'badge_class' => 'cb-n',
                'amount' => round($directNpo + $indNpo['total']),
                'desc_bn' => 'ট্রাস্ট ও দাতব্য হাসপাতাল · অপেক্ষা ৩–৫ সপ্তাহ',
            ],
            'priv' => [
                'name_bn' => 'বেসরকারি',
                'badge_bn' => 'দ্রুততম',
                'badge_class' => 'cb-p',
                'amount' => round($directPriv + $indPriv['total']),
                'desc_bn' => 'স্কয়ার, এভারকেয়ার · অপেক্ষা ১–২ সপ্তাহ',
            ],
        ];

        return [
            'inputs' => [
                'type' => $cancerTypeKey,
                'stage' => $stage,
                'dist' => $dist,
                'att' => $attendants,
                'loss' => $loss,
                'hosp' => $selectedHosp,
                'treat' => $treat,
            ],
            'cancer_type_name_bn' => $base['name_bn'],
            'months' => $months,
            'trips' => round($trips),
            'direct_cost' => round($selectedDirect),
            'direct_percent' => $directPercent,
            'indirect_cost' => $selectedInd['total'],
            'indirect_percent' => $indirectPercent,
            'total_estimated' => round($totalEstimated),
            'min_range' => $minRange,
            'max_range' => $maxRange,
            'monthly_avg' => $monthlyAvg,
            'comparisons' => $comparisons,
            'phases' => $phases,
            'monthly_flow' => $monthlyFlow,
            'indirect_items' => [
                ['title_bn' => 'যাতায়াত', 'icon' => 'ti-bus', 'amount' => $selectedInd['travel'], 'desc_bn' => "আনুমানিক {$this->bnNum(round($trips))} বার যাওয়া-আসা"],
                ['title_bn' => 'থাকার খরচ', 'icon' => 'ti-home', 'amount' => $selectedInd['stay'], 'desc_bn' => $selectedInd['stay'] > 0 ? 'হাসপাতালের কাছে অস্থায়ী থাকা' : 'ঢাকায় থাকায় প্রযোজ্য নয়'],
                ['title_bn' => 'খাওয়া', 'icon' => 'ti-tools-kitchen-2', 'amount' => $selectedInd['food'], 'desc_bn' => "রোগী ও {$this->bnNum($attendants)} জন সাথের লোকের"],
                ['title_bn' => 'বাইরে থেকে ওষুধ', 'icon' => 'ti-pill', 'amount' => $selectedInd['meds'], 'desc_bn' => 'হাসপাতালে না পাওয়া ওষুধ ও সরঞ্জাম'],
                ['title_bn' => 'আয় বন্ধ থাকা', 'icon' => 'ti-briefcase', 'amount' => $selectedInd['lostIncome'], 'desc_bn' => $selectedInd['lostIncome'] > 0 ? "{$this->bnNum($months)} মাস সাথের লোকের কাজ বন্ধ" : 'আয় বন্ধ হবে না বলে ধরা হয়েছে'],
                ['title_bn' => 'অন্যান্য', 'icon' => 'ti-dots', 'amount' => $selectedInd['misc'], 'desc_bn' => 'কাগজপত্র, ফটোকপি, ছোট খরচ'],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, bool> $treat
     */
    private function calcDirectCost(array $base, array $treat, float $stageMul, float $hospMul): float
    {
        $total = $base['diag'];
        if (! empty($treat['surgery']) && $base['surgery'] > 0) {
            $total += $base['surgery'];
        }
        if (! empty($treat['chemo']) && $base['chemo'] > 0) {
            $total += $base['chemo'];
        }
        if (! empty($treat['radiation']) && $base['radiation'] > 0) {
            $total += $base['radiation'];
        }
        if (! empty($treat['targeted']) && $base['targeted'] > 0) {
            $total += $base['targeted'];
        }

        return $total * $stageMul * $hospMul;
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, bool> $treat
     * @param array<string, mixed> $templates
     * @return list<array{t: string, w: string, amt: int, items: list<array{0: string, 1: int}>}>
     */
    private function buildPhases(array $base, array $treat, float $stageMul, float $hospMul, array $templates): array
    {
        $phases = [];

        // 1. Diagnosis
        $diagTpl = $templates['diagnosis'] ?? null;
        $diagAmt = round($base['diag'] * $hospMul);
        $diagItems = [];
        $breakdown = $diagTpl['breakdown'] ?? [['বায়োপসি ও প্যাথলজি', 0.45], ['ইমেজিং (CT/আল্ট্রাসনো)', 0.35], ['রক্ত পরীক্ষা ও ডাক্তারের ফি', 0.20]];
        foreach ($breakdown as $item) {
            $diagItems[] = [$item[0], (int) round($diagAmt * $item[1])];
        }
        $phases[] = [
            't' => $diagTpl['phase_title_bn'] ?? 'নির্ণয় ও পরীক্ষা',
            'w' => $diagTpl['when_bn'] ?? 'প্রথম ২–৪ সপ্তাহ',
            'amt' => $diagAmt,
            'items' => $diagItems,
        ];

        // 2. Surgery
        if (! empty($treat['surgery']) && $base['surgery'] > 0) {
            $surgTpl = $templates['surgery'] ?? null;
            $surgAmt = round($base['surgery'] * $stageMul * $hospMul);
            $surgItems = [];
            $breakdown = $surgTpl['breakdown'] ?? [['অপারেশন ও ওটি চার্জ', 0.70], ['ভর্তি ও শয্যা', 0.20], ['ওষুধ ও ড্রেসিং', 0.10]];
            foreach ($breakdown as $item) {
                $surgItems[] = [$item[0], (int) round($surgAmt * $item[1])];
            }
            $phases[] = [
                't' => $surgTpl['phase_title_bn'] ?? 'অপারেশন',
                'w' => $surgTpl['when_bn'] ?? '১–২ মাসের মধ্যে',
                'amt' => $surgAmt,
                'items' => $surgItems,
            ];
        }

        // 3. Chemo
        if (! empty($treat['chemo']) && $base['chemo'] > 0) {
            $chemoTpl = $templates['chemo'] ?? null;
            $chemoAmt = round($base['chemo'] * $stageMul * $hospMul);
            $chemoItems = [];
            $breakdown = $chemoTpl['breakdown'] ?? [['কেমো ওষুধ', 0.60], ['ডে-কেয়ার ও প্রশাসন', 0.25], ['পার্শ্বপ্রতিক্রিয়ার ওষুধ', 0.15]];
            foreach ($breakdown as $item) {
                $chemoItems[] = [$item[0], (int) round($chemoAmt * $item[1])];
            }
            $phases[] = [
                't' => $chemoTpl['phase_title_bn'] ?? 'কেমোথেরাপি',
                'w' => $chemoTpl['when_bn'] ?? '৪–৬ মাস · ৬–৮টি সাইকেল',
                'amt' => $chemoAmt,
                'items' => $chemoItems,
            ];
        }

        // 4. Radiation
        if (! empty($treat['radiation']) && $base['radiation'] > 0) {
            $radTpl = $templates['radiation'] ?? null;
            $radAmt = round($base['radiation'] * $stageMul * $hospMul);
            $radItems = [];
            $breakdown = $radTpl['breakdown'] ?? [['রেডিয়েশন সেশন', 0.80], ['প্ল্যানিং ও সিমুলেশন', 0.20]];
            foreach ($breakdown as $item) {
                $radItems[] = [$item[0], (int) round($radAmt * $item[1])];
            }
            $phases[] = [
                't' => $radTpl['phase_title_bn'] ?? 'রেডিওথেরাপি',
                'w' => $radTpl['when_bn'] ?? '৫–৭ সপ্তাহ · ২৫–৩৩টি সেশন',
                'amt' => $radAmt,
                'items' => $radItems,
            ];
        }

        // 5. Targeted
        if (! empty($treat['targeted']) && $base['targeted'] > 0) {
            $tarTpl = $templates['targeted'] ?? null;
            $tarAmt = round($base['targeted'] * $stageMul * $hospMul);
            $tarItems = [];
            $breakdown = $tarTpl['breakdown'] ?? [['ওষুধ (প্রায়ই বাইরে থেকে)', 0.90], ['প্রয়োগ ও পর্যবেক্ষণ', 0.10]];
            foreach ($breakdown as $item) {
                $tarItems[] = [$item[0], (int) round($tarAmt * $item[1])];
            }
            $phases[] = [
                't' => $tarTpl['phase_title_bn'] ?? 'টার্গেটেড থেরাপি',
                'w' => $tarTpl['when_bn'] ?? '৬–১২ মাস',
                'amt' => $tarAmt,
                'items' => $tarItems,
            ];
        }

        // 6. Follow-up
        $folTpl = $templates['followup'] ?? null;
        $folAmt = round($base['diag'] * $hospMul * 0.55);
        $folItems = [];
        $breakdown = $folTpl['breakdown'] ?? [['নিয়মিত পরীক্ষা ও স্ক্যান', 0.40], ['ডাক্তারের ফি', 0.15]];
        foreach ($breakdown as $item) {
            $folItems[] = [$item[0], (int) round($folAmt * $item[1])];
        }
        $phases[] = [
            't' => $folTpl['phase_title_bn'] ?? 'ফলো-আপ (প্রথম বছর)',
            'w' => $folTpl['when_bn'] ?? 'চিকিৎসার পর',
            'amt' => $folAmt,
            'items' => $folItems,
        ];

        return $phases;
    }

    /**
     * @return list<array{month: int, total: int, direct: int, indirect: int, height_percent: float, direct_percent: float}>
     */
    private function buildMonthlyFlow(float $direct, float $indirect, int $months): array
    {
        $shapeCurve = [0.7, 1.3, 1.5, 1.2, 1.4, 1.1, 0.8, 0.6, 0.5, 0.4, 0.4, 0.3, 0.3, 0.3];
        $shape = array_slice($shapeCurve, 0, $months);
        $sum = array_sum($shape) ?: 1.0;

        $dirM = array_map(fn ($x) => ($direct * $x) / $sum, $shape);
        $indM = array_map(fn ($x) => ($indirect * $x) / $sum, $shape);

        $totals = [];
        foreach ($dirM as $i => $d) {
            $totals[] = $d + $indM[$i];
        }
        $maxV = ! empty($totals) ? max($totals) : 1.0;
        if ($maxV <= 0) {
            $maxV = 1.0;
        }

        $flow = [];
        foreach ($totals as $i => $tot) {
            $d = $dirM[$i];
            $flow[] = [
                'month' => $i + 1,
                'total' => (int) round($tot),
                'direct' => (int) round($d),
                'indirect' => (int) round($indM[$i]),
                'height_percent' => round(($tot / $maxV) * 100, 1),
                'direct_percent' => $tot > 0 ? round(($d / $tot) * 100, 1) : 50.0,
            ];
        }

        return $flow;
    }

    /**
     * @return array<string, mixed>
     */
    public function loadRates(): array
    {
        return Cache::remember('cost_estimator_rates', 3600, function () {
            $baseRates = [];
            $cancerTypes = CancerType::with('costBaseRates')->get();
            foreach ($cancerTypes as $type) {
                $ratesByService = $type->costBaseRates->keyBy('service_key');
                $baseRates[$type->slug] = [
                    'name_bn' => $type->name_bn,
                    'diag' => $ratesByService->get('diagnosis')?->govt_amount ?? 14000,
                    'surgery' => $ratesByService->get('surgery')?->govt_amount ?? 32000,
                    'chemo' => $ratesByService->get('chemo')?->govt_amount ?? 48000,
                    'radiation' => $ratesByService->get('radiation')?->govt_amount ?? 22000,
                    'targeted' => $ratesByService->get('targeted')?->govt_amount ?? 180000,
                    'months' => $ratesByService->get('diagnosis')?->default_months ?? 8,
                ];
            }

            $stageMultipliers = CostMultiplier::where('group', 'stage')->pluck('multiplier', 'key')->all();
            $hospMultipliers = CostMultiplier::where('group', 'hospital_type')->pluck('multiplier', 'key')->all();
            $distMultipliers = CostMultiplier::where('group', 'distance')->pluck('multiplier', 'key')->all();

            $indirectRates = CostIndirectRate::all()->keyBy('key')->map(fn ($r) => [
                'base_amount' => $r->base_amount,
                'unit' => $r->unit,
                'percent_value' => $r->percent_value,
                'label_bn' => $r->label_bn,
            ])->all();

            $phaseTemplates = CostPhaseTemplate::all()->keyBy('service_key')->all();

            return [
                'base_rates' => $baseRates,
                'stage_multipliers' => $stageMultipliers,
                'hosp_multipliers' => $hospMultipliers,
                'dist_multipliers' => $distMultipliers,
                'indirect_rates' => $indirectRates,
                'phase_templates' => $phaseTemplates,
            ];
        });
    }

    private function bnNum(int|float|string $number): string
    {
        $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($en, $bn, (string) $number);
    }
}
