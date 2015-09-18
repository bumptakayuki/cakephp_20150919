<?php $this->request->session()->write('data',$this->request->data); ?>
<h1>確認画面</h1>
<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('タグ一覧'), ['action' => 'index']) ?> </li>
    </ul>
</div>
<div class="Posts view large-10 medium-9 columns">
    <h2><?= h($tag->tag_id) ?></h2>
    <div class="row">
        <div class="large-5 columns strings">
            <h6 class="subheader"><?= __('Id') ?></h6>
            <p><?= h($tag->tag_id) ?></p>
            <h6 class="subheader"><?= __('タグ名') ?></h6>
            <p><?= h($tag->tag_name) ?></p>
        </div>
    </div>
    <!-- form部分 -->
    <div id="submit-button-area">
        <?= $this->Form->create($tag,['url' => ['controller' => 'tags','action' => 'editFinish']])?>
        <?= $this->Form->button(__('確定')) ?>
        <?= $this->Form->end() ?>
    </div>
    <div id="cancel-button-area">
        <?= $this->Form->create($tag,['url' => ['controller' => 'tags','action' => 'editFinish']])?>
        <?= $this->Form->input('cancel', ['type' => 'hidden','value' => true]) ?>
        <?= $this->Form->button(__('戻る',['type' => 'button'])) ?>
        <?= $this->Form->end() ?>
	</div>
</div>