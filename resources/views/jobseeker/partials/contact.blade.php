<table class="table">
    <thead>
        <tr>
            <th>{{ __('messages.Email') }}</th>
            <th>{{ __('messages.AlternateEmail') }}</th>
            <th>{{ __('messages.Phonenumber') }}</th>
            <th>{{ __('messages.AlternatePhonenumber') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $user->personal_info ? $user->personal_info->email : null }}</td>
            <td>{{ $user->personal_info ? $user->personal_info->alternate_email : null }}</td>
            <td>{{ $user->personal_info ? $user->personal_info->phone : null }}</td>
            <td>{{ $user->personal_info ? $user->personal_info->alternate_phone : null }}</td>
        </tr>
    </tbody>
</table>