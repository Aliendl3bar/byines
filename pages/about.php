<?php
session_start();
$pageTitle = 'About Us';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $success = 'Thank you for your message, ' . htmlspecialchars($name) . '! We will get back to you shortly.';
    }
}

include '../includes/header.php';
?>
    <link rel="stylesheet" href="../css/about.css">

    <main class="about-page">
        <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <span class="current">About</span>
        </nav>

        <section class="about-intro">
            <h1>Our Story</h1>
            <p>Byines was born from a belief that modesty and elegance are not mutually exclusive. We craft timeless pieces that celebrate grace, sophistication, and the modern woman — because true style is never loud, it is felt.</p>
        </section>

        <section class="about-values">
            <div class="value-card">
                <div class="value-icon material-symbols-outlined">self_improvement</div>
                <h3>Respecting Islamic Dress</h3>
                <p>Every design honours the principles of modest Islamic attire — offering coverage, grace, and dignity while allowing women to express their faith through timeless elegance.</p>
            </div>
            <div class="value-card">
                <div class="value-icon material-symbols-outlined">public</div>
                <h3>Rooted in Heritage</h3>
                <p>Our designs draw from rich cultural traditions, reimagined through a contemporary lens for women everywhere.</p>
            </div>
            <div class="value-card">
                <div class="value-icon material-symbols-outlined">eco</div>
                <h3>Sustainable Mindset</h3>
                <p>We believe in slow fashion — creating fewer, better things that you will treasure season after season.</p>
            </div>
        </section>

        <section class="about-story">
            <div class="about-story-text">
                <h2>Timeless Elegance,<br>Modern Expression</h2>
                <p>Founded with a vision to fill a gap in the modest fashion landscape, Byines brings together heritage craftsmanship and clean, modern design. Each collection is a conversation between the past and the present — honouring tradition while embracing the future.</p>
                <p>From flowing abayas to tailored separates, every garment is designed to empower. We believe that what you wear should be an extension of who you are: confident, graceful, and unapologetically yourself.</p>
            </div>
            <div class="about-story-image">
                <img src="../assets/hero-image/Firefly.jpg" alt="Byines studio">
            </div>
        </section>

        <section class="contact-section" id="contact">
            <h2>Get In Touch</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-info-item">
                        <span class="contact-info-icon material-symbols-outlined">location_on</span>
                        <div>
                            <h4>Visit Us</h4>
                            <p>123 Modest Lane<br>Tangier, Morocco</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <span class="contact-info-icon material-symbols-outlined">mail</span>
                        <div>
                            <h4>Email</h4>
                            <p>hello@byines.com</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <span class="contact-info-icon material-symbols-outlined">phone</span>
                        <div>
                            <h4>Phone</h4>
                            <p>+212 5 1234 5678</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <span class="contact-info-icon material-symbols-outlined">schedule</span>
                        <div>
                            <h4>Store Hours</h4>
                            <p>Mon – Fri: 9:00 AM – 7:00 PM<br>Sat: 10:00 AM – 5:00 PM</p>
                        </div>
                    </div>
                </div>

                <form class="contact-form" method="POST" action="about.php#contact">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name <span style="color:#991b1b;">*</span></label>
                            <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span style="color:#991b1b;">*</span></label>
                            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="message">Message <span style="color:#991b1b;">*</span></label>
                        <textarea id="message" name="message" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
        </section>
    </main>

<?php include '../includes/footer.php'; ?>
