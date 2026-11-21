@extends('layouts.master')

@section('content')
<div class="container-fluid members-page">
    <div class="members-page-header">
        <div>
            <h1>Members</h1>
            <p>Manage member profiles, contact information, and membership status.</p>
        </div>
        @can('create members')
            <a href="{{ route('members.create') }}" class="btn btn-primary">
                <i class="ti-plus me-1" aria-hidden="true"></i> Add member
            </a>
        @endcan
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
                <h2>Member directory</h2>
                <small class="text-muted">{{ $members->total() }} {{ \Illuminate\Support\Str::plural('member', $members->total()) }}</small>
            </div>
            <form action="{{ route('members.index') }}" method="GET" class="members-search" role="search">
                <label for="member-search" class="visually-hidden">Search members</label>
                <input
                    id="member-search"
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
                    <a href="{{ route('members.index') }}" class="btn btn-light border" aria-label="Clear search">Clear</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table members-table">
                <thead>
                    <tr>
                        <th scope="col">Member</th>
                        <th scope="col">Contact</th>
                        <th scope="col">Gender</th>
                        <th scope="col">Joined</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Actions</th>
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
                            <td>
                                @if ((string) $member->gender === '0')
                                    Male
                                @elseif ((string) $member->gender === '1')
                                    Female
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td>{{ $member->joining_date ? $member->joining_date->format('M d, Y') : '—' }}</td>
                            <td>
                                @if ($member->status === 'active')
                                    <span class="member-status member-status-active">Active</span>
                                @else
                                    <span class="member-status member-status-inactive">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="member-actions">
                                    @can('edit members')
                                        <a
                                            href="{{ route('members.edit', $member) }}"
                                            class="btn btn-light border text-primary"
                                            aria-label="Edit {{ $member->name }}"
                                            title="Edit member"
                                        >
                                            <i class="ti-pencil" aria-hidden="true"></i>
                                        </a>
                                    @endcan
                                    @can('delete members')
                                        <form
                                            action="{{ route('members.destroy', $member) }}"
                                            method="POST"
                                            onsubmit="return confirm('Delete this member? This action cannot be undone.');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="btn btn-light border text-danger"
                                                aria-label="Delete {{ $member->name }}"
                                                title="Delete member"
                                            >
                                                <i class="ti-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="members-empty">
                                <strong>{{ request()->filled('search') ? 'No matching members found' : 'No members yet' }}</strong>
                                <span class="text-muted">
                                    {{ request()->filled('search') ? 'Try another name, email address, or member code.' : 'Add a member to get started.' }}
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <div class="members-pagination">
                {{ $members->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection