@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1>Loan clients</h1><p class="text-muted mb-0">Non-members who can be selected when enrolling a loan.</p></div>
        <a href="{{ route('investments.clients.create') }}" class="btn btn-primary">Register client</a>
    </div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>NID</th><th>Address</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse ($clients as $client)
                <tr>
                    <td>{{ $client->name }}</td>
                    <td>{{ $client->phone ?: '—' }}</td>
                    <td>{{ $client->email ?: '—' }}</td>
                    <td>{{ $client->nid ?: '—' }}</td>
                    <td>{{ $client->address ?: '—' }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('investments.clients.edit', $client) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('investments.clients.destroy', $client) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this loan client?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4">No clients registered yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div>
    <div class="mt-3">{{ $clients->links() }}</div>
</div>
@endsection
