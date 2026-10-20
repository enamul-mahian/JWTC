<table class="table">
    <thead>
        <tr>
            <th> {{ __('messages.Degree') }}</th>
            <th>{{ __('messages.InstituteName') }}</th>
            <th>{{ __('messages.Result') }}</th>
            <th>{{ __('messages.Action') }}</th>
            <th>{{ __('messages.Action') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($user->educations as $index => $education)
        <tr>
            <td width="30%">{{ $education?->degreeName?->name }}</td>
            <td width="20%">{{ $education->institute_name ?? null }}</td>
            <td width="20%">{{ $education->result ?? null }}</td>
            <td width="20%">{{ $education->pass_year ?? null }}</td>
            <td width="210%">
                <div class="add-post-btn d-flex">
                    <div class="float-left">
                        <a href="{{ route('edit-education', $education->id) }}" class="btn-added btn_modal mr-2"><i class="lni lni-pencil"></i></a>
                    </div>
                    <div class="float-right">
                        <form action="{{ route('delete-education', $education->id) }}" method="post">
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