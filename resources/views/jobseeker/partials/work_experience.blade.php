<table class="table">
    <thead>
        <tr>
            <td>{{ __('messages.Occupationorpositionheld') }}</td>
            <td>{{ __('messages.Employer') }}</td>
            <td>{{ __('messages.City') }}</td>
            <td>{{ __('messages.Country') }}</td>
            <td>{{ __('messages.FromDate') }}</td>
            <td>{{ __('messages.ToDate') }}</td>
            <th>{{ __('messages.Action') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($user->work_experiences as $index => $work)
        <tr>
            <td>{{ $work->position_name ? $work->position_name : null }}</td>
            <td>{{ $work->employeer ? $work->employeer : null }}</td>
            <td>{{ $work->city ? $work->city : null }}</td>
            <td>{{ $work->occupation_country }}</td>
            <td>{{ $work->from_date ? $work->from_date : null }}</td>
            <td>{{ $work->continuing == 1 ? 'Continuing here' : $work->to_date }} </td>
            <td>
                <div class="add-post-btn d-flex">
                    <div class="float-left">
                        <a href="{{ route('edit-work-experience', $work->id) }}" class="btn-added btn_modal mr-2"><i class="lni lni-pencil"></i></a>
                    </div>
                    <div class="float-right">
                        <form action="{{ route('delete-work-experience', $work->id) }}" method="POST">
                            @csrf
                            @method('delete')
                            <a href="javascript:void(0);" class="show_confirm btn-delete"><i class="lni lni-archive"></i></a>
                        </form>
                    </div>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>