@extends('dashboard.layout.dasboardMaster')
@section('title' , 'Dashboard')
@section('content')
@if(auth()->user()->user_type == 'admin')
<div class="container-fluid" id="dashboard_content">

</div>
@endif
@endsection

@push('script')
<script src="{{ asset('assets/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('backend/js/dashboard.js') }}"></script>
<script>
    function userFunction() {
        fetch('/admin-dashboard/users-chart-data')
        .then(response => response.json())
        .then(data => {
            const options = {
                chart: {
                    type: 'bar',
                },
                xaxis: {
                    categories: data.categories,
                },
                series: [
                    {
                        name: 'Count',
                        data: data.series,
                    },
                ],
            };

            const chart = new ApexCharts(document.querySelector("#reportsChart"), options);
            chart.render();
        });
    }

    setTimeout(userFunction, 2000);
</script>

<script>
    function getJobsData() {
        fetch('/admin-dashboard/jobs-chart-data')
        .then(response => response.json())
        .then(data => {
            const options = {
                chart: {
                    type: 'bar',
                },
                xaxis: {
                    categories: data.categories,
                },
                series: [
                    {
                        name: 'Count',
                        data: data.series,
                    },
                ],
            };

            const chart = new ApexCharts(document.querySelector("#jobsChart"), options);
            chart.render();
        });
    }

    setTimeout(getJobsData, 2000);
</script>

<script>
    function getJobCategoryData() {
        fetch('/admin-dashboard/job-category-chart-data')
            .then(response => response.json())
            .then(data => {
                const options = {
                    chart: {
                        type: 'pie',
                    },
                    labels: data.categories,
                    series: data.series,
                };

                const chart = new ApexCharts(document.querySelector("#piechart"), options);
                chart.render();
            });
    }

    setTimeout(getJobCategoryData, 2000);
</script>
@endpush