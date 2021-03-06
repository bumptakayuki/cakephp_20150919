<?php $this->request->session()->write('data',$this->request->data); ?>
<h1>確認画面</h1>
<div class="actions columns large-2 medium-3">
	<h3><?= __('Actions') ?></h3>
	<ul class="side-nav">
		<li><?= $this->Html->link(__('ユーザ編集'), ['action' => 'edit', $user->id]) ?> </li>
		<li><?= $this->Form->postLink(__('ユーザ削除'), ['action' => 'delete', $user->id])?> </li>
		<li><?= $this->Html->link(__('ユーザ一覧'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('ユーザ登録'), ['action' => 'add']) ?> </li>
	</ul>
</div>
<div class="Posts view large-10 medium-9 columns">
	<h2><?= h($user->id) ?></h2>
	<div class="row">
		<div class="large-5 columns strings">
			<h6 class="subheader"><?= __('Id') ?></h6>
			<p><?= h($user->id) ?></p>
			<h6 class="subheader"><?= __('ユーザ名') ?></h6>
			<p><?= h($user->name) ?></p>
			<h6 class="subheader"><?= __('権限') ?></h6>
			<p><?= h($user->permission) ?></p>
			<h6 class="subheader"><?= __('メールアドレス') ?></h6>
			<p><?= h($user->mail_address) ?></p>			
		</div>
	</div>
	<div id="submit-button-area">
    	<?= $this->Form->create($user,['url' => ['controller' => 'Login','action' => 'addConfirm']])?>
        <?= $this->Form->button(__('確定')) ?>
        <?= $this->Form->end() ?>
	</div>
	<div id="cancel-button-area">
        <?= $this->Form->create($user,['url' => ['controller' => 'users','action' => 'addConfirm']])?>
        <?= $this->Form->input('cancel', ['type' => 'hidden','value' => true]) ?>
        <?= $this->Form->button(__('戻る',['type' => 'button'])) ?>
        <?= $this->Form->end() ?>
	</div>
</div>