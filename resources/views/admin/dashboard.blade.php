@extends('portal')
@section('title', 'Admin management')
@section('content')
<div class="dashboard-heading">
    <div><div class="eyebrow"><span></span> Administration</div><h1>Platform management</h1><p>Manage accounts, guide access, hospital listings, services and transactions.</p></div>
    <span class="role-chip"><i class="bi bi-shield-lock"></i> Admin</span>
</div>

<section class="portal-section" id="users">
    <div class="section-title"><div><span class="eyebrow"><span></span> Accounts</div><h2>Users</h2></div>
    <div class="admin-list">
        @forelse($users as $managedUser)
            <article class="admin-row"><span><strong>{{ $managedUser->name }}</strong><small>{{ $managedUser->email }} · {{ ucfirst($managedUser->role) }} · {{ $managedUser->blocked_at ? 'Blocked' : 'Active' }}</small></span>
                <details><summary class="btn btn-outline-secondary btn-sm">Edit</summary><form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="portal-form mt-3">@csrf @method('PUT')<label class="form-label">Name</label><input class="form-control mb-2" name="name" value="{{ $managedUser->name }}" required><label class="form-label">Email</label><input class="form-control mb-2" name="email" type="email" value="{{ $managedUser->email }}" required><label class="form-label">Phone</label><input class="form-control mb-2" name="phone" value="{{ $managedUser->phone }}"><button class="btn btn-forest btn-sm">Save changes</button></form></details>
+                <form method="POST" action="{{ route('admin.users.block', $managedUser) }}">@csrf @method('PATCH')<button class="btn btn-outline-secondary btn-sm">{{ $managedUser->blocked_at ? 'Unblock' : 'Block' }}</button></form>
+                @if($managedUser->id !== auth()->id())<form method="POST" action="{{ route('admin.users.delete', $managedUser) }}" onsubmit="return confirm('Permanently delete this user and dependent records?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Delete</button></form>@endif
+            </article>
+        @empty<p class="help-text">No users found.</p>@endforelse
+    </div>
+    {{ $users->links() }}
+</section>
+
+<section class="portal-section" id="guides">
+    <div class="section-title"><div><span class="eyebrow"><span></span> Verification</div><h2>Guides</h2></div>
+    <div class="admin-list">
+        @forelse($guides as $guide)
+            <article class="admin-row"><span><strong>{{ $guide->user->name }}</strong><small>{{ $guide->user->email }} · {{ $guide->years_experience }} years · {{ $guide->hospitals->pluck('name')->join(', ') ?: 'No hospitals selected' }}</small><small>Status: {{ ucfirst($guide->status ?? ($guide->is_verified ? 'verified' : 'pending')) }}</small></span>
+                <form method="POST" action="{{ route('admin.guides.verify', $guide) }}" class="d-flex gap-2">@csrf @method('PATCH')<select class="form-select form-select-sm" name="status" aria-label="Guide status"><option value="verified">Verify</option><option value="rejected">Reject</option><option value="suspended">Suspend</option></select><button class="btn btn-forest btn-sm">Update</button></form>
+                @if($guide->document_path)<a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.guides.documents.view', $guide) }}" target="_blank" rel="noopener">View document</a>@else<span class="help-text">No document</span>@endif
+                <form method="POST" enctype="multipart/form-data" action="{{ route('admin.guides.documents.upload', $guide) }}">@csrf<input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" aria-label="Upload guide document" required><button class="btn btn-outline-secondary btn-sm mt-1">Upload</button></form>
+            </article>
+        @empty<p class="help-text">No guide applications found.</p>@endforelse
+    </div>
+</section>
+
+<section class="portal-section" id="hospitals">
+    <div class="section-title"><div><span class="eyebrow"><span></span> Directory</div><h2>Hospitals</h2></div>
+    <form method="POST" action="{{ route('admin.hospitals.store') }}" class="portal-form admin-hospital-form">@csrf<div class="row g-3"><div class="col-md-6"><label class="form-label">Hospital name</label><input class="form-control" name="name" required></div><div class="col-md-3"><label class="form-label">City</label><input class="form-control" name="city" required></div><div class="col-md-3"><label class="form-label">Type</label><input class="form-control" name="type"></div><div class="col-md-6"><label class="form-label">Address</label><input class="form-control" name="address" required></div><div class="col-md-3"><label class="form-label">Phone</label><input class="form-control" name="phone"></div><div class="col-md-3"><label class="form-label">Image URL</label><input class="form-control" type="url" name="image_url"></div></div><button class="btn btn-forest mt-3">Add hospital</button></form>
+    <div class="admin-list mt-3">@foreach($hospitals as $hospital)<article class="admin-row"><span><strong>{{ $hospital->name }}</strong><small>{{ $hospital->city }} · {{ $hospital->address }} · {{ $hospital->is_active ? 'Active' : 'Hidden' }}</small></span><details><summary class="btn btn-outline-secondary btn-sm">Edit</summary><form method="POST" action="{{ route('admin.hospitals.update', $hospital) }}" class="portal-form mt-3">@csrf @method('PUT')<input class="form-control mb-2" name="name" value="{{ $hospital->name }}" required><input class="form-control mb-2" name="city" value="{{ $hospital->city }}" required><input class="form-control mb-2" name="type" value="{{ $hospital->type }}"><input class="form-control mb-2" name="address" value="{{ $hospital->address }}" required><input class="form-control mb-2" name="phone" value="{{ $hospital->phone }}"><input class="form-control mb-2" type="url" name="image_url" value="{{ $hospital->image_url }}"><button class="btn btn-forest btn-sm">Save</button></form></details><form method="POST" action="{{ route('admin.hospitals.toggle', $hospital) }}">@csrf @method('PATCH')<button class="btn btn-outline-secondary btn-sm">{{ $hospital->is_active ? 'Hide' : 'Activate' }}</button></form><form method="POST" action="{{ route('admin.hospitals.delete', $hospital) }}" onsubmit="return confirm('Delete this hospital?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Delete</button></form></article>@endforeach</div>
+</section>
+
+<section class="portal-section" id="services">
+    <div class="section-title"><div><span class="eyebrow"><span></span> Booking catalog</div><h2>Services</h2></div>
+    <form method="POST" action="{{ route('admin.services.store') }}" class="portal-form"><div class="row g-2">@csrf<div class="col-md-4"><label class="form-label">Name</label><input class="form-control" name="name" required></div><div class="col-md-4"><label class="form-label">Description</label><input class="form-control" name="description"></div><div class="col-md-2"><label class="form-label">Base price</label><input class="form-control" name="base_price" type="number" min="0" step="0.01" value="0" required></div><div class="col-md-2 d-flex align-items-end"><button class="btn btn-forest">Add service</button></div></div></form>
+    <div class="admin-list mt-3">@forelse($services as $service)<article class="admin-row"><span><strong>{{ $service->name }}</strong><small>{{ $service->description ?: 'No description' }} · ₹{{ number_format((float) $service->base_price, 2) }} · {{ $service->is_active ? 'Active' : 'Inactive' }}</small></span><details><summary class="btn btn-outline-secondary btn-sm">Edit</summary><form method="POST" action="{{ route('admin.services.update', $service) }}" class="portal-form mt-3">@csrf @method('PUT')<input class="form-control mb-2" name="name" value="{{ $service->name }}" required><input class="form-control mb-2" name="description" value="{{ $service->description }}"><input class="form-control mb-2" name="base_price" type="number" min="0" step="0.01" value="{{ $service->base_price }}" required><label class="form-check"><input type="checkbox" name="is_active" value="1" @checked($service->is_active)> Active</label><button class="btn btn-forest btn-sm">Save</button></form></details><form method="POST" action="{{ route('admin.services.delete', $service) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Delete</button></form></article>@empty<p class="help-text">No services configured.</p>@endforelse</div>
+</section>
+
+<section class="portal-section" id="bookings">
+    <div class="section-title"><div><span class="eyebrow"><span></span> Visit requests</div><h2>Bookings</h2></div>
+    <form method="GET" action="{{ route('dashboard') }}" class="d-flex gap-2 mb-3"><select class="form-select" name="booking_status" aria-label="Filter bookings by status"><option value="">All statuses</option>@foreach(['pending', 'accepted', 'rejected', 'cancelled', 'completed'] as $status)<option value="{{ $status }}" @selected($bookingStatus === $status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="btn btn-outline-secondary">Filter</button></form>
+    @forelse($bookings as $booking)<article class="booking-row"><div class="booking-date"><strong>{{ $booking->visit_date->format('d') }}</strong><span>{{ $booking->visit_date->format('M Y') }}</span></div><div class="booking-details"><strong>{{ $booking->hospital->name }} · {{ $booking->service }}</strong><span>{{ $booking->patient->name }} with {{ $booking->guideProfile->user->name }}</span><small>{{ ucfirst($booking->status) }}</small></div><form method="POST" action="{{ route('admin.bookings.status', $booking) }}" class="d-flex gap-2">@csrf @method('PATCH')<select class="form-select form-select-sm" name="status" aria-label="Booking status">@foreach(['pending', 'accepted', 'rejected', 'cancelled', 'completed'] as $status)<option value="{{ $status }}" @selected($booking->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="btn btn-forest btn-sm">Save</button></form></article>@empty<div class="empty-panel">No bookings match this filter.</div>@endforelse
+    {{ $bookings->links() }}
+</section>
+
+<section class="portal-section" id="payments">
+    <div class="section-title"><div><span class="eyebrow"><span></span> Transactions</div><h2>Payments</h2></div>
+    <div class="admin-list">@forelse($payments as $payment)<article class="admin-row"><span><strong>{{ $payment->transaction_reference ?: 'Payment #'.$payment->id }} · {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</strong><small>{{ $payment->booking->patient->name }} · {{ $payment->booking->hospital->name }} · {{ $payment->provider ?: 'Provider pending' }}</small></span><span class="status status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span><small>{{ $payment->paid_at?->format('d M Y, H:i') ?: 'Not paid' }}</small></article>@empty<p class="help-text">No payment transactions recorded.</p>@endforelse</div>
+</section>
+
+<section class="portal-section" id="reviews">
+    <div class="section-title"><div><span class="eyebrow"><span></span> Customer feedback</div><h2>Reviews</h2></div>
+    <div class="admin-list">@forelse($reviews as $review)<article class="admin-row"><span><strong>{{ $review->rating }}/5 · {{ $review->booking->patient->name }} for {{ $review->booking->guideProfile->user->name }}</strong><small>{{ $review->booking->hospital->name }} · {{ $review->comment }}</small></span><form method="POST" action="{{ route('admin.reviews.visibility', $review) }}">@csrf @method('PATCH')<button class="btn btn-outline-secondary btn-sm">{{ $review->is_visible ? 'Hide' : 'Publish' }}</button></form><form method="POST" action="{{ route('admin.reviews.delete', $review) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Delete</button></form></article>@empty<p class="help-text">No reviews recorded.</p>@endforelse</div>
+    {{ $reviews->links() }}
+</section>
+
+<section class="portal-section" id="commission">
+    <div class="section-title"><div><span class="eyebrow"><span></span> Platform revenue</div><h2>Commission</h2></div>
+    <form method="POST" action="{{ route('admin.commission.update') }}" class="portal-form">@csrf @method('PUT')<div class="row g-3"><div class="col-md-4"><label class="form-label">Commission rate (%)</label><input class="form-control" name="percentage_rate" type="number" min="0" max="100" step="0.01" value="{{ $commission?->percentage_rate ?? 0 }}" required></div><div class="col-md-4"><label class="form-label">Fixed amount per booking (₹)</label><input class="form-control" name="fixed_amount" type="number" min="0" step="0.01" value="{{ $commission?->fixed_amount ?? 0 }}" required></div><div class="col-md-4 d-flex align-items-end"><button class="btn btn-forest">Save commission</button></div></div></form>
+</section>
+@endsection
