<?php
/**
 * OAuth2 Identity Provider - Home Page
 *
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Home');
?>

<style>
    .hero {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        margin-bottom: 3rem;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 800;
        color: var(--gray-900);
        margin-bottom: 1rem;
        line-height: 1.2;
    }

    .hero-subtitle {
        font-size: 1.5rem;
        color: var(--gray-600);
        margin-bottom: 2.5rem;
        font-weight: 400;
    }

    .hero-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .hero-btn {
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .hero-btn-primary {
        background: var(--primary);
        color: white;
    }

    .hero-btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);
    }

    .hero-btn-secondary {
        background: white;
        color: var(--primary);
        border: 2px solid var(--primary);
    }

    .hero-btn-secondary:hover {
        background: var(--gray-50);
        transform: translateY(-3px);
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .feature-card {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        transition: all 0.3s;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }

    .feature-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        color: white;
    }

    .feature-icon svg {
        width: 32px;
        height: 32px;
    }

    .feature-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.75rem;
    }

    .feature-description {
        color: var(--gray-600);
        line-height: 1.6;
    }

    .tech-stack {
        background: white;
        padding: 2.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        margin-bottom: 3rem;
    }

    .tech-stack-title {
        font-size: 2rem;
        font-weight: 700;
        text-align: center;
        color: var(--gray-900);
        margin-bottom: 2rem;
    }

    .tech-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
    }

    .tech-badge {
        padding: 0.75rem 1.5rem;
        background: var(--gray-100);
        border-radius: 8px;
        font-weight: 600;
        color: var(--gray-700);
        border: 2px solid var(--gray-200);
    }

    .cta-section {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 3rem 2rem;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(79, 70, 229, 0.3);
    }

    .cta-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .cta-description {
        font-size: 1.2rem;
        opacity: 0.95;
        margin-bottom: 2rem;
    }

    .cta-btn {
        padding: 1rem 2.5rem;
        background: white;
        color: var(--primary);
        font-size: 1.1rem;
        font-weight: 700;
        border-radius: 10px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
    }

    .cta-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(255, 255, 255, 0.3);
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem;
        }

        .hero-subtitle {
            font-size: 1.2rem;
        }

        .features-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="hero">
    <h1 class="hero-title">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: inline-block; vertical-align: middle; color: var(--primary);">
            <path d="M12 15C15.866 15 19 11.866 19 8C19 4.13401 15.866 1 12 1C8.13401 1 5 4.13401 5 8C5 11.866 8.13401 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M8.21 13.89L7 23L12 20L17 23L15.79 13.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        OAuth2 Identity Provider
    </h1>
    <p class="hero-subtitle">
        Secure, modern authentication system built with CakePHP
    </p>
    <div class="hero-buttons">
        <?php if ($this->request->getAttribute('identity')): ?>
            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'index']) ?>" class="hero-btn hero-btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Go to Dashboard
            </a>
            <a href="<?= $this->Url->build(['controller' => 'Clients', 'action' => 'index']) ?>" class="hero-btn hero-btn-secondary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 16V8C21 6.89543 20.1046 6 19 6H5C3.89543 6 3 6.89543 3 8V16C3 17.1046 3.89543 18 5 18H19C20.1046 18 21 17.1046 21 16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M1 10H23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Manage Clients
            </a>
        <?php else: ?>
            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>" class="hero-btn hero-btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 17L15 12L10 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Sign In
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="features-grid">
    <div class="feature-card">
        <div class="feature-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 15C15.866 15 19 11.866 19 8C19 4.13401 15.866 1 12 1C8.13401 1 5 4.13401 5 8C5 11.866 8.13401 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8.21 13.89L7 23L12 20L17 23L15.79 13.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h3 class="feature-title">OAuth2 & OpenID Connect</h3>
        <p class="feature-description">
            Industry-standard authentication protocols for secure, scalable identity management.
        </p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8.5 11C10.7091 11 12.5 9.20914 12.5 7C12.5 4.79086 10.7091 3 8.5 3C6.29086 3 4.5 4.79086 4.5 7C4.5 9.20914 6.29086 11 8.5 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M20 8V14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M23 11H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h3 class="feature-title">Social Login</h3>
        <p class="feature-description">
            Seamless integration with Google and GitHub for one-click authentication.
        </p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h3 class="feature-title">Secure by Design</h3>
        <p class="feature-description">
            Built with security best practices: CSRF protection, secure password hashing, and token encryption.
        </p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h3 class="feature-title">Token Management</h3>
        <p class="feature-description">
            Full support for access tokens, refresh tokens, and token rotation for enhanced security.
        </p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M21 16V8C21 6.89543 20.1046 6 19 6H5C3.89543 6 3 6.89543 3 8V16C3 17.1046 3.89543 18 5 18H19C20.1046 18 21 17.1046 21 16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="7 10 12 13 17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h3 class="feature-title">Client Management</h3>
        <p class="feature-description">
            Register and manage OAuth2 clients with configurable scopes and redirect URIs.
        </p>
    </div>

    <div class="feature-card">
        <div class="feature-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h3 class="feature-title">Well Documented</h3>
        <p class="feature-description">
            Comprehensive documentation for developers, including architecture guides and API references.
        </p>
    </div>
</div>

<div class="tech-stack">
    <h2 class="tech-stack-title">Built With Modern Technologies</h2>
    <div class="tech-badges">
        <span class="tech-badge">PHP 8.3</span>
        <span class="tech-badge">CakePHP 5</span>
        <span class="tech-badge">PostgreSQL</span>
        <span class="tech-badge">OAuth 2.0</span>
        <span class="tech-badge">OpenID Connect</span>
        <span class="tech-badge">PHPUnit</span>
        <span class="tech-badge">TDD</span>
    </div>
</div>

<div class="cta-section">
    <h2 class="cta-title">Ready to Get Started?</h2>
    <p class="cta-description">
        <?php if ($this->request->getAttribute('identity')): ?>
            Manage your identity provider and OAuth2 clients
        <?php else: ?>
            Sign in to access the identity provider dashboard
        <?php endif; ?>
    </p>
    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => $this->request->getAttribute('identity') ? 'index' : 'login']) ?>" class="cta-btn">
        <?= $this->request->getAttribute('identity') ? 'Go to Dashboard' : 'Sign In Now' ?>
    </a>
</div>
