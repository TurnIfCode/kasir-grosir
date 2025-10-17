<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACE - Jeka.by</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 20px;
        }
        
        nav ul li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        nav ul li a:hover {
            color: #3498db;
        }
        
        .hero {
            background-color: #3498db;
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 20px;
            max-width: 700px;
            margin: 0 auto 30px;
        }
        
        .btn {
            display: inline-block;
            background-color: #fff;
            color: #3498db;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background-color: #f1f1f1;
            transform: translateY(-2px);
        }
        
        .features {
            padding: 80px 0;
            background-color: #fff;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 36px;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .section-title p {
            color: #7f8c8d;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .feature-card {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 40px;
            color: #3498db;
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .about {
            padding: 80px 0;
            background-color: #f5f5f5;
        }
        
        .about-content {
            display: flex;
            align-items: center;
            gap: 50px;
        }
        
        .about-text {
            flex: 1;
        }
        
        .about-image {
            flex: 1;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .about-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .about-text h2 {
            font-size: 36px;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .about-text p {
            margin-bottom: 20px;
            color: #555;
        }
        
        .contact {
            padding: 80px 0;
            background-color: #fff;
        }
        
        .contact-form {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        footer {
            background-color: #2c3e50;
            color: #fff;
            padding: 40px 0;
            text-align: center;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .footer-logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .footer-links {
            display: flex;
            list-style: none;
        }
        
        .footer-links li {
            margin-left: 20px;
        }
        
        .footer-links a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: #3498db;
        }
        
        .copyright {
            margin-top: 20px;
            width: 100%;
            color: #bdc3c7;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin-top: 15px;
                justify-content: center;
            }
            
            .about-content {
                flex-direction: column;
            }
            
            .footer-content {
                flex-direction: column;
            }
            
            .footer-links {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">ACE</div>
                <nav>
                    <ul>
                        <li><a href="#">Beranda</a></li>
                        <li><a href="#">Fitur</a></li>
                        <li><a href="#">Tentang</a></li>
                        <li><a href="#">Kontak</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1>Solusi Terbaik untuk Kebutuhan Anda</h1>
            <p>Kami menyediakan layanan berkualitas tinggi dengan teknologi terkini untuk membantu bisnis Anda berkembang pesat.</p>
            <a href="#" class="btn">Pelajari Lebih Lanjut</a>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Fitur Unggulan</h2>
                <p>Kami menawarkan berbagai fitur yang akan membantu Anda mencapai tujuan bisnis dengan lebih efisien.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Performa Tinggi</h3>
                    <p>Solusi kami dirancang untuk memberikan performa optimal dengan kecepatan yang luar biasa.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Keamanan Terjamin</h3>
                    <p>Dengan sistem keamanan berlapis, data Anda akan selalu terlindungi dengan baik.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔄</div>
                    <h3>Terus Berkembang</h3>
                    <p>Kami terus berinovasi dan memperbarui layanan untuk memenuhi kebutuhan Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Tentang Kami</h2>
                    <p>Kami adalah perusahaan teknologi yang berfokus pada pengembangan solusi inovatif untuk berbagai industri. Dengan pengalaman lebih dari 10 tahun, kami telah membantu ratusan klien mencapai tujuan bisnis mereka.</p>
                    <p>Tim kami terdiri dari profesional berpengalaman yang selalu siap memberikan layanan terbaik dengan pendekatan yang personal dan solusi yang tepat.</p>
                    <a href="#" class="btn">Hubungi Kami</a>
                </div>
                <div class="about-image">
                    <!-- Placeholder untuk gambar -->
                    <div style="background-color: #3498db; height: 300px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                        Gambar Perusahaan
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="contact">
        <div class="container">
            <div class="section-title">
                <h2>Hubungi Kami</h2>
                <p>Jangan ragu untuk menghubungi kami jika Anda memiliki pertanyaan atau membutuhkan informasi lebih lanjut.</p>
            </div>
            <form class="contact-form">
                <div class="form-group">
                    <label for="name">Nama</label>
                    <input type="text" id="name" class="form-control" placeholder="Masukkan nama Anda">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" class="form-control" placeholder="Masukkan email Anda">
                </div>
                <div class="form-group">
                    <label for="message">Pesan</label>
                    <textarea id="message" class="form-control" placeholder="Tulis pesan Anda di sini"></textarea>
                </div>
                <button type="submit" class="btn">Kirim Pesan</button>
            </form>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">ACE</div>
                <ul class="footer-links">
                    <li><a href="#">Beranda</a></li>
                    <li><a href="#">Fitur</a></li>
                    <li><a href="#">Tentang</a></li>
                    <li><a href="#">Kontak</a></li>
                </ul>
                <div class="copyright">
                    <p>&copy; 2023 ACE. Semua hak dilindungi.</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>