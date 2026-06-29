@extends('layouts.dashboard')

@section('header', 'My Bookings')
@section('subheader', 'Track upcoming appointments, packages, and visit history')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h3 class="text-xl font-semibold text-[var(--ink)]">Upcoming Appointments</h3>
        <p class="mt-1 text-sm text-[var(--muted)]">Your bookings are private and visible only to your account.</p>
    </div>
    <button type="button" onclick="openCustomerBooking()" class="theme-button rounded-xl px-4 py-2.5 text-center text-sm font-semibold">Book Appointment</button>
</div>

@if(session('success'))
    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
@endif

<div class="grid gap-4 lg:grid-cols-2">
    @forelse($upcoming as $appointment)
        <article class="theme-card rounded-2xl p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h4 class="text-lg font-semibold text-[var(--ink)]">{{ $appointment->service_names }}</h4>
                    <p class="mt-1 text-sm text-[var(--muted)]">{{ \Carbon\Carbon::parse($appointment->date)->format('M d, Y') }} at {{ $appointment->formatted_time }}</p>
                    <p class="mt-1 text-xs font-semibold text-[var(--desert-rock)]">{{ $appointment->party_size }} client{{ $appointment->party_size === 1 ? '' : 's' }}</p>
                    @if($appointment->party_size > 1)<div class="mt-2 space-y-1 text-xs text-[var(--muted)]">@foreach($appointment->participants as $participant)<p><strong>{{ $participant->display_name }}:</strong> {{ $participant->services->pluck('name')->join(', ') }}</p>@endforeach</div>@endif
                </div>
                <span class="rounded-full bg-[var(--creamed-oat)] px-3 py-1 text-xs font-semibold capitalize text-[var(--ink)]">{{ $appointment->status }}</span>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-[rgba(230,218,200,.28)] p-4 text-sm">
                <div><span class="block text-xs text-[var(--muted)]">Booking</span><span class="font-semibold capitalize">{{ $appointment->booking_type }}</span></div>
                <div><span class="block text-xs text-[var(--muted)]">Payment</span><span class="font-semibold capitalize">{{ str_replace('_', ' ', $appointment->payment_status) }}</span></div>
                @if($appointment->servicePackage)
                    <div class="col-span-2"><span class="block text-xs text-[var(--muted)]">Package</span><span class="font-semibold text-violet-700">{{ $appointment->servicePackage->remaining_sessions }} of {{ $appointment->servicePackage->total_sessions }} sessions remaining</span></div>
                @endif
            </div>
        </article>
    @empty
        <div class="theme-card col-span-full rounded-2xl px-5 py-14 text-center">
            <h4 class="text-lg font-semibold text-[var(--ink)]">No upcoming appointments</h4>
            <p class="mt-2 text-sm text-[var(--muted)]">When you make a booking, it will appear here.</p>
        </div>
    @endforelse
</div>

<section class="mt-8">
    <h3 class="text-xl font-semibold text-[var(--ink)]">Booking History</h3>
    <div class="theme-card mt-4 overflow-hidden rounded-2xl">
        @forelse($history as $appointment)
            <div class="flex flex-col gap-2 border-b border-[rgba(164,141,120,.16)] px-5 py-4 last:border-0 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold text-[var(--ink)]">{{ $appointment->service_names }}</p>
                    <p class="mt-1 text-xs text-[var(--muted)]">{{ \Carbon\Carbon::parse($appointment->date)->format('M d, Y') }} at {{ $appointment->formatted_time }}</p>
                    <p class="mt-1 text-xs font-semibold text-[var(--desert-rock)]">{{ $appointment->party_size }} client{{ $appointment->party_size === 1 ? '' : 's' }}</p>
                    @if($appointment->party_size > 1)<div class="mt-2 space-y-1 text-xs text-[var(--muted)]">@foreach($appointment->participants as $participant)<p><strong>{{ $participant->display_name }}:</strong> {{ $participant->services->pluck('name')->join(', ') }}</p>@endforeach</div>@endif
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="rounded-full bg-[var(--creamed-oat)] px-3 py-1 font-semibold capitalize">{{ $appointment->status }}</span>
                    <span class="rounded-full bg-[var(--porcelain-mist)] px-3 py-1 font-semibold capitalize">{{ str_replace('_', ' ', $appointment->payment_status) }}</span>
                </div>
            </div>
        @empty
            <p class="px-5 py-10 text-center text-sm text-[var(--muted)]">No booking history yet.</p>
        @endforelse
    </div>
</section>

<div id="customerBookingModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <button type="button" class="fixed inset-0 bg-black/45 backdrop-blur-sm" onclick="closeCustomerBooking()" aria-label="Close booking form"></button>
        <div class="theme-card relative w-full max-w-2xl overflow-hidden rounded-2xl">
            <div class="flex items-center justify-between border-b border-[rgba(164,141,120,.16)] px-5 py-4">
                <div><h2 class="text-lg font-semibold text-[var(--ink)]">Book Appointment</h2><p class="mt-1 text-xs text-[var(--muted)]">Your account details are already filled in.</p></div>
                <button type="button" class="muted-button rounded-lg px-3 py-1.5 text-sm" onclick="closeCustomerBooking()">Close</button>
            </div>
            <form method="POST" action="{{ route('appointments.store') }}" class="space-y-4 px-5 py-5">
                @csrf
                <input type="hidden" name="full_name" value="{{ auth()->user()->name }}">
                <input type="hidden" name="contact_number" value="{{ $bookingClient?->contact_number ?? auth()->user()->phone }}">
                <input type="hidden" name="email" value="{{ $bookingClient?->email ?? auth()->user()->email }}">

                <div class="rounded-xl bg-[rgba(230,218,200,.3)] p-4 text-sm">
                    <p class="font-semibold text-[var(--ink)]">{{ auth()->user()->name }}</p>
                    <p class="mt-1 text-xs text-[var(--muted)]">{{ $bookingClient?->contact_number ?? auth()->user()->phone }} &middot; {{ $bookingClient?->email ?? auth()->user()->email }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Category</label><select id="customerBookCategory" class="theme-field px-3 py-2.5" required><option value="">Select category</option>@foreach($services->pluck('category')->unique() as $category)<option value="{{ $category }}">{{ $category }}</option>@endforeach</select></div>
                    <div><label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Client 1 services</label><input type="hidden" name="participants[0][name]" value="{{ auth()->user()->name }}"><select id="customerBookService" data-customer-participant-services name="participants[0][service_ids][]" multiple size="5" class="theme-field px-3 py-2.5" required>@foreach($services as $service)<option value="{{ $service->id }}" data-category="{{ $service->category }}" data-consent="{{ $service->requires_consent ? '1' : '0' }}" data-price="{{ $service->price }}">{{ $service->name }} — PHP {{ number_format($service->price, 2) }}</option>@endforeach</select><p class="mt-1 text-xs text-[var(--muted)]">Select one or more services.</p></div>
                </div>
                <div><label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Number of clients</label><input id="customerPartySize" type="number" name="party_size" min="1" max="50" value="{{ old('party_size', 1) }}" class="theme-field px-3 py-2.5" required><p class="mt-1 text-xs text-[var(--muted)]">Each client can choose different services.</p></div>
                <div id="customerAdditionalParticipants" class="space-y-3"></div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Date</label><input type="date" name="date" min="{{ now()->toDateString() }}" value="{{ old('date') }}" class="theme-field px-3 py-2.5" required></div>
                    <div><label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Time</label><select name="time" class="theme-field px-3 py-2.5" required><option value="">Select time</option>@foreach($onlineTimeSlots as $slot)<option value="{{ $slot }}">{{ \Carbon\Carbon::createFromFormat('H:i', $slot)->format('g:i A') }}</option>@endforeach</select></div>
                </div>
                <div><label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Special requests <span class="font-normal">(optional)</span></label><textarea name="notes" rows="3" class="theme-field resize-none px-3 py-2.5">{{ old('notes') }}</textarea></div>

                <p class="rounded-xl bg-[rgba(230,218,200,.3)] p-4 text-xs text-[var(--muted)]">If a selected service requires consent, it will be reviewed and signed at the store before treatment.</p>

                <div class="grid gap-2 pt-2 sm:grid-cols-2"><button type="button" class="muted-button rounded-xl px-4 py-2.5 text-sm font-semibold" onclick="closeCustomerBooking()">Cancel</button><button type="submit" class="theme-button rounded-xl px-4 py-2.5 text-sm font-semibold">Submit Booking</button></div>
            </form>
        </div>
    </div>
</div>

<script>
const customerBookingModal = document.getElementById('customerBookingModal');
const customerBookCategory = document.getElementById('customerBookCategory');
const customerBookService = document.getElementById('customerBookService');
const customerSignatureCanvas = document.getElementById('customerSignatureCanvas');
const customerSignatureInput = document.getElementById('customerConsentSignature');
const customerSignatureContext = customerSignatureCanvas?.getContext('2d');
let customerSigning = false;

function renderCustomerParticipants() {
    const count = Math.max(1, Math.min(50, Number(document.getElementById('customerPartySize')?.value) || 1));
    const container = document.getElementById('customerAdditionalParticipants');
    const options = customerBookService.innerHTML;
    container.innerHTML = '';
    for (let index = 1; index < count; index++) {
        container.insertAdjacentHTML('beforeend', `<div class="rounded-xl border border-[rgba(164,141,120,.2)] bg-white/50 p-4"><label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Client ${index + 1} name (optional)</label><input name="participants[${index}][name]" class="theme-field mb-3 px-3 py-2.5" placeholder="Client ${index + 1}"><label class="mb-1.5 block text-xs font-semibold text-[var(--muted)]">Client ${index + 1} services</label><select data-customer-participant-services name="participants[${index}][service_ids][]" multiple size="5" class="theme-field px-3 py-2.5" required>${options}</select></div>`);
    }
}

function customerBookingRequiresConsent() {
    return Array.from(document.querySelectorAll('[data-customer-participant-services]')).some(select =>
        Array.from(select.selectedOptions).some(option => option.dataset.consent === '1')
    );
}

function openCustomerBooking() { customerBookingModal.classList.remove('hidden'); document.body.style.overflow = 'hidden'; resizeCustomerSignature(); }
function closeCustomerBooking() { customerBookingModal.classList.add('hidden'); document.body.style.overflow = ''; }
function resizeCustomerSignature() { if (!customerSignatureCanvas || !customerSignatureContext) return; const rect = customerSignatureCanvas.getBoundingClientRect(); customerSignatureCanvas.width = Math.max(rect.width, 320); customerSignatureCanvas.height = 144; customerSignatureContext.lineWidth = 2; customerSignatureContext.lineCap = 'round'; customerSignatureContext.strokeStyle = '#4d4037'; }
function clearCustomerSignature() { if (!customerSignatureContext) return; customerSignatureContext.clearRect(0, 0, customerSignatureCanvas.width, customerSignatureCanvas.height); customerSignatureInput.value = ''; }
function customerSignaturePoint(event) { const rect = customerSignatureCanvas.getBoundingClientRect(); const source = event.touches?.[0] ?? event; return { x: source.clientX - rect.left, y: source.clientY - rect.top }; }
function startCustomerSignature(event) { event.preventDefault(); customerSigning = true; const point = customerSignaturePoint(event); customerSignatureContext.beginPath(); customerSignatureContext.moveTo(point.x, point.y); }
function drawCustomerSignature(event) { if (!customerSigning) return; event.preventDefault(); const point = customerSignaturePoint(event); customerSignatureContext.lineTo(point.x, point.y); customerSignatureContext.stroke(); customerSignatureInput.value = customerSignatureCanvas.toDataURL('image/png'); }
function stopCustomerSignature() { customerSigning = false; }

customerBookCategory?.addEventListener('change', () => {
    Array.from(customerBookService.options).forEach(option => { const visible = !customerBookCategory.value || option.dataset.category === customerBookCategory.value || option.selected; option.hidden = !visible; option.disabled = false; });
});
document.getElementById('customerPartySize')?.addEventListener('input', renderCustomerParticipants);
document.getElementById('customerAdditionalParticipants')?.addEventListener('change', () => { const required = customerBookingRequiresConsent(); document.getElementById('customerConsentSection')?.classList.toggle('hidden', !required); if (!required) clearCustomerSignature(); else resizeCustomerSignature(); });
customerBookService?.addEventListener('change', () => { const required = customerBookingRequiresConsent(); document.getElementById('customerConsentSection')?.classList.toggle('hidden', !required); if (!required) clearCustomerSignature(); else resizeCustomerSignature(); });
customerSignatureCanvas?.addEventListener('mousedown', startCustomerSignature); customerSignatureCanvas?.addEventListener('mousemove', drawCustomerSignature); window.addEventListener('mouseup', stopCustomerSignature);
customerSignatureCanvas?.addEventListener('touchstart', startCustomerSignature, { passive: false }); customerSignatureCanvas?.addEventListener('touchmove', drawCustomerSignature, { passive: false }); window.addEventListener('touchend', stopCustomerSignature);
renderCustomerParticipants();
if (window.location.hash === '#book' || {{ $errors->any() ? 'true' : 'false' }}) openCustomerBooking();
</script>
@endsection
