<table class="table">
    <thead>
        <tr>
            <th>{{ __('messages.Language') }}</th>
            <th>{{ __('messages.Listening') }}</th>
            <th>{{ __('messages.Reading') }}</th>
            <th>{{ __('messages.Writting') }}</th>
            <th>{{ __('messages.Action') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($user->languages as $index => $lang)
        <tr>
            <td width="30%">{{ $lang ? $lang->language_name : null }}</td>
            <td width="20%">{{ $lang->listening_proficiency }}</td>
            <td width="20%">{{ $lang->reading_proficiency }}</td>
            <td width="20%">{{ $lang->writting_proficiency }}</td>
            <td width="10%">
                <div class="add-post-btn d-flex">
                    <div class="float-left">
                        <a href="{{ route('edit-language', $lang->id) }}" class="btn-added btn_modal mr-2"><i class="lni lni-pencil"></i></a>
                    </div>
                    <div class="float-right">
                        <form action="{{ route('delete-language', $lang->id) }}" method="post">
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