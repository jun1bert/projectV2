@extends('layouts.app')

@section('header', 'Commission Rules')
@section('subheader', 'Manage how staff commissions are calculated automatically')

@section('content')

<!-- ===================== INFO PANEL ===================== -->
<div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-2xl mb-6 text-sm">

    <p class="font-semibold mb-1">How Commission Rules Work</p>

    <ul class="list-disc ml-5 space-y-1">
        <li>Rules determine how staff commissions are calculated automatically.</li>
        <li><b>Percentage</b> = based on service price (e.g. 10% of ₱1000 = ₱100)</li>
        <li><b>Fixed</b> = flat amount regardless of service price</li>
        <li><b>Priority</b> controls which rule is applied first (higher number = higher priority)</li>
        <li>Staff rules override service rules, and service rules override default rules</li>
    </ul>

</div>

<!-- ===================== FORM ===================== -->
<div class="bg-white p-6 rounded-2xl shadow mb-6">

    <h2 class="text-lg font-semibold mb-4">➕ Create New Rule</h2>

    <form method="POST" action="/commission-rules"
          class="grid grid-cols-1 md:grid-cols-5 gap-3">

        @csrf

        <!-- TYPE -->
        <div>
            <label class="text-xs text-gray-600">Rule Type</label>
            <select name="type" class="border rounded-lg p-2 w-full">
                <option value="percentage">Percentage (%)</option>
                <option value="fixed">Fixed Amount (₱)</option>
            </select>
        </div>

        <!-- VALUE -->
        <div>
            <label class="text-xs text-gray-600">Value</label>
            <input name="value" type="number" step="0.01"
                   placeholder="e.g. 10 or 100"
                   class="border rounded-lg p-2 w-full">
        </div>

        <!-- PRIORITY -->
        <div>
            <label class="text-xs text-gray-600">Priority</label>
            <input name="priority" type="number"
                   placeholder="Higher = stronger rule"
                   class="border rounded-lg p-2 w-full">
        </div>

        <!-- SERVICE -->
        <div>
            <label class="text-xs text-gray-600">Service (optional)</label>
            <select name="service_id" class="border rounded-lg p-2 w-full">
                <option value="">All Services</option>
                @foreach($services as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- STAFF -->
        <div>
            <label class="text-xs text-gray-600">Staff (optional)</label>
            <select name="staff_id" class="border rounded-lg p-2 w-full">
                <option value="">All Staff</option>
                @foreach($staff as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- BUTTON -->
        <div class="md:col-span-5">
            <button class="bg-black text-white rounded-lg px-4 py-2 w-full hover:bg-gray-800">
                Save Commission Rule
            </button>
        </div>

    </form>
</div>

<!-- ===================== TABLE ===================== -->
<div class="bg-white rounded-2xl shadow overflow-hidden">

    <div class="p-4 border-b flex justify-between items-center">
        <h2 class="font-semibold">Existing Rules</h2>
        <span class="text-xs text-gray-500">
            Highest priority rules are applied first
        </span>
    </div>

    <table class="w-full text-sm">

        <thead class="bg-gray-50 text-left">
            <tr>
                <th class="p-4">Type</th>
                <th class="p-4">Value</th>
                <th class="p-4">Priority</th>
                <th class="p-4">Scope</th>
                <th class="p-4">Status</th>
                <th class="p-4">Actions</th>
            </tr>
        </thead>

        <tbody>

        @forelse($rules as $rule)

            <tr class="border-b hover:bg-gray-50">

                <td class="p-4 font-medium">
                    {{ ucfirst($rule->type) }}
                </td>

                <td class="p-4">
                    {{ $rule->value }}
                    <span class="text-xs text-gray-500">
                        {{ $rule->type === 'percentage' ? '%' : '₱' }}
                    </span>
                </td>

                <td class="p-4">
                    <span class="px-2 py-1 text-xs rounded bg-gray-100">
                        {{ $rule->priority }}
                    </span>
                </td>

                <td class="p-4 text-xs text-gray-600">

                    @if($rule->staff_id && $rule->service_id)
                        Staff + Service Rule
                    @elseif($rule->staff_id)
                        Staff Rule
                    @elseif($rule->service_id)
                        Service Rule
                    @else
                        Default Rule
                    @endif

                </td>

                <td class="p-4">
                    @if($rule->is_active)
                        <span class="text-green-600 text-xs font-semibold">Active</span>
                    @else
                        <span class="text-red-600 text-xs font-semibold">Inactive</span>
                    @endif
                </td>

                <td class="p-4 flex gap-2">

                    <form method="POST" action="/commission-rules/{{ $rule->id }}/toggle">
                        @csrf
                        <button class="text-blue-600 text-xs hover:underline">
                            Toggle
                        </button>
                    </form>

                    <form method="POST" action="/commission-rules/{{ $rule->id }}">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600 text-xs hover:underline"
                                onclick="return confirm('Delete this rule?')">
                            Delete
                        </button>
                    </form>

                </td>

            </tr>

        @empty

            <tr>
                <td colspan="6" class="p-6 text-center text-gray-500">
                    No commission rules found. Create your first rule above.
                </td>
            </tr>

        @endforelse

        </tbody>

    </table>
</div>

@endsection