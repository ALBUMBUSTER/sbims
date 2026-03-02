@extends('layouts.app')

@section('title', 'Edit Blotter')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <div class="page-title">
            <h1>Edit Blotter Case</h1>
            <p>Update blotter information</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('captain.blotters.show', $blotter) }}" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to Details
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('captain.blotters.update', $blotter) }}" method="POST">
                @csrf
                @method('PUT')
                @include('secretary.blotter._form', ['blotter' => $blotter])

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"></path>
                            <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"></polygon>
                        </svg>
                        Update Case
                    </button>
                    <a href="{{ route('captain.blotters.show', $blotter) }}" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.container-fluid {
    padding: 1.5rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title h1 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1.8rem;
}

.page-title p {
    color: #666;
    font-size: 1rem;
}

.page-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-primary, .btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s;
    cursor: pointer;
    border: none;
    font-weight: 500;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.btn-primary:hover { opacity: 0.9; }

.btn-secondary {
    background: white;
    color: #667eea;
    border: 1px solid #667eea;
}
.btn-secondary:hover { background: #eef2ff; }

.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-body {
    padding: 2rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e2e8f0;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }

    .btn-primary, .btn-secondary {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush
