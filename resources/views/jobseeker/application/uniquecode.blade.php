@extends('jobseeker.layout.workspace')
@section('title', 'Unique Application Codes')
@section('workspace_title', 'Unique Application Codes')
@section('workspace_description', 'Use one approved code for each job application.')
@section('workspace_content')
<section class="jg-profile-card">
<p>Request another code to apply for more jobs. An administrator can approve multiple codes or reject your request.</p>
@if ($canCreate > 0)
<form method="POST" action="{{ route('request-code') }}">@csrf<button type="submit" class="jg-button">Request New Code</button></form>
@else<p class="jg-notice">Your code request is pending administrator review.</p>@endif
<div class="table-wrap"><table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>User</th>
                                                <th>Code</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($uniqueCodes as $uniqueCode)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $uniqueCode->user->name ?? 'N/A' }}</td>
                                                    <td>{{ $uniqueCode->code }}</td>
                                                    <td>{{ $uniqueCode->date }}</td>
                                                    <td>{{ $uniqueCode->code === null ? ($uniqueCode->is_used ? 'Rejected / Cancelled' : 'Pending approval') : ($uniqueCode->is_used ? 'Used' : 'Available') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table></div>
</section>
@endsection
