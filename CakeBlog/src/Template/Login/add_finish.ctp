<?php $this->request->session()->write('data',$this->request->data); ?>
<h1>確認画面</h1>
<div class="actions columns large-2 medium-3">
	<h3><?= __('Actions') ?></h3>
	<ul class="side-nav">
		<li><?= $this->Html->link(__('TOP　'), ['controller' => 'Posts','action' => 'index']) ?> </li>
	</ul>
</div>
<div class="Posts view large-10 medium-9 columns">
	<div class="row">
			<p>本登録が完了しました。</p>
	</div>
</div>