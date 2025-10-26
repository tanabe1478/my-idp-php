<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Client $client
 * @var \Cake\Collection\CollectionInterface|string[] $scopes
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Clients'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="clients form content">
            <?= $this->Form->create($client) ?>
            <fieldset>
                <legend><?= __('Register New Client') ?></legend>
                <?php
                    echo $this->Form->control('name', [
                        'label' => 'Client Name',
                        'placeholder' => 'My Application',
                    ]);
                    echo $this->Form->control('redirect_uris', [
                        'type' => 'textarea',
                        'label' => 'Redirect URIs (one per line)',
                        'placeholder' => "https://example.com/callback\nhttps://example.com/callback2",
                        'value' => is_array($client->redirect_uris) ? implode("\n", $client->redirect_uris) : '',
                    ]);
                    echo $this->Form->control('grant_types', [
                        'type' => 'select',
                        'multiple' => 'checkbox',
                        'options' => [
                            'authorization_code' => 'Authorization Code',
                            'refresh_token' => 'Refresh Token',
                            'client_credentials' => 'Client Credentials',
                        ],
                        'label' => 'Grant Types',
                    ]);
                    echo $this->Form->control('is_confidential', [
                        'label' => 'Is Confidential Client',
                        'checked' => true,
                    ]);
                    echo $this->Form->control('is_active', [
                        'label' => 'Is Active',
                        'checked' => true,
                    ]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Register Client')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
// Convert textarea to array on form submit
document.querySelector('form').addEventListener('submit', function(e) {
    const redirectUrisTextarea = document.querySelector('textarea[name="redirect_uris"]');
    if (redirectUrisTextarea) {
        const lines = redirectUrisTextarea.value.split('\n')
            .map(line => line.trim())
            .filter(line => line.length > 0);

        // Remove textarea and add hidden inputs
        redirectUrisTextarea.remove();
        lines.forEach((line, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `redirect_uris[${index}]`;
            input.value = line;
            this.appendChild(input);
        });
    }
});
</script>
