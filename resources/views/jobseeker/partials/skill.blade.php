<table class="table">
    <thead>
        <tr>
            <th>{{ __('messages.SkillName') }}</th>
            <th>{{ __('messages.Proficiency') }}</th>
            <th>{{ __('messages.Action') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($user->skills as $index => $skill)
        <tr>
            <td width="45%">{{ $skill ? $skill->skill_name : null }}</td>
            <td width="45%">{{ $skill ? $skill->skill_proficiency : null }}</td>
            <td width="10%">
                <div class="add-post-btn d-flex">
                    <div class="float-left">
                        <a href="{{ route('edit-skill', $skill->id) }}" class="btn-added btn_modal mr-2"><i class="lni lni-pencil"></i></a>
                    </div>
                    <div class="float-right">
                        <form action="{{ route('delete-skill', $skill->id) }}" method="post">
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
