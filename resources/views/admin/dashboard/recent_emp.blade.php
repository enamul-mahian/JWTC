<table class="table table-bordered">
    <thead>
        <tr>
            <th>Sl. No </th>
            <th>Name </th>
            <th>Email </th>
        </tr>
    </thead>
    <tbody>
        @forelse ($employers as $user)
        <tr>
            <td>{{ $loop->index +1 }}</td>
            <td>{{ $user->name  }}</td>
            <td>{{ $user->email }}</td>
        </tr>
        @empty 
        <tr>
            <td colspan="3">No data found !</td>
        </tr>
        @endforelse
    </tbody>
</table>
