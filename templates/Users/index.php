<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $currentUser
 * @var \App\Model\Entity\User[] $users
 */
$this->assign('title', 'Dashboard');

// Check which social accounts are connected
$hasGoogle = false;
$hasGithub = false;
foreach ($currentUser->social_accounts ?? [] as $account) {
    if ($account->provider === 'google') $hasGoogle = true;
    if ($account->provider === 'github') $hasGithub = true;
}
?>

<style>
    .dashboard-header {
        margin-bottom: 2rem;
    }

    .dashboard-header h1 {
        font-size: 2.25rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .dashboard-header p {
        color: var(--gray-600);
        font-size: 1.1rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 1.75rem;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .stat-card-secondary {
        background: linear-gradient(135deg, var(--secondary) 0%, #0891b2 100%);
    }

    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .info-item {
        padding: 1rem;
        background: var(--gray-50);
        border-radius: 8px;
        border-left: 3px solid var(--primary);
    }

    .info-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-size: 1.1rem;
        color: var(--gray-900);
        font-weight: 600;
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .social-account-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: var(--gray-50);
        border-radius: 10px;
        border: 2px solid var(--gray-200);
        margin-bottom: 1rem;
    }

    .social-icon {
        width: 48px;
        height: 48px;
        background: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .social-info {
        flex: 1;
    }

    .social-provider {
        font-weight: 700;
        color: var(--gray-900);
        font-size: 1.1rem;
        margin-bottom: 0.25rem;
    }

    .social-email {
        color: var(--gray-600);
        font-size: 0.9rem;
    }

    .connect-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .connect-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.85rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        border: 2px solid var(--gray-200);
        background: white;
        color: var(--gray-700);
    }

    .connect-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-color: var(--primary);
    }

    .connect-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: var(--gray-100);
    }

    .connect-btn svg {
        width: 20px;
        height: 20px;
    }

    .section-divider {
        border: none;
        border-top: 2px solid var(--gray-100);
        margin: 2rem 0;
    }

    .admin-section {
        margin-top: 2rem;
    }

    .admin-section summary {
        cursor: pointer;
        font-weight: 600;
        padding: 1rem 1.5rem;
        background: var(--gray-100);
        border-radius: 8px;
        transition: all 0.2s;
        color: var(--gray-700);
    }

    .admin-section summary:hover {
        background: var(--gray-200);
    }

    .admin-content {
        margin-top: 1.5rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }

    table thead {
        background: var(--gray-100);
    }

    table th {
        padding: 0.95rem 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--gray-700);
        font-size: 0.9rem;
    }

    table td {
        padding: 0.95rem 1rem;
        border-top: 1px solid var(--gray-200);
    }

    table tbody tr:hover {
        background: var(--gray-50);
    }

    .pagination {
        display: flex;
        gap: 0.5rem;
        list-style: none;
        padding: 1rem 0;
        justify-content: center;
    }

    .pagination a {
        padding: 0.5rem 0.95rem;
        border-radius: 6px;
        border: 1px solid var(--gray-300);
        text-decoration: none;
        color: var(--gray-700);
        transition: all 0.2s;
    }

    .pagination a:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .pagination .active a {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
</style>

<div class="dashboard-header">
    <h1>Welcome back, <?= h($currentUser->username) ?>!</h1>
    <p>Manage your account and connected services</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Account Status</div>
        <div class="stat-value">
            <?= $currentUser->is_active ? 'Active' : 'Inactive' ?>
        </div>
    </div>

    <div class="stat-card stat-card-secondary">
        <div class="stat-label">Connected Accounts</div>
        <div class="stat-value">
            <?= count($currentUser->social_accounts ?? []) ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Account Information</h3>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Username</div>
            <div class="info-value"><?= h($currentUser->username) ?></div>
        </div>

        <div class="info-item">
            <div class="info-label">Email</div>
            <div class="info-value"><?= h($currentUser->email) ?></div>
        </div>

        <div class="info-item">
            <div class="info-label">Status</div>
            <div class="info-value">
                <?php if ($currentUser->is_active): ?>
                    <span class="badge badge-success">Active</span>
                <?php else: ?>
                    <span class="badge badge-danger">Inactive</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="info-item">
            <div class="info-label">Member Since</div>
            <div class="info-value"><?= $currentUser->created->format('M d, Y') ?></div>
        </div>
    </div>
</div>

<hr class="section-divider">

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Connected Social Accounts</h3>
    </div>

    <?php if (!empty($currentUser->social_accounts)): ?>
        <?php foreach ($currentUser->social_accounts as $account): ?>
            <div class="social-account-card">
                <div class="social-icon">
                    <?php if ($account->provider === 'google'): ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                    <?php else: ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#181717" d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="social-info">
                    <div class="social-provider"><?= ucfirst(h($account->provider)) ?></div>
                    <div class="social-email"><?= h($account->email) ?></div>
                </div>
                <span class="badge badge-success">Connected</span>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="color: var(--gray-600); text-align: center; padding: 2rem 0;">
            No social accounts connected yet. Connect your accounts below to enable social login.
        </p>
    <?php endif; ?>

    <div style="padding-top: 1.5rem; border-top: 2px solid var(--gray-100); margin-top: 1rem;">
        <p style="font-weight: 600; color: var(--gray-700); margin-bottom: 1rem;">Connect more accounts:</p>
        <div class="connect-buttons">
            <?php if (!$hasGoogle): ?>
                <?= $this->Html->link(
                    '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>' .
                    '<span>Connect Google</span>',
                    ['action' => 'socialLogin', 'google'],
                    ['class' => 'connect-btn', 'escape' => false]
                ) ?>
            <?php else: ?>
                <button class="connect-btn" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Google Connected</span>
                </button>
            <?php endif; ?>

            <?php if (!$hasGithub): ?>
                <?= $this->Html->link(
                    '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>' .
                    '<span>Connect GitHub</span>',
                    ['action' => 'socialLogin', 'github'],
                    ['class' => 'connect-btn', 'escape' => false]
                ) ?>
            <?php else: ?>
                <button class="connect-btn" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>GitHub Connected</span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<details class="admin-section">
    <summary>View All Users (Admin)</summary>
    <div class="admin-content">
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th><?= $this->Paginator->sort('username') ?></th>
                        <th><?= $this->Paginator->sort('email') ?></th>
                        <th><?= $this->Paginator->sort('is_active', 'Status') ?></th>
                        <th><?= $this->Paginator->sort('created', 'Created') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?= h($user->username) ?></strong></td>
                        <td><?= h($user->email) ?></td>
                        <td>
                            <?php if ($user->is_active): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $user->created->format('M d, Y') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination">
                <?= $this->Paginator->first('<< ' . __('first')) ?>
                <?= $this->Paginator->prev('< ' . __('previous')) ?>
                <?= $this->Paginator->numbers() ?>
                <?= $this->Paginator->next(__('next') . ' >') ?>
                <?= $this->Paginator->last(__('last') . ' >>') ?>
            </div>
            <p style="text-align: center; color: var(--gray-600); font-size: 0.9rem;">
                <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
            </p>
        </div>
    </div>
</details>
