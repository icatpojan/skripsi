@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<!-- Hero Section with Parallax -->
<div class="hero-section" id="heroSection">
    <div class="parallax-background">
        <img src="https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=1920&h=1080&fit=crop" alt="Background" class="parallax-image" id="parallaxImage">
        <div class="parallax-overlay"></div>
    </div>
    
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title">
                Aplikasi Pelaporan Sampah
            </h1>
            <p class="hero-description">
                Mari bersama-sama menjaga lingkungan kita tetap bersih dan sehat.
                Laporkan sampah yang Anda temukan dan bantu kami menciptakan lingkungan yang lebih baik.
            </p>
            <div class="hero-actions">
                @guest
                <a href="{{ route('register') }}" class="btn-primary">
                    Mulai Sekarang
                </a>
                <a href="{{ route('login') }}" class="btn-secondary">
                    Masuk ke Akun
                </a>
                @else
                <a href="{{ route('dashboard') }}" class="btn-primary">
                    Dashboard
                </a>
                <a href="{{ route('waste-reports.create') }}" class="btn-secondary">
                    Laporkan Sampah
                </a>
                @endguest
            </div>
        </div>
    </div>
    
    <div class="scroll-indicator">
        <div class="scroll-arrow"></div>
    </div>
</div>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <img src="https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=400&h=400&fit=crop" alt="Mudah Digunakan">
                </div>
                <h3>Mudah Digunakan</h3>
                <p>Interface yang sederhana dan intuitif untuk semua pengguna</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?w=400&h=400&fit=crop" alt="GPS Akurat">
                </div>
                <h3>GPS Akurat</h3>
                <p>Pelacakan lokasi yang presisi untuk laporan yang akurat</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <img src="https://images.unsplash.com/photo-1559028012-481c04fa702d?w=400&h=400&fit=crop" alt="Real-time">
                </div>
                <h3>Real-time</h3>
                <p>Update status laporan secara langsung dan transparan</p>
            </div>
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="process-section" id="processSection">
    <div class="container">
        <div class="section-intro">
            <h2>4 Langkah Mudah Melapor</h2>
            <p>Empat langkah sederhana untuk melaporkan sampah dan menjaga lingkungan tetap bersih</p>
        </div>

        <div class="process-list">
            <div class="process-step">
                <div class="step-number">01</div>
                <div class="step-content">
                    <h3>Daftar & Login</h3>
                    <p>Buat akun gratis atau login ke aplikasi dengan mudah. Proses pendaftaran hanya membutuhkan beberapa menit.</p>
                </div>
            </div>

            <div class="process-step">
                <div class="step-number">02</div>
                <div class="step-content">
                    <h3>Ambil Foto</h3>
                    <p>Foto sampah yang ditemukan dengan kamera berkualitas tinggi. Pastikan foto jelas dan menunjukkan kondisi sampah.</p>
                </div>
            </div>

            <div class="process-step">
                <div class="step-number">03</div>
                <div class="step-content">
                    <h3>Isi Informasi</h3>
                    <p>Lengkapi detail lokasi, jenis sampah, dan keterangan. Sistem akan otomatis mendeteksi lokasi GPS Anda.</p>
                </div>
            </div>

            <div class="process-step">
                <div class="step-number">04</div>
                <div class="step-content">
                    <h3>Kirim & Pantau</h3>
                    <p>Submit laporan dan pantau status penanganan secara real-time. Tim akan segera menindaklanjuti laporan Anda.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Siap Bergabung?</h2>
            <p>Mari bersama-sama menciptakan lingkungan yang lebih bersih dan sehat</p>
            <div class="cta-actions">
                @guest
                <a href="{{ route('register') }}" class="btn-primary">
                    Daftar Sekarang
                </a>
                <a href="{{ route('login') }}" class="btn-secondary">
                    Masuk
                </a>
                @else
                <a href="{{ route('waste-reports.create') }}" class="btn-primary">
                    Laporkan Sampah
                </a>
                <a href="{{ route('dashboard') }}" class="btn-secondary">
                    Dashboard
                </a>
                @endguest
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        line-height: 1.6;
        color: #1a1a1a;
        overflow-x: hidden;
    }

    /* Navbar Transparent Fixed */
    #mainNavbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background: rgba(255, 255, 255, 0);
        backdrop-filter: blur(0px);
        transition: all 0.3s ease;
        padding: 1.5rem 0;
    }

    #mainNavbar.navbar-scrolled {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        padding: 1rem 0;
    }

    #mainNavbar.navbar-scrolled .navbar-brand,
    #mainNavbar.navbar-scrolled .nav-link {
        color: #1a1a1a !important;
    }

    #mainNavbar .navbar-brand,
    #mainNavbar .nav-link {
        color: #ffffff !important;
        transition: color 0.3s ease;
    }

    /* Hero Section */
    .hero-section {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
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

    .hero-content {
        position: relative;
        z-index: 3;
        max-width: 800px;
        padding: 0 2rem;
        text-align: center;
        color: white;
    }

    .hero-title {
        font-size: clamp(2.5rem, 5vw, 4.5rem);
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 1.5rem;
        letter-spacing: -0.02em;
    }

    .hero-description {
        font-size: clamp(1rem, 2vw, 1.25rem);
        line-height: 1.8;
        margin-bottom: 3rem;
        opacity: 0.95;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .hero-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-primary {
        display: inline-block;
        background: #ffffff;
        color: #1a1a1a;
        padding: 1rem 2.5rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
        text-decoration: none;
        color: #1a1a1a;
    }

    .btn-secondary {
        display: inline-block;
        background: transparent;
        color: #ffffff;
        padding: 1rem 2.5rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        border: 2px solid rgba(255, 255, 255, 0.5);
    }

    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.8);
        transform: translateY(-2px);
        text-decoration: none;
        color: #ffffff;
    }

    .scroll-indicator {
        position: absolute;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 3;
    }

    .scroll-arrow {
        width: 30px;
        height: 30px;
        border-right: 2px solid white;
        border-bottom: 2px solid white;
        transform: rotate(45deg);
        animation: scrollBounce 2s infinite;
    }

    @keyframes scrollBounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0) rotate(45deg);
        }
        40% {
            transform: translateY(-10px) rotate(45deg);
        }
        60% {
            transform: translateY(-5px) rotate(45deg);
        }
    }

    /* Features Section */
    .features-section {
        padding: 8rem 0;
        background: #ffffff;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 4rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .feature-item {
        text-align: center;
    }

    .feature-icon {
        width: 120px;
        height: 120px;
        margin: 0 auto 2rem;
        border-radius: 50%;
        overflow: hidden;
        position: relative;
    }

    .feature-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .feature-item:hover .feature-icon img {
        transform: scale(1.1);
    }

    .feature-item h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #1a1a1a;
    }

    .feature-item p {
        color: #666;
        line-height: 1.8;
        font-size: 1rem;
    }

    /* Process Section */
    .process-section {
        padding: 8rem 2rem;
        background: #ffffff;
        position: relative;
    }

    .section-intro {
        text-align: center;
        margin-bottom: 5rem;
    }

    .section-intro h2 {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        margin-bottom: 1rem;
        color: #1a1a1a;
        letter-spacing: -0.02em;
    }

    .section-intro p {
        font-size: 1.2rem;
        color: #666;
        max-width: 600px;
        margin: 0 auto;
    }

    .process-list {
        max-width: 1400px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
    }

    .process-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 3rem 2rem;
        background: #f8f9fa;
        border-radius: 20px;
        transition: all 0.3s ease;
        border-top: 4px solid transparent;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        position: relative;
    }

    .process-step:hover {
        background: #ffffff;
        border-top-color: #1a1a1a;
        transform: translateY(-8px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    }

    .step-number {
        font-size: 4rem;
        font-weight: 700;
        color: #1a1a1a;
        opacity: 0.15;
        line-height: 1;
        margin-bottom: 1.5rem;
        font-family: 'Courier New', monospace;
    }

    .step-content {
        flex: 1;
        width: 100%;
    }

    .step-content h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #1a1a1a;
    }

    .step-content p {
        color: #666;
        line-height: 1.7;
        font-size: 1rem;
        margin: 0;
    }

    /* CTA Section */
    .cta-section {
        padding: 8rem 0;
        background: #1a1a1a;
        color: white;
        text-align: center;
    }

    .cta-content h2 {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 700;
        margin-bottom: 1rem;
        letter-spacing: -0.02em;
    }

    .cta-content p {
        font-size: 1.2rem;
        margin-bottom: 3rem;
        opacity: 0.9;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .cta-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .cta-section .btn-primary {
        background: #ffffff;
        color: #1a1a1a;
    }

    .cta-section .btn-primary:hover {
        background: #f0f0f0;
        color: #1a1a1a;
    }

    .cta-section .btn-secondary {
        border-color: rgba(255, 255, 255, 0.5);
        color: #ffffff;
    }

    .cta-section .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.8);
        color: #ffffff;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .hero-content {
            padding: 0 1.5rem;
        }

        .hero-actions {
            flex-direction: column;
            align-items: center;
        }

        .btn-primary,
        .btn-secondary {
            width: 100%;
            max-width: 300px;
        }

        .features-grid {
            gap: 3rem;
        }

        .process-list {
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .process-step {
            padding: 2rem 1.5rem;
        }

        .step-number {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }

        .step-content h3 {
            font-size: 1.25rem;
        }

        .step-content p {
            font-size: 0.95rem;
        }

        .cta-actions {
            flex-direction: column;
            align-items: center;
        }

        .cta-section .btn-primary,
        .cta-section .btn-secondary {
            width: 100%;
            max-width: 300px;
        }
    }

    @media (max-width: 480px) {
        .features-section,
        .process-section,
        .cta-section {
            padding: 4rem 0;
        }

        .section-intro {
            margin-bottom: 3rem;
        }

        .process-list {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .process-step {
            padding: 2rem 1.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Parallax Effect
    document.addEventListener('DOMContentLoaded', function() {
        const parallaxImage = document.getElementById('parallaxImage');
        const heroSection = document.getElementById('heroSection');
        
        if (parallaxImage && heroSection) {
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const rate = scrolled * 0.5;
                
                if (scrolled < heroSection.offsetHeight) {
                    parallaxImage.style.transform = `translateY(${rate}px)`;
                }
            });
        }
    });
</script>
@endpush
