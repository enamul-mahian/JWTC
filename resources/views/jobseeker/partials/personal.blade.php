<div class="table-container">
<table class="table">
    <thead>
        <tr>
            <th>{{ __('messages.Firstname') }}</th>
            <th>{{ __('messages.Lastnames') }}</th>
            <th>{{ __('messages.Dateofbirth') }}</th>
            <th>{{ __('messages.Gender') }}</th>
            <th>{{ __('messages.Nationality') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $user->personal_info->first_name? $user->personal_info->first_name : null }}</td>
            <td>{{ $user->personal_info? $user->personal_info->last_name : null }}</td>
            <td>{{ $user->personal_info ? $user->personal_info->dob : null }}</td>
            <td>{{ $user->personal_info && $user->personal_info->gender ? $user->personal_info->gender : '' }}</td>
            <td>{{ $user->personal_info && $user->personal_info->nationality ? $user->personal_info->nationality: '' }}</td>
        </tr>
    </tbody>
</table>
</div>