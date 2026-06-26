@php
    $isEdit = $edit ?? false;
@endphp

<div>
    <label class="mb-1.5 block text-xs font-bold text-[var(--muted)]">Full name</label>
    <input type="text"
           @if($isEdit) id="edit_name" @endif
           name="name"
           required
           placeholder="e.g. Maria Santos"
           class="theme-field w-full rounded-xl px-3 py-2.5 text-sm">
</div>

<div>
    <label class="mb-1.5 block text-xs font-bold text-[var(--muted)]">Email</label>
    <input type="email"
           @if($isEdit) id="edit_email" @endif
           name="email"
           required
           placeholder="email@example.com"
           class="theme-field w-full rounded-xl px-3 py-2.5 text-sm">
</div>

<div>
    <label class="mb-1.5 block text-xs font-bold text-[var(--muted)]">
        {{ $isEdit ? 'New password' : 'Password' }}
        @if($isEdit)
            <span class="font-semibold opacity-70">(leave blank to keep current)</span>
        @endif
    </label>
    <input type="password"
           name="password"
           {{ $isEdit ? '' : 'required' }}
           placeholder="{{ $isEdit ? 'Optional' : 'Required' }}"
           class="theme-field w-full rounded-xl px-3 py-2.5 text-sm">
</div>

<div>
    <label class="mb-1.5 block text-xs font-bold text-[var(--muted)]">Role</label>
    <select name="role"
            @if($isEdit) id="edit_role" @endif
            required
            class="theme-field w-full rounded-xl px-3 py-2.5 text-sm">
        @foreach($roles as $value => $label)
            @if($value !== 'admin' || $isEdit)
                <option value="{{ $value }}">{{ $label }}</option>
            @endif
        @endforeach
    </select>
</div>
