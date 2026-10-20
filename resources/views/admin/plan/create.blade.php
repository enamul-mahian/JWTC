@extends('dashboard.layout.dasboardMaster')
@section('title', 'Plan create')
@section('content')
    <section class="pc-container">
        <div class="pc-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="h4"> Plan create</span>
                            @can('plan.list')
                                <a href="{{ route('plans.index') }}" class="btn btn-warning float-end rounded-2">Plan List
                                </a>
                            @endcan
                        </div>
                        <div class="card-body table-border-style">
                            <form action="{{ route('plans.store') }}" method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name') }}"
                                            class="form-control" placeholder="Plan Name">
                                        @error('name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Price <span class="text-danger">*</span></label>
                                        <input type="number" step=".01" name="price" value="{{ old('price') }}"
                                            class="form-control" placeholder="Plan Price">
                                        @error('price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Signup Fee <span class="text-danger">*</span></label>
                                        <input type="number" step=".01" name="signup_fee"
                                            value="{{ old('signup_fee') }}" class="form-control" placeholder="Signup Fee">
                                        @error('signup_fee')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="title">Plan Type <span class="text-danger">*</span></label>
                                        <select name="plan_type" id="" class="form-select">
                                            <option value="">select plan type</option>
                                            @foreach (plan_type() as $key => $plan_type)
                                                <option value="{{ $key }}"> {{ ucfirst($plan_type) }} </option>
                                            @endforeach
                                        </select>
                                        @error('plan_type')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <label for="title">Invoice Period <span class="text-danger">*</span></label>
                                        <input type="number" name="invoice_period" value="{{ old('invoice_period') }}"
                                            class="form-control" placeholder="Invoice Period">
                                        @error('invoice_period')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Invoice Interval <span class="text-danger">*</span></label>
                                        <input type="text" step=".01" name="invoice_interval" value="month"
                                            class="form-control" placeholder="Invoice Interval" readonly>
                                        <small class="text-danger">* Month value </small>
                                        @error('invoice_interval')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Trial Period <span class="text-danger">*</span></label>
                                        <input type="number" step=".01" name="trial_period"
                                            value="{{ old('trial_period') }}" class="form-control"
                                            placeholder="Trial Period">
                                        @error('trial_period')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Trial Interval <span class="text-danger">*</span></label>
                                        <input type="text" name="trial_interval" value="day" class="form-control"
                                            placeholder="Trial Interval" readonly>
                                        <small class="text-danger">* Day value </small>
                                        @error('trial_interval')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="title">Sort Order <span class="text-danger">*</span></label>
                                        <input type="number" step=".01" name="sort_order"
                                            value="{{ old('sort_order') }}" class="form-control" placeholder="Sort Order">
                                        @error('sort_order')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="title">Currency <span class="text-danger">*</span></label>
                                        <input type="text" name="currency" value="USD" class="form-control"
                                            placeholder="Currency">
                                        @error('currency')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <label for="title">Description <span class="text-danger">*</span></label>
                                        <input type="text" name="description" value="{{ old('description') }}"
                                            class="form-control" placeholder="Description">
                                        @error('description')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <hr>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3>Plan Features </h3>
                                            <button class="btn btn-primary mb-3" id="add"
                                                type="button">Add</button>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="dynamic_field">
                                                <tr>
                                                    <td>
                                                        <input type="text" name="features[0][name]"
                                                            class="form-control" placeholder="Feature Name">
                                                    </td>

                                                    <td class="w-25">
                                                        <input type="text" name="features[0][features_type]"
                                                            class="form-control" placeholder="features type">
                                                    </td>

                                                    <td>
                                                        <input type="text" name="features[0][value]"
                                                            class="form-control" placeholder="Feature Value">
                                                    </td>

                                                    <td>
                                                        <input type="number" step=".01"
                                                            name="features[0][sort_order]" class="form-control"
                                                            placeholder="Feature sort order">
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="row py-4">
                                        <button class="btn btn-primary" type="submit">Create</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@push('script')
    <script>
        $(document).ready(function() {

            var i = 0;
            $('#add').click(function() {
                i++;
                $('#dynamic_field').append('<tr id="row' + i +
                    '" class="dynamic-added"><td><input type="text" name="features[' + i +
                    '][name]" class="form-control" placeholder="Feature Name"></td>' +
                    '<td class="w-25"><input type="text" name="features[' + i +
                    '][features_type]" class="form-control"  placeholder="features type"></td>' +
                    '<td><input type="text" name="features[' + i +
                    '][value]" class="form-control" placeholder="Feature Value"></td>' +
                    '<td><input type="number" step=".01" name="features[' + i +
                    '][sort_order]" class="form-control" placeholder="Feature sort order"></td>' +
                    '<td><button type="button" name="remove" id="' + i +
                    '" class="btn btn-danger btn_remove">X</button></td></tr>');
            });

            $(document).on('click', '.btn_remove', function() {
                var button_id = $(this).attr("id");
                $('#row' + button_id + '').remove();
            });
        });
    </script>
@endpush
