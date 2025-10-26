<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Client $client
 * @var string $redirectUri
 * @var array $requestedScopes
 * @var string|null $state
 */
?>
<div class="oauth authorize content">
    <h3>Authorization Request</h3>

    <div class="authorization-info">
        <p>
            <strong><?= h($client->name) ?></strong> is requesting access to your account.
        </p>
    </div>

    <?php if (!empty($requestedScopes)): ?>
    <div class="scopes-requested">
        <h4>This application would like to:</h4>
        <ul>
            <?php foreach ($requestedScopes as $scope): ?>
                <li><?= h($scope) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="authorization-actions">
        <?= $this->Form->create(null, ['url' => ['action' => 'authorize']]) ?>
        <?= $this->Form->hidden('client_id', ['value' => $client->client_id]) ?>
        <?= $this->Form->hidden('redirect_uri', ['value' => $redirectUri]) ?>
        <?php if ($state): ?>
            <?= $this->Form->hidden('state', ['value' => $state]) ?>
        <?php endif; ?>

        <?php if (!empty($requestedScopes)): ?>
            <?php foreach ($requestedScopes as $index => $scope): ?>
                <?= $this->Form->hidden("scopes.{$index}", ['value' => $scope]) ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="button-group">
            <?= $this->Form->button('Authorize', [
                'type' => 'submit',
                'name' => 'approved',
                'value' => '1',
                'class' => 'button-approve'
            ]) ?>

            <?= $this->Form->button('Deny', [
                'type' => 'submit',
                'name' => 'approved',
                'value' => '0',
                'class' => 'button-deny'
            ]) ?>
        </div>

        <?= $this->Form->end() ?>
    </div>

    <div class="client-info">
        <p class="text-muted">
            You will be redirected to:<br>
            <code><?= h($redirectUri) ?></code>
        </p>
    </div>
</div>

<style>
.oauth.authorize {
    max-width: 600px;
    margin: 2rem auto;
    padding: 2rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
}

.authorization-info {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
}

.scopes-requested {
    margin-bottom: 1.5rem;
}

.scopes-requested h4 {
    margin-bottom: 0.5rem;
}

.scopes-requested ul {
    list-style: none;
    padding: 0;
}

.scopes-requested li {
    padding: 0.5rem;
    margin: 0.25rem 0;
    background: #e9ecef;
    border-radius: 4px;
}

.button-group {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.button-group button {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.button-approve {
    background: #28a745;
    color: white;
}

.button-approve:hover {
    background: #218838;
}

.button-deny {
    background: #dc3545;
    color: white;
}

.button-deny:hover {
    background: #c82333;
}

.client-info {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #ddd;
}

.text-muted {
    color: #6c757d;
    font-size: 0.875rem;
}

code {
    padding: 0.2rem 0.4rem;
    background: #f8f9fa;
    border-radius: 3px;
    font-size: 0.875rem;
    word-break: break-all;
}
</style>
