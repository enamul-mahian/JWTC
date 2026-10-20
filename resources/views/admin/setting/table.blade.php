<table class="table table-hover">
    <thead>
        <tr>
            <th>SL</th>
            <th>Date</th>
            <th>Description</th>
            <th>User Name &amp; Role</th>
            <th>IP Address</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $log)
        <tr>
            <td>{{ $loop->index +1 }}</td>
            <td>{{ $log->created_at }}</td>
            <td>{{ $log->description }}</td>
            <td>{{ $log->user->full_name }} - {{ $log->user->roles->first()->name }}</td>
            <td>{{ $log->ip_address }}</td>
        </tr>
        @endforeach 
    </tbody>
</table>
<div class="mt-2">
{!! $items->links() !!}
</div>