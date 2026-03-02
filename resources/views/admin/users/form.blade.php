@extends('layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Create User')

@push('styles')
<style>
    .form-container {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        max-width: 800px;
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #333;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .error-message {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="main-container">
    <main class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>{{ isset($user) ? 'Edit User' : 'Create New User' }}</h1>
                <p>{{ isset($user) ? 'Update user information' : 'Add a new user to the system' }}</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                    <span>⬅️</span> Back to Users
                </a>
            </div>
        </div>

        <div class="form-container">
            <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username"
                               value="{{ old('username', $user->username ?? '') }}"
                               required
                               placeholder="Enter username">
                        @error('username')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email"
                               value="{{ old('email', $user->email ?? '') }}"
                               required
                               placeholder="Enter email address">
                        @error('email')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name"
                               value="{{ old('full_name', $user->full_name ?? '') }}"
                               required
                               placeholder="Enter full name">
                        @error('full_name')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="role_id">Role *</label>
                        <select id="role_id" name="role_id" required>
                            <option value="">Select Role</option>
                            <option value="1" {{ old('role_id', $user->role_id ?? '') == 1 ? 'selected' : '' }}>Admin</option>
                            <option value="2" {{ old('role_id', $user->role_id ?? '') == 2 ? 'selected' : '' }}>Captain</option>
                            <option value="3" {{ old('role_id', $user->role_id ?? '') == 3 ? 'selected' : '' }}>Secretary</option>
                            <option value="4" {{ old('role_id', $user->role_id ?? '') == 4 ? 'selected' : '' }}>Clerk</option>
                        </select>
                        @error('role_id')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">{{ isset($user) ? 'New Password' : 'Password *' }}</label>
                        <input type="password" id="password" name="password"
                               {{ isset($user) ? '' : 'required' }}
                               placeholder="{{ isset($user) ? 'Leave blank to keep current' : 'Enter password' }}">
                        @error('password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               placeholder="Confirm password">
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-outline" style="background: #667eea; color: white; border-color: #667eea;">
                        <span>💾</span> {{ isset($user) ? 'Update User' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
