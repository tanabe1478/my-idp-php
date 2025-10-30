<div class="users form">
    <?= $this->Flash->render() ?>
    <h3>Login</h3>
    <?= $this->Form->create() ?>
    <fieldset>
        <legend><?= __('Please enter your username and password') ?></legend>
        <?= $this->Form->control('username', ['required' => true]) ?>
        <?= $this->Form->control('password', ['required' => true]) ?>
    </fieldset>
    <?= $this->Form->submit(__('Login')); ?>
    <?= $this->Form->end() ?>

    <div class="social-login" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #ddd;">
        <p style="text-align: center; margin-bottom: 1rem; color: #666;">Or sign in with</p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <?= $this->Html->link(
                'Sign in with Google',
                ['action' => 'socialLogin', 'google'],
                [
                    'class' => 'button',
                    'style' => 'background-color: #4285f4; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px; display: inline-block;'
                ]
            ) ?>
            <?= $this->Html->link(
                'Sign in with GitHub',
                ['action' => 'socialLogin', 'github'],
                [
                    'class' => 'button',
                    'style' => 'background-color: #333; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px; display: inline-block;'
                ]
            ) ?>
        </div>
    </div>
</div>
