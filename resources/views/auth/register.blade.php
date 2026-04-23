@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="auth-page">
    <div class="auth-hero">
        <div class="parallax-background">
            <img src="https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=1920&h=1080&fit=crop" alt="Background" class="parallax-image" id="parallaxImage">
            <div class="parallax-overlay"></div>
        </div>
        <div class="auth-hero-content">
            <img src="{{ asset('img/waste.png') }}" alt="Waste App Icon" class="auth-logo">
            <h1>Aplikasi Pelaporan Sampah</h1>
            <p>Bergabunglah dengan kami untuk melaporkan sampah</p>
        </div>
        </div>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-card-header">
                <h2><i class="fas fa-user-plus"></i> Daftar Akun</h2>
            </div>

            <div class="auth-card-body">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="form-row">
                            <div class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Masukkan nama lengkap">
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="form-group">
                            <label for="username">Username</label>
                            <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" placeholder="Masukkan username">
                                @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                        </div>
                    </div>

                    <div class="form-row">
                            <div class="form-group">
                            <label for="email">Email</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Masukkan email">
                                @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="form-group">
                            <label for="phone">Nomor Telepon</label>
                            <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="Masukkan nomor telepon">
                                @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Alamat</label>
                        <textarea id="address" class="form-control @error('address') is-invalid @enderror" name="address" required rows="3" placeholder="Masukkan alamat lengkap">{{ old('address') }}</textarea>
                        @error('address')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <div class="form-row">
                            <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-wrapper">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Masukkan password">
                                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password', 'password-icon')">
                                    <i class="fas fa-eye" id="password-icon"></i>
                                </button>
                            </div>
                                @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                            <div class="form-group">
                            <label for="password-confirm">Konfirmasi Password</label>
                            <div class="password-wrapper">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Konfirmasi password">
                                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password-confirm', 'password-confirm-icon')">
                                    <i class="fas fa-eye" id="password-confirm-icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-auth">
                        <i class="fas fa-user-plus"></i> Daftar
                    </button>
                </form>
            </div>

            <div class="auth-card-footer">
                <p>Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .auth-page {
        min-height: 100vh;
        display: flex;
        position: relative;
        overflow: hidden;
    }

    .auth-hero {
        flex: 1;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        overflow: hidden;
    }

    .parallax-background {
        position: absolute;
        top: -20%;
        left: 0;
        width: 100%;
        height: 120%;
        z-index: 1;
    }

    .parallax-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        will-change: transform;
    }

    .parallax-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 2;
    }

    .auth-hero-content {
        position: relative;
        z-index: 3;
        text-align: center;
        color: white;
        padding: 2rem;
    }

    .auth-logo {
        width: 80px;
        height: 80px;
        margin-bottom: 1.5rem;
        filter: brightness(0) invert(1);
    }

    .auth-hero-content h1 {
        font-size: clamp(1.75rem, 4vw, 2.5rem);
        font-weight: 700;
        margin-bottom: 1rem;
        letter-spacing: -0.02em;
    }

    .auth-hero-content p {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    .auth-container {
        width: 100%;
        max-width: 600px;
        background: #ffffff;
        display: flex;
        align-items: center;
        padding: 2rem;
        overflow-y: auto;
    }

    .auth-card {
        width: 100%;
    }

    .auth-card-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .auth-card-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a1a1a;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #374151;
        font-weight: 500;
        font-size: 0.95rem;
    }

    .form-control {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
        color: #374151;
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a1a1a;
        box-shadow: 0 0 0 3px rgba(26, 26, 26, 0.1);
    }

    .form-control.is-invalid {
        border-color: #ef4444;
    }

    .invalid-feedback {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: block;
    }

    .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-wrapper .form-control {
        padding-right: 3rem;
    }

    .btn-toggle-password {
        position: absolute;
        right: 0.75rem;
        background: none;
        border: none;
        color: #6b7280;
        padding: 0.5rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
        z-index: 10;
    }

    .btn-toggle-password:hover {
        color: #1a1a1a;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .btn-auth {
        width: 100%;
        background: #1a1a1a;
        color: white;
        padding: 1rem;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-auth:hover {
        background: #333;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .auth-card-footer {
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }

    .auth-card-footer p {
        color: #666;
        font-size: 0.95rem;
    }

    .auth-card-footer a {
        color: #1a1a1a;
        text-decoration: none;
        font-weight: 600;
    }

    .auth-card-footer a:hover {
        text-decoration: underline;
    }

    @media (max-width: 968px) {
        .auth-page {
            flex-direction: column;
        }

        .auth-hero {
            min-height: 50vh;
        }

        .auth-container {
            max-width: 100%;
            padding: 2rem 1.5rem;
        }

        .form-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .auth-hero-content h1 {
            font-size: 1.5rem;
        }

        .auth-hero-content p {
            font-size: 0.95rem;
        }

        .auth-logo {
            width: 60px;
            height: 60px;
        }

        .auth-card-body {
            padding: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const parallaxImage = document.getElementById('parallaxImage');
        
        if (parallaxImage) {
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const rate = scrolled * 0.3;
                parallaxImage.style.transform = `translateY(${rate}px)`;
            });
        }
    });

    function togglePasswordVisibility(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const passwordIcon = document.getElementById(iconId);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.classList.remove('fa-eye');
            passwordIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            passwordIcon.classList.remove('fa-eye-slash');
            passwordIcon.classList.add('fa-eye');
        }
    }
</script>
@endpush
