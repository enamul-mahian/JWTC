<div class="row">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="bi bi-person h1"></i>
                    <div class="text-end">
                        <h3>{{ $total_job_seeker }}</h3>
                        <p>Total Job Seeker </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="bi bi-file-text h1"></i>
                    <div class="text-end">
                        <h3>{{ $total_company }}</h3>
                        <p>Total Company </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="bi bi-cart h1"></i>
                    <div class="text-end">
                        <h3>{{ $total_job }}</h3>
                        <p>Total Jobs </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <i class="bi bi-cart h1"></i>
                    <div class="text-end">
                        <h3>{{ $total_job_seeker }}</h3>
                        <p>Total Resume</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>Last 30 days New Jobseeker and Employer </h5>
                <div id="reportsChart"></div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>Last 30 days New Job and Applications </h5>
                <div id="jobsChart"></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>Job post by category </h5>
                <div id="piechart"></div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5>Recent Registred Employer </h5>
                <div id="recent_emp"></div>
            </div>
        </div>
    </div>

</div>
