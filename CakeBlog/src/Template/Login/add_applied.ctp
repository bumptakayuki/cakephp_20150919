<?php $this->request->session()->write('data',$this->request->data); ?>
<div class="actions columns large-2 medium-3">
	<h3><?= __('Actions') ?></h3>
	<ul class="side-nav">
		<li><?= $this->Html->link(__('TOP'), ['controller' => 'Posts','action' => 'index']) ?> </li>
	</ul>
</div>
<div class="Posts view large-10 medium-9 columns">
	<div id="addConfirm-area">
			<p>仮登録が完了しました。</p>
			<p>送られたメールのURLに24時間以内にアクセスして、本登録を完了させてください。</p>
			<p>※まだ、登録は完了していません。</p>
	</div>
</div>