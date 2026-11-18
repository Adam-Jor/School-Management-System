<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AcademicPeriodController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_id' => ['required', 'exists:tbl_academic_periods,id'],
        ]);

        $period = AcademicPeriod::findOrFail($validated['period_id']);
        session(['active_academic_period_id' => $period->id]);

        return back()->with('toast', "Periode akademik aktif dialihkan ke {$period->name} ({$period->semester})");
    }
}
