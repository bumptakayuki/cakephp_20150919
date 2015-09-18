<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('ユーザ登録'), ['controller' => 'Login','action' => 'add']) ?></li>
    </ul>
</div>
<div id='main'>
    <div class="boards form large-10 medium-9 columns">
        <?= $this->Form->create($user,[
        'url' => ['action' => 'login']]) ?>
        <fieldset>
            <legend><?= __('ログイン') ?></legend>
            <?= $this->Form->input('name',['required' => false]); ?>
            <?= $this->Form->input('password',['required' => false]); ?>
            <p>自動ログイン設定</p>
             <?= $this->Form->checkbox('autoLoginFlg', ['hiddenField' => false]); ?>
    	    </fieldset>
        <?= $this->Form->button(__('ログイン')) ?>
        <?= $this->Form->end() ?>
    </div>
</div>