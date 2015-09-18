<?php $this->request->session()->write('data',$this->request->data); ?>
<h1>確認画面</h1>
<div class="actions columns large-2 medium-3">
	<h3><?= __('Actions') ?></h3>
	<ul class="side-nav">
		<li><?= $this->Html->link(__('記事一覧'), ['action' => 'index']) ?> </li>
	</ul>
</div>
<div class="Posts view large-10 medium-9 columns">
	<h2><?= h($post->id) ?></h2>
	<div class="row">
		<div class="large-5 columns strings">
			<h6 class="subheader"><?= __('Id') ?></h6>
			<p><?= h($post->id) ?></p>
			<h6 class="subheader"><?= __('タイトル') ?></h6>
			<p><?= h($post->title) ?></p>
			<h6 class="subheader"><?= __('本文') ?></h6>
			<p><?= h($post->body) ?></p>
			<h6 class="subheader"><?= __('タグ') ?></h6>
			<p>
		<?php  $tagIdList=null; ?>
		<?php foreach ($post->tags as $relation): ?>
			<?php $tagIdList .=$relation->tag_name.','?>
		<?php endforeach; ?>
		<?php echo h(rtrim($tagIdList,','))?>
			<h6 class="subheader"><?= __('下書きステータス') ?></h6>
			<p><?= h($post->draft_status) ?></p>
		</div>
		<div class="large-2 columns dates end">
			<h6 class="subheader"><?= __('作成日時') ?></h6>
			<p><?= h($post->created) ?></p>
			<h6 class="subheader"><?= __('更新日時') ?></h6>
			<p><?= h($post->modified) ?></p>
		</div>
	</div>
	<div id="submit-button-area">
		<!-- form部分 -->
		<?= $this->Form->create($post,['url' => ['controller' => 'posts','action' => 'editFinish']])?>
        <?= $this->Form->input('cancel', ['type' => 'hidden','value' => false]) ?>
        <?= $this->Form->button(__('確定',['type' => 'button'])) ?>
        <?= $this->Form->end() ?>
    </div>
	<div id="cancel-button-area">
        <?= $this->Form->create($post,['url' => ['controller' => 'posts','action' => 'editFinish']])?>
        <?= $this->Form->input('cancel', ['type' => 'hidden','value' => true]) ?>
        <?= $this->Form->button(__('戻る',['type' => 'button'])) ?>
        <?= $this->Form->end() ?>
	</div>
</div>