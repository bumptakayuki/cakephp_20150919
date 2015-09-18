<?php  $filePath = $this->request->session()->read('fileInfo.filePath')  ?>
<h1>記事詳細</h1>
<div class="actions columns large-2 medium-3">
	<h3><?= __('Actions') ?></h3>
	<ul class="side-nav">
		<li><?= $this->Html->link(__('記事編集'), ['action' => 'edit', $post->id]) ?> </li>
		<li><?= $this->Form->postLink(__('記事削除'), ['action' => 'delete', $post->id,$post->actionType='delete'])?> </li>
		<li><?= $this->Html->link(__('記事一覧'), ['action' => 'index']) ?> </li>
		<li><?= $this->Html->link(__('新規投稿'), ['action' => 'add']) ?> </li>
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
			<?php  $tagIdList = null; ?>
			<?php foreach ($post->tags as $relation): ?>
			<?php $tagIdList .=$relation->tag_name.','?>
			<?php endforeach; ?>
			<?php echo h(rtrim($tagIdList,','))?>
			</p>
			<?php if(! empty($filePath)) : ?>
    			<h6 class="subheader"><?= __('画像') ?></h6>
     			<img src="/CakeBlog/Posts/readImage" />
 			<?php endif;?>
		</div>
		<div class="large-2 columns dates end">
			<h6 class="subheader"><?= __('作成日時') ?></h6>
			<p><?= h($post->created) ?></p>
			<h6 class="subheader"><?= __('更新日時') ?></h6>
			<p><?= h($post->modified) ?></p>
		</div>
	</div>
</div>
