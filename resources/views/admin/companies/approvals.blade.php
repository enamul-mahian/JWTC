@extends('dashboard.layout.dasboardMaster')
@section('title', 'Company approvals')
@section('content')
<section class="section dashboard"><div class="card"><div class="card-body p-4">
<h1 class="h4">Company account approvals</h1>
<p>Company accounts have no workspace access until explicitly approved. Existing accounts without a recorded approval are also held for review.</p>
<div class="table-responsive"><table class="table"><thead><tr><th>Company / Account</th><th>Contact</th><th>Approval</th><th>Action</th></tr></thead><tbody>
@foreach($companies as $company)
<tr><td>{{ $company->name }}</td><td>{{ $company->email }}<br>{{ $company->phone }}</td><td>{{ $company->employer_approved_at && (int)$company->status === 1 ? 'Approved' : 'Pending / revoked' }}</td><td>
<form method="POST" action="{{ route('admin.company-approvals.decide', $company->id) }}">@csrf
<input name="reason" class="form-control mb-2" maxlength="1000" aria-label="Decision reason" placeholder="Optional review note">
<button name="decision" value="approve" class="btn btn-primary btn-sm">Approve</button>
<button name="decision" value="revoke" class="btn btn-outline-danger btn-sm">Revoke access</button>
</form></td></tr>
@endforeach
</tbody></table></div>{{ $companies->links() }}
</div></div></section>
@endsection
