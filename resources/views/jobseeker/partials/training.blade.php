<table class="table">
    <thead>
        <tr>
            <th>{{ __('messages.Country') }}</th>
            <th>{{ __('messages.TrainingTitle') }}</th>
            <th>{{ __('messages.TopicsCovered') }}</th>
            <th>{{ __('messages.TrainingYear') }}</th>
            <th>{{ __('messages.Institute') }}</th>
            <th>{{ __('messages.Duration') }}</th>
            <th>{{ __('messages.Action') }}</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($user->trainings as $index => $training)
    <tr>
        <td width="20%">{{ $training->country }}</td>
        <td width="30%">{{ $training->title }}</td>
        <td width="20%">{{ $training->topic }}</td>
        <td width="20%">{{ $training->training_year }}</td>
        <td width="20%">{{ $training->training_institute }}</td>
        <td width="20%">{{ $training->training_duration }}</td>
        <td width="10%">
            <div class="add-post-btn d-flex">
                <div class="float-left">
                    <a href="{{ route('edit-training', $training->id) }}" class="btn-added btn_modal mr-2"><i class="lni lni-pencil"></i></a>
                </div>
                <div class="float-right">
                    <form action="{{ route('delete-training', $training->id) }}" method="post">
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