
@extends('layouts.dashboard')
@section('header', 'Commission Rules')
@section('subheader', 'Manage how staff commissions are calculated automatically')

@section('content')

{{-- ===================== INFO PANEL ===================== --}}
<div class="bg-[#faf7f2] border border-[#c8a96a]/30 rounded-2xl p-4 mb-6 text-sm">
    <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-full bg-[#c8a96a]/20 text-[#8a6a30] flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold text-[#3a2a22] mb-2">How Commission Rules Work</p>
            <ul class="space-y-1 text-gray-600 text-xs">
                <li class="flex items-start gap-1.5"><span class="text-[#c8a96a] mt-0.5">•</span> Rules determine how staff commissions are calculated automatically when a payment is processed.</li>
                <li class="flex items-start gap-1.5"><span class="text-[#c8a96a] mt-0.5">•</span> <span><strong>Percentage</strong> — based on service price (e.g. 10% of ₱1,000 = ₱100)</span></li>
                <li class="flex items-start gap-1.5"><span class="text-[#c8a96a] mt-0.5">•</span> <span><strong>Fixed</strong> — flat amount regardless of service price (e.g. ₱150 per service)</span></li>
                <li class="flex items-start gap-1.5"><span class="text-[#c8a96a] mt-0.5">•</span> <span><strong>Priority</strong> — higher number wins. Staff + Service rules override Staff-only, then Service-only, then Default.</span></li>
            </ul>
        </div>
    </div>
</div>

{{-- ===================== CREATE RULE FORM ===================== --}}
<div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6 mb-6">

    <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <svg class="w-4 h-4 text-[#c8a96a]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Create New Rule
    </h2>

    <form method="POST" action="/commission-rules">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-4">

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Rule Type</label>
                <select name="type"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
                    <option value="percentage">Percentage (%)</option>
                    <option value="fixed">Fixed Amount (₱)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Value</label>
                <input name="value" type="number" step="0.01" required placeholder="e.g. 10 or 150"
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Priority</label>
                <input name="priority" type="number" placeholder="e.g. 1, 10, 100"
                       class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 focus:border-[#c8a96a] transition">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Service <span class="text-gray-400 font-normal">(optional)</span></label>
                <select name="service_id"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                    <option value="">All Services</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Staff <span class="text-gray-400 font-normal">(optional)</span></label>
                <select name="staff_id"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#c8a96a]/50 transition">
                    <option value="">All Staff</option>
                    @foreach($staff as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#c8a96a] hover:bg-[#b8955a] active:scale-95 text-white text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Save Commission Rule
        </button>

    </form>
</div>

{{-- ===================== DESKTOP TABLE ===================== --}}
<div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm" id="desktopTable">

    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-900">Existing Rules</h2>
        <span class="text-xs text-gray-400">Highest priority applied first</span>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="bg-[#3a2a22]/5 border-b border-gray-100 text-xs font-medium text-[#3a2a22]/70 uppercase tracking-wide">
                <th class="px-5 py-3.5 text-left">Type</th>
                <th class="px-5 py-3.5 text-left">Value</th>
                <th class="px-5 py-3.5 text-left">Priority</th>
                <th class="px-5 py-3.5 text-left">Scope</th>
                <th class="px-5 py-3.5 text-left">Status</th>
                <th class="px-5 py-3.5 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
        @forelse($rules as $rule)
            <tr class="hover:bg-[#faf7f2] transition-colors">

                <td class="px-5 py-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                        {{ $rule->type === 'percentage' ? 'bg-[#c8a96a]/10 text-[#8a6a30] ring-1 ring-[#c8a96a]/30' : 'bg-[#3a2a22]/10 text-[#3a2a22] ring-1 ring-[#3a2a22]/20' }}">
                        {{ $rule->type === 'percentage' ? '% Percentage' : '₱ Fixed' }}
                    </span>
                </td>

                <td class="px-5 py-4 font-semibold text-gray-800">
                    {{ $rule->type === 'percentage' ? $rule->value . '%' : '₱' . number_format($rule->value, 2) }}
                </td>

                <td class="px-5 py-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                        {{ $rule->priority }}
                    </span>
                </td>

                <td class="px-5 py-4 text-xs text-gray-600">
                    @if($rule->staff && $rule->service)
                        <span class="font-medium text-gray-800">{{ $rule->staff->name }}</span>
                        <span class="text-gray-400"> · </span>
                        {{ $rule->service->name }}
                    @elseif($rule->staff_id)
                        <span class="font-medium text-gray-800">{{ $rule->staff->name ?? '—' }}</span>
                        <span class="ml-1 text-gray-400">(all services)</span>
                    @elseif($rule->service_id)
                        {{ $rule->service->name ?? '—' }}
                        <span class="ml-1 text-gray-400">(all staff)</span>
                    @else
                        <span class="text-gray-400 italic">Default — all staff & services</span>
                    @endif
                </td>

                <td class="px-5 py-4">
                    @if($rule->is_active)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">Active</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500 ring-1 ring-gray-200">Inactive</span>
                    @endif
                </td>

                <td class="px-5 py-4">
                    <div class="flex items-center gap-2">
                        <form method="POST" action="/commission-rules/{{ $rule->id }}/toggle">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-[#3a2a22]/10 hover:bg-[#3a2a22]/20 text-[#3a2a22] transition">
                                {{ $rule->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        <form method="POST" action="/commission-rules/{{ $rule->id }}"
                              onsubmit="return confirm('Delete this rule?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 hover:bg-red-100 text-red-600 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </td>

            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-5 py-16 text-center">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-400 text-sm">No commission rules yet. Create your first rule above.</p>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- ===================== MOBILE CARDS ===================== --}}
<div class="grid gap-3" id="mobileCards">

    <div class="flex items-center justify-between mb-1">
        <h2 class="text-sm font-semibold text-gray-900">Existing Rules</h2>
        <span class="text-xs text-gray-400">Highest priority applied first</span>
    </div>

@forelse($rules as $rule)
<div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">

    <div class="flex justify-between items-start gap-2 mb-3">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                {{ $rule->type === 'percentage' ? 'bg-[#c8a96a]/10 text-[#8a6a30] ring-1 ring-[#c8a96a]/30' : 'bg-[#3a2a22]/10 text-[#3a2a22] ring-1 ring-[#3a2a22]/20' }}">
                {{ $rule->type === 'percentage' ? '% Percentage' : '₱ Fixed' }}
            </span>
            <span class="text-sm font-bold text-gray-800">
                {{ $rule->type === 'percentage' ? $rule->value . '%' : '₱' . number_format($rule->value, 2) }}
            </span>
        </div>
        @if($rule->is_active)
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 shrink-0">Active</span>
        @else
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500 ring-1 ring-gray-200 shrink-0">Inactive</span>
        @endif
    </div>

    <div class="text-xs text-gray-500 space-y-1 mb-3">
        <div class="flex justify-between">
            <span class="text-gray-400">Scope</span>
            <span class="text-gray-700 font-medium text-right">
                @if($rule->staff && $rule->service)
                    {{ $rule->staff->name }} · {{ $rule->service->name }}
                @elseif($rule->staff_id)
                    {{ $rule->staff->name ?? '—' }} (all services)
                @elseif($rule->service_id)
                    {{ $rule->service->name ?? '—' }} (all staff)
                @else
                    Default
                @endif
            </span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-400">Priority</span>
            <span class="text-gray-700 font-medium">{{ $rule->priority }}</span>
        </div>
    </div>

    <div class="border-t border-gray-100 pt-3 flex gap-2">
        <form method="POST" action="/commission-rules/{{ $rule->id }}/toggle" class="flex-1">
            @csrf
            <button type="submit"
                    class="w-full inline-flex items-center justify-center px-3 py-2 rounded-xl text-xs font-medium bg-[#3a2a22]/10 hover:bg-[#3a2a22]/20 text-[#3a2a22] transition">
                {{ $rule->is_active ? 'Deactivate' : 'Activate' }}
            </button>
        </form>
        <form method="POST" action="/commission-rules/{{ $rule->id }}"
              onsubmit="return confirm('Delete this rule?')" class="flex-1">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-red-50 hover:bg-red-100 text-red-600 transition">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
            </button>
        </form>
    </div>
</div>
@empty
<div class="text-center py-16 text-gray-400 text-sm">
    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
    </svg>
    No commission rules yet. Create your first rule above.
</div>
@endforelse

</div>

<style>
#desktopTable { display: none; }
@media (min-width: 768px) {
    #desktopTable { display: block; }
    #mobileCards  { display: none; }
}
</style>

@endsection