@extends('dashboard.layout.dasboardMaster')
@section('title', 'Plans')
@section('content')
    <section class="pc-container">
        <div class="pc-content">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="h3">All Plans</span>
                            {{-- @can('plan.create') --}}
                                <a href="{{ route('plans.create') }}" class="btn btn-primary float-end rounded-2">Add New Plan
                                </a>
                            {{-- @endcan --}}
                        </div>
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sprite" id="example">
                                    <thead>
                                        <tr>
                                            <th>Sl.</th>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Plan Type</th>
                                            <th>Signup Fee </th>
                                            <th>Status</th>
                                            {{-- @canany(['plan.edit', 'plan.delete', 'plan.show']) --}}
                                                <th>Action</th>
                                            {{-- @endcanany --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($plans as $plan)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $plan->name }}</td>
                                                <td>{{ $plan->price }}</td>
                                                <td>{{ ucfirst($plan->plan_type) }}</td>
                                                <td>{{ $plan->signup_fee }}</td>
                                                <td>
                                                    @if ($plan->is_active == 1)
                                                        <span class="badge bg-secondary">Active</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        {{--                                            @can('plan.show') --}}
                                                        {{--                                            <a href="{{ route('plans.show', $plan->id) }}" class="btn btn-info btn-sm me-2"> --}}
                                                        {{--                                                <span class="fa fa-book-open"></span> --}}
                                                        {{--                                            </a> --}}
                                                        {{--                                            @endcan --}}

                                                        {{-- @can('plan.edit') --}}
                                                            <a href="{{ route('plans.edit', $plan->id) }}"
                                                                class="btn btn-primary btn-sm me-2">
                                                                <span class="fa fa-edit"></span>
                                                            </a>
                                                        {{-- @endcan --}}

                                                        {{-- @can('plan.delete') --}}
                                                            <form action="{{ route('plans.destroy', $plan->id) }}"
                                                                method="post" id="deleteForm_{{ $plan->id }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" class="btn btn-danger btn-sm btnDelete" data-id="{{ $plan->id }}">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        {{-- @endcan --}}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script>
        $('.btnDelete').on('click', function() {
            let id = $(this).attr('data-id');

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#deleteForm_' + id).submit();
                }
            });
        })
    </script>
@endpush
