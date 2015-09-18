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
		<?php foreach ($post->tags as $tagName): ?>
			<?php $tagIdList .=$tagName.','?>
		<?php endforeach; ?>
		<?php echo h(rtrim($tagIdList,','))?>
			</p>
			<?php if($ImageExistsFlg) : ?>
    			<h6 class="subheader"><?= __('画像') ?></h6>
			<p>
				<img width="300" height="300" src="/CakeBlog/Posts/readTempImage" />
			</p>
    		<?php endif; ?>
    	<h6 class="subheader"><?= __('記事公開日') ?></h6>
			<p><?= h($post->published_date->format('Y-m-d H:i:s')) ?></p>
		</div>
	</div>
	<div id="submit-button-area">
		<?= $this->Form->create($post,['url' => ['controller' => 'posts','action' => 'addFinish']])?>
        <?= $this->Form->input('cancel', ['type' => 'hidden','value' => false]) ?>
        <?= $this->Form->button(__('確定')) ?>
        <?= $this->Form->end() ?>
    </div>
	<div id="cancel-button-area">
        <?= $this->Form->create($post,['url' => ['controller' => 'posts','action' => 'addFinish']])?>
        <?= $this->Form->input('cancel', ['type' => 'hidden','value' => true]) ?>
        <?= $this->Form->button(__('戻る',['type' => 'button'])) ?>
        <?= $this->Form->end() ?>
	</div>
	<div id="draft-button-area">
		<?= $this->Form->create($post,['url' => ['controller' => 'posts','action' => 'addDraftFinish']])?>
        <?= $this->Form->input('cancel', ['type' => 'hidden','value' => false]) ?>
        <?= $this->Form->button(__('下書き保存'),['id' => 'draft']) ?>
        <?= $this->Form->end() ?>
    </div>
</div>
