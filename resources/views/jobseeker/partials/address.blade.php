<p>{{ __('messages.PresentAddress') }}</p>
<table class="table">
    <thead>
        <tr>
            <th>{{ __('messages.AddressType') }}</th>
            <th>{{ __('messages.Addressline1') }}</th>
            <th>{{ __('messages.Postalcode') }}</th>
            <th>{{ __('messages.City') }}</th>
            <th>{{ __('messages.Country') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $user->personal_info ? $user->personal_info->present_address_type : null }}</td>
            <td>{{ $user->personal_info ? $user->personal_info->present_address_line_1 : null }}</td>
            <td>{{ $user->personal_info ? $user->personal_info->present_postal_code : null }}</td>
            <td>{{ $user->personal_info ? $user->personal_info->present_city : null }}</td>
            <td>{{ $user->personal_info ? $user->personal_info->present_country : null }}</td>
        </tr>
    </tbody>
</table>
<p> {{ __('messages.PermanentAddress') }}</p>
<table class="table">
    <thead>
        <tr>
            <th>{{ __('messages.AddressType') }}</th>
            <th>{{ __('messages.Addressline1') }}</th>
            <th>{{ __('messages.Postalcode') }}</th>
            <th>{{ __('messages.City') }}</th>
            <th>{{ __('messages.Country') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $user->personal_info ? $user->personal_info->permanent_address_type : null }}</td>
            <td>{{ $user->personal_info ? $user->personal_info->permanent_address_line_1 : null }}</td>
            <td>{{ $user->personal_info ? $user->personal_info->permanent_postal_code : null }}</td>
            <td>{{ $user->personal_info ? $user->personal_info->permanent_city : null }}</td>
            <td>{{ $user->personal_info ? $user->personal_info->permanent_country : null }}</td>
        </tr>
    </tbody>
</table>
