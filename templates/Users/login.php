<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Login');
?>
<style>
    .login-container {
        max-width: 480px;
        margin: 3rem auto;
    }

    .login-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        padding: 3rem 2.5rem;
    }

    .login-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .login-header h2 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .login-header p {
        color: var(--gray-600);
        font-size: 0.95rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-input {
        width: 100%;
        padding: 0.85rem 1rem;
        border: 2px solid var(--gray-200);
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s;
        background: var(--gray-50);
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .submit-btn {
        width: 100%;
        padding: 0.95rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 0.5rem;
    }

    .submit-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 2rem 0;
        color: var(--gray-500);
        font-size: 0.9rem;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid var(--gray-200);
    }

    .divider span {
        padding: 0 1rem;
    }

    .social-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .social-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 0.85rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        border: 2px solid var(--gray-200);
        background: white;
        color: var(--gray-700);
    }

    .social-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-color: var(--gray-300);
    }

    .social-btn svg {
        width: 20px;
        height: 20px;
    }
</style>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h2>Welcome Back</h2>
            <p>Sign in to your account to continue</p>
        </div>

        <?= $this->Form->create(null, ['class' => 'login-form']) ?>
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <?= $this->Form->control('username', [
                    'label' => false,
                    'class' => 'form-input',
                    'placeholder' => 'Enter your username',
                    'required' => true
                ]) ?>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <?= $this->Form->control('password', [
                    'label' => false,
                    'class' => 'form-input',
                    'placeholder' => 'Enter your password',
                    'required' => true
                ]) ?>
            </div>

            <?= $this->Form->button(__('Sign In'), ['class' => 'submit-btn']) ?>
        <?= $this->Form->end() ?>

        <div class="divider">
            <span>or continue with</span>
        </div>

        <div class="social-buttons">
            <?= $this->Html->link(
                '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>' .
                '<span>Continue with Google</span>',
                ['action' => 'socialLogin', 'google'],
                ['class' => 'social-btn', 'escape' => false]
            ) ?>

            <?= $this->Html->link(
                '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>' .
                '<span>Continue with GitHub</span>',
                ['action' => 'socialLogin', 'github'],
                ['class' => 'social-btn', 'escape' => false]
            ) ?>
        </div>
    </div>
</div>
