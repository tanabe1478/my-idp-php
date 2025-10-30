<div class="users index content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #ddd;">
        <h3 style="margin: 0;"><?= __('Welcome, {0}!', h($currentUser->username)) ?></h3>
        <?= $this->Html->link(
            'Logout',
            ['action' => 'logout'],
            ['class' => 'button', 'style' => 'background-color: #dc3545; border-color: #dc3545;']
        ) ?>
    </div>

    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
        <h4 style="margin-top: 0;">Your Account Information</h4>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <div>
                <strong>Username:</strong> <?= h($currentUser->username) ?>
            </div>
            <div>
                <strong>Email:</strong> <?= h($currentUser->email) ?>
            </div>
            <div>
                <strong>Account Created:</strong> <?= $currentUser->created->format('Y-m-d H:i:s') ?>
            </div>
            <div>
                <strong>Status:</strong> <?= $currentUser->is_active ? '<span style="color: green;">Active</span>' : '<span style="color: red;">Inactive</span>' ?>
            </div>
        </div>
    </div>

    <div style="background: #fff; padding: 1.5rem; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 2rem;">
        <h4 style="margin-top: 0;">Connected Social Accounts</h4>

        <?php if (!empty($currentUser->social_accounts)): ?>
            <div style="margin-bottom: 1rem;">
                <?php foreach ($currentUser->social_accounts as $account): ?>
                    <div style="padding: 0.75rem; background: #e9ecef; border-radius: 4px; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="text-transform: capitalize;"><?= h($account->provider) ?>:</strong>
                            <?= h($account->provider_user_id) ?>
                            <?php if ($account->email): ?>
                                (<?= h($account->email) ?>)
                            <?php endif; ?>
                        </div>
                        <span style="color: green; font-size: 0.875rem;">✓ Connected</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #666; margin-bottom: 1rem;">No social accounts connected yet.</p>
        <?php endif; ?>

        <div style="padding-top: 1rem; border-top: 1px solid #ddd;">
            <p style="margin-bottom: 0.5rem; font-weight: bold;">Connect more accounts:</p>
            <div style="display: flex; gap: 1rem;">
                <?php
                $hasGoogle = false;
                $hasGithub = false;
                foreach ($currentUser->social_accounts as $account) {
                    if ($account->provider === 'google') $hasGoogle = true;
                    if ($account->provider === 'github') $hasGithub = true;
                }
                ?>

                <?php if (!$hasGoogle): ?>
                    <?= $this->Html->link(
                        'Connect Google',
                        ['action' => 'socialLogin', 'google'],
                        ['class' => 'button', 'style' => 'background-color: #4285f4; border-color: #4285f4; color: white; padding: 0.5rem 1rem;']
                    ) ?>
                <?php else: ?>
                    <button disabled style="background-color: #6c757d; border-color: #6c757d; color: white; padding: 0.5rem 1rem; cursor: not-allowed;">Google Connected</button>
                <?php endif; ?>

                <?php if (!$hasGithub): ?>
                    <?= $this->Html->link(
                        'Connect GitHub',
                        ['action' => 'socialLogin', 'github'],
                        ['class' => 'button', 'style' => 'background-color: #333; border-color: #333; color: white; padding: 0.5rem 1rem;']
                    ) ?>
                <?php else: ?>
                    <button disabled style="background-color: #6c757d; border-color: #6c757d; color: white; padding: 0.5rem 1rem; cursor: not-allowed;">GitHub Connected</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <details style="margin-top: 2rem;">
        <summary style="cursor: pointer; font-weight: bold; padding: 0.5rem; background: #f8f9fa; border-radius: 4px;">View All Users (Admin)</summary>
        <div style="margin-top: 1rem;">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th><?= $this->Paginator->sort('username') ?></th>
                            <th><?= $this->Paginator->sort('email') ?></th>
                            <th><?= $this->Paginator->sort('is_active') ?></th>
                            <th><?= $this->Paginator->sort('created') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= h($user->username) ?></td>
                            <td><?= h($user->email) ?></td>
                            <td><?= $user->is_active ? __('Yes') : __('No'); ?></td>
                            <td><?= h($user->created->format('Y-m-d H:i:s')) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="paginator">
                <ul class="pagination">
                    <?= $this->Paginator->first('<< ' . __('first')) ?>
                    <?= $this->Paginator->prev('< ' . __('previous')) ?>
                    <?= $this->Paginator->numbers() ?>
                    <?= $this->Paginator->next(__('next') . ' >') ?>
                    <?= $this->Paginator->last(__('last') . ' >>') ?>
                </ul>
                <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
            </div>
        </div>
    </details>
</div>
