<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Client $client
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Clients'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('Register New Client'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="clients view content">
            <h3><?= h($client->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Client ID') ?></th>
                    <td><?= h($client->client_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($client->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Confidential') ?></th>
                    <td><?= $client->is_confidential ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Active') ?></th>
                    <td><?= $client->is_active ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($client->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($client->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Redirect URIs') ?></strong>
                <ul>
                    <?php foreach ($client->redirect_uris as $uri): ?>
                        <li><?= h($uri) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="text">
                <strong><?= __('Grant Types') ?></strong>
                <ul>
                    <?php foreach ($client->grant_types as $type): ?>
                        <li><?= h($type) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="related">
                <h4><?= __('Related Scopes') ?></h4>
                <?php if (!empty($client->scopes)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Description') ?></th>
                        </tr>
                        <?php foreach ($client->scopes as $scope) : ?>
                        <tr>
                            <td><?= h($scope->name) ?></td>
                            <td><?= h($scope->description) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
