@extends('jobseeker.layout.workspace')
@section('title','Find Jobs')
@section('workspace_title','Find Jobs')
@section('workspace_description','Browse published vacancies and find your next opportunity.')
@section('workspace_content')<section class="jg-profile-card jg-filter jg-filter-horizontal"><h2>Filter vacancies</h2>@include('shared.job_filters',['filterAction'=>route('find-jobs')])</section><p class="jg-listing-count">{{ number_format($jobs->total()) }} published {{ \Illuminate\Support\Str::plural('job',$jobs->total()) }}</p>@include('jobseeker.application.jobs_table')@endsection
