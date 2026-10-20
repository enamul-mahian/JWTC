@extends('dashboard.layout.dasboardMaster')
@section('title', 'Plan Edit')
@section('content')
    <section class="pc-container">
        <div class="pc-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="h4"> Plan Edit</span>
                            <a href="{{ route('plans.index') }}" class="btn btn-warning float-end rounded-2">Plan List</a>
                        </div>
                        <div class="card-body table-border-style">
                            <form action="{{ route('plans.update', $plan->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ $plan->name }}"
                                            class="form-control" placeholder="Plan Name">
                                        @error('name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Price <span class="text-danger">*</span></label>
                                        <input type="number" step=".01" name="price" value="{{ $plan->price }}"
                                            class="form-control" placeholder="Plan Price">
                                        @error('price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Signup Fee <span class="text-danger">*</span></label>
                                        <input type="number" step=".01" name="signup_fee"
                                            value="{{ $plan->signup_fee }}" class="form-control" placeholder="Signup Fee">
                                        @error('signup_fee')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="title">Plan Type <span class="text-danger">*</span></label>
                                        <select name="plan_type" id="" class="form-select">
                                            <option value="">select plan type</option>
                                            @foreach (plan_type() as $key => $plan_type)
                                                <option {{ $key == $plan->plan_type ? 'selected' : '' }}
                                                    value="{{ $key }}"> {{ ucfirst($plan_type) }}
                                                </option>
                                                @error('plan_type')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Invoice Period <span class="text-danger">*</span></label>
                                        <input type="number" name="invoice_period" value="{{ $plan->invoice_period }}"
                                            class="form-control" placeholder="Invoice Period">
                                        @error('invoice_period')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Invoice Interval <span class="text-danger">*</span></label>
                                        <input type="text" step=".01" name="invoice_interval"
                                            value="{{ $plan->invoice_interval }}" class="form-control"
                                            placeholder="Invoice Interval" readonly>
                                        <small class="text-danger">* Month value </small>
                                        @error('invoice_interval')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Trial Period <span class="text-danger">*</span></label>
                                        <input type="number" step=".01" name="trial_period"
                                            value="{{ $plan->trial_period }}" class="form-control"
                                            placeholder="Trial Period">
                                        @error('trial_period')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Trial Interval <span class="text-danger">*</span></label>
                                        <input type="text" name="trial_interval" value="{{ $plan->trial_interval }}"
                                            class="form-control" placeholder="Trial Interval" readonly>
                                        <small class="text-danger">* Day value </small>
                                        @error('trial_interval')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Sort Order <span class="text-danger">*</span></label>
                                        <input type="number" step=".01" name="sort_order"
                                            value="{{ $plan->sort_order }}" class="form-control" placeholder="Sort Order">
                                        @error('sort_order')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="title">Currency <span class="text-danger">*</span></label>
                                        <input type="text" name="currency" value="USD" class="form-control"
                                            placeholder="Currency">
                                        @error('currency')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label for="title">Description <span class="text-danger">*</span></label>
                                        <input type="text" name="description" value="{{ $plan->description }}"
                                            class="form-control" placeholder="Description">
                                        @error('description')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <hr>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3>Plan Features </h3>
                                            <button class="btn btn-primary mb-3" id="add"
                                                type="button">Add</button>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="dynamic_field">
                                                @foreach ($features as $featue)
                                                    <tr>
                                                        <td><input type="text"
                                                                name="features[{{ $loop->iteration }}][name]"
                                                                value="{{ $featue->name }}" class="form-control"
                                                                placeholder="Feature Name">
                                                        </td>
                                                        <td class="w-25">
                                                            <input type="text"
                                                                name="features[{{ $loop->iteration }}][features_type]"
                                                                class="form-control" placeholder="features type"
                                                                value="{{ $featue->feature_type }}">
                                                        </td>
                                                        <td>
                                                            <input type="text"
                                                                name="features[{{ $loop->iteration }}][value]"
                                                                value="{{ $featue->value }}" class="form-control"
                                                                placeholder="Feature Value">
                                                        </td>
                                                        <td>
                                                            <input type="number" step=".01"
                                                                name="features[{{ $loop->iteration }}][sort_order]"
                                                                value="{{ $featue->sort_order }}" class="form-control"
                                                                placeholder="Feature sort order">
                                                        </td>
                                                        <td>
                                                            <button type="button" name="remove"
                                                                class="btn btn-danger remove_td"
                                                                data-id="{{ $featue->id }}">X</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </div>

                                    <div class="row py-4">
                                        <button class="btn btn-primary" type="submit">Update</button>
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
                    '][features_type]" class="form-control"  placeholder="features type"> </td>' +
                    '<td><input type="text" name="features[' + i +
                    '][value]" class="form-control" placeholder="Feature Value"></td>' +
                    '<td><input type="number" step=".01" name="features[' + i +
                    '][sort_order]" class="form-control" placeholder="Feature sort order"></td>' +
                    '<td><button type="button" name="remove" id="' + i +
                    '" class="btn btn-danger btn_remove">X</button></td></tr>');
            });


        });
    </script>

    <script>
        var len = document.querySelectorAll('.remove_td').length;

        for (let i = 0; i < len; i++) {
            document.querySelectorAll('.remove_td')[i].onclick = function() {

                if (confirm('Are you sure?')) {

                    $.ajax({
                        type: 'GET',
                        url: '/feature-delete-via-ajax',
                        data: {
                            data: {
                                id: $(this).data('id'),
                                plan_id: '{{ $plan->id }}'
                            }
                        },
                        success: function(res) {
                            console.log(res);
                            console.log({
                                id: $(this).data('id'),
                                plan_id: '{{ $plan->id }}'
                            })
                        },
                        error: function(err) {
                            console.log(err);
                        }
                    });

                    $(document).on('click', '.btn_remove', function() {
                        var button_id = $(this).attr("id");
                        $('#row' + button_id + '').remove();
                    });

                    $(document).on('click', '.remove_td', function() {
                        $(this).closest('tr').remove();
                    });

                } else {
                    return false
                }
            }
        }
    </script>
@endpush
