<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommissionRule;
use App\Models\User;
use App\Models\Service;

class CommissionRuleController extends Controller
{
    public function index()
    {
        $rules = CommissionRule::latest()->get();
        $staff = User::where('role', 'staff')->get();
        $services = Service::all();

        return view('commission-rules.index', compact('rules', 'staff', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric',
            'priority' => 'nullable|integer',
            'service_id' => 'nullable|exists:services,id',
            'staff_id' => 'nullable|exists:users,id',
        ]);

        CommissionRule::create([
            'type' => $request->type,
            'value' => $request->value,
            'priority' => $request->priority ?? 0,
            'service_id' => $request->service_id,
            'staff_id' => $request->staff_id,
            'is_active' => true,
        ]);

        return back()->with('success', 'Rule created successfully.');
    }

    public function toggle($id)
    {
        $rule = CommissionRule::findOrFail($id);
        $rule->is_active = !$rule->is_active;
        $rule->save();

        return back();
    }

    public function destroy($id)
    {
        CommissionRule::findOrFail($id)->delete();
        return back();
    }
}