<?php $userInfo = $this->request->session()->read('Auth.User'); ?>
<?php if (! empty($userInfo)) : ?>
    <div class="actions columns large-2 medium-3">
        <h3><?= __('Actions') ?></h3>
        <ul class="side-nav">
            <li><?= $this->Html->link(__('ユーザ一覧'), ['controller' => 'Users', 'action' => "index"]) ?></li>
        </ul>
    </div>
<?php endif; ?>
<div class="boards form large-10 medium-9 columns">
    <?= $this->Form->create($user,[
    'url' => ['controller' => 'Login', 'action' => 'add']]) ?>
    <fieldset>
        <legend><?= __('新規登録') ?></legend>
        <?php
            echo $this->Form->input('name',['required' => false]);
            echo $this->Form->input('password',['required' => false]);
            echo $this->Form->input('mail_address',['required' => false]);
            echo $this->Form->input('permission', ['required' => false,
                'options' => ['author' => '一般']
            ]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('登録')) ?>
    <?= $this->Form->end() ?>
</div>
