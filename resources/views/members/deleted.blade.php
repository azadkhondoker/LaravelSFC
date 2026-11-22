@extends('layouts.master')

@section('content')
<div class="container-fluid members-page">
    <div class="members-page-header">
        <div>
            <h1>Deleted Members</h1>
            <p>Restore members removed from the active member and installment lists.</p>
        </div>
        <a href="{{ route('members.index') }}" class="btn btn-light border">
            <i class="ti-arrow-left me-1" aria-hidden="true"></i> Back to members
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card members-card">
        <div class="card-header">
            <div>
                <h2>Deleted member records</h2>
                <small class="text-muted">{{ $members->total() }} {{ \Illuminate\Support\Str::plural('member', $members->total()) }}</small>
            </div>
            <form action="{{ route('members.deleted') }}" method="GET" class="members-search" role="search">
                <label for="deleted-member-search" class="visually-hidden">Search deleted members</label>
                <input
                    id="deleted-member-search"
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    placeholder="Search name, email or member code"
                >
                <button type="submit" class="btn btn-primary" aria-label="Search">
                    <i class="ti-search" aria-hidden="true"></i>
                </button>
                @if (request()->filled('search'))
                    <a href="{{ route('members.deleted') }}" class="btn btn-light border">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table members-table">
                <thead>
                    <tr>
                        <th scope="col">Member</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Deleted on</th>
                        <th scope="col" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td>
                                <span class="member-name">{{ $member->name }}</span>
                                <span class="member-code">{{ $member->member_code }}</span>
                            </td>
                            <td>
                                <span class="member-contact-email">{{ $member->email }}</span>
                                @if ($member->phone)
                                    <span class="member-contact-phone">{{ $member->phone }}</span>
                                @endif
                            </td>
                            <td>{{ $member->deleted_at->format('M d, Y') }}</td>
                            <td>
                                @can('delete members')
                                    <div class="member-actions">
                                        <form action="{{ route('members.restore', $member) }}" method="POST">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="btn btn-light border text-success"
                                                aria-label="Restore {{ $member->name }}"
                                                title="Restore member"
                                            >
                                                <i class="ti-back-left" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="members-empty">
                                <strong>{{ request()->filled('search') ? 'No matching deleted members' : 'No deleted members' }}</strong>
                                <span class="text-muted">
                                    {{ request()->filled('search') ? 'Try another name, email address, or member code.' : 'Deleted members will appear here and can be restored.' }}
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <div class="members-pagination">
                {{ $members->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
