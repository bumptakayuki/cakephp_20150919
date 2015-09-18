<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('ユーザ一覧'), ['action' => 'index']) ?></li>
    </ul>
</div>
<div class="boards form large-10 medium-9 columns">
    <?= $this->Form->create($user,[
    'url' => ['controller' => 'users', 'action' => 'edit']]) ?>
    <fieldset>
        <legend><?= __('タグ編集') ?></legend>
        <?php
            echo $this->Form->input('id', array('type' => 'hidden'));
            echo $this->Form->input('name',['required' => false]);
            echo $this->Form->input('password',['required' => false]);
            echo $this->Form->input('mail_address',['required' => false]);
            echo $this->Form->input('permission', ['required' => false,
                'options' => ['admin' => '管理者', 'author' => '一般']
            ]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('更新')) ?>
    <?= $this->Form->end() ?>
</div>
