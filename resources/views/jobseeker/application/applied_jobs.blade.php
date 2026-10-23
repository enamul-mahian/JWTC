@extends('jobseeker.layout.workspace')
@section('title', 'Applied Jobs')
@section('workspace_title', 'Applied Jobs')
@section('workspace_description', 'Review your applications and track their progress.')
@section('workspace_content')
@include('jobseeker.application.applied_jobs_table')
@endsection
