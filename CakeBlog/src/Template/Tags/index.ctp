<div id='main'>
    <div id="main-menu">
        <ul id="menu">
				<li><?= $this->Html->link(__('TOP　'), ['controller' => 'Posts','action' => 'index']) ?> </li>
                <li>記事関連
                <ul class="child">
                    <li class="current"><?= $this->Html->link(__('記事一覧　'), ['action' => 'index']) ?> </li>
        			<li><?= $this->Html->link(__('下書き記事一覧　'), ['controller' => 'Posts','action' => 'indexDraft']) ?> </li>
        			<li><?= $this->Html->link(__('予約記事一覧　'), ['controller' => 'Posts','action' => 'indexReservations']) ?> </li>
        			<li><?= $this->Html->link(__('記事検索　'),  ['controller' => 'Posts', 'action' => "search"]) ?> </li>
        			<li><?= $this->Html->link(__('新規投稿　'),  ['controller' => 'Posts', 'action' => "add"]) ?> </li>
                </ul>
            </li>
            <li>タグ関連
                <ul class="child">
            		<li><?= $this->Html->link(__('タグ一覧　'), ['controller' => 'tags', 'action' => "index"]) ?> </li>
        			<li><?= $this->Html->link(__('タグ新規作成　'),  ['controller' => 'tags', 'action' => "add"]) ?> </li>
                </ul>
            </li>
            <li>ユーザ関連
                <ul class="child">
        			<li><?= $this->Html->link(__('ユーザ管理画面　'),  ['controller' => 'Users', 'action' => "index"]) ?> </li>
                	<li><?= $this->Html->link(__('ユーザ登録　'),  ['controller' => 'Login', 'action' => "add"]) ?> </li>
                </ul>
            </li>
        </ul>
    </div>
<div id="headline">タグ一覧</div>
<div id="tag-list-all">
<table>
	<tr>
		<th>Id</th>
		<th>Title</th>
		<th>Action</th>
	</tr>
<?php foreach ($tags as $tag): ?>
	<tr>
	<td><?php echo h($tag->tag_id); ?></td>
	<td>
		<?php echo $tag->tag_name; ?>
	</td>
	<td>
		<?= $this->Html->link(__('編集'), ['action' => 'edit', $tag->tag_id]) ?>
		<?= $this->Form->postLink(__('削除'), ['action' => 'delete', $tag->tag_id])?>
	</td>
	</tr>
<?php endforeach; ?>
<?php unset($tag); ?>
</table>

<!-- paging部分 -->
<div id='paging-area'>
	<div id='paging-area-inner'>
		<ul>
    	<?php if($pagingInfoBean->getPagingDispButtonFlg()) : ?>
    		<!-- 「TOP」「前へ」ボタン制御 -->
            <?php if($pagingInfoBean->getPrevButtonFlg()) : ?>
                <li class="around">
                    <?php echo $this->Html->link(__('<<TOP'), ['action' => 'index','?' => ['pagingId' => 1]]);?>
                </li>
                <li class="around">
                    <?php echo $this->Html->link(__('<前へ'), ['action' => 'index','?' => ['pagingId' => $pagingInfoBean->getCurrentPageId()-1]]);?>
                </li>
            <?php endif; ?>
            <!-- ページ番号リンク制御 -->
            <?php foreach ($pagingInfoBean->getPagingButtonList() as $pagingButton) : ?>
                <li class= "<?php echo $pagingButton['current']?>">
                    <?php if($pagingButton['pagingButtonDispFlg'] == true) : ?>
                        <?php echo $this->Html->link(__($pagingButton['pageId']), ['action' => 'index','?' => ['pagingId' => $pagingButton['pageId']]]);?>
                    <?php elseif($pagingButton['pagingButtonDispFlg'] == false) : ?>
                    	<span>...</span>
                    <?php endif; ?>
                </li>
            <?php endForeach;?>
            <!-- 「最大」「次へ」ボタン制御 -->
            <?php if ($pagingInfoBean->getNextButtonFlg()) :?>
                <li class="around">
                    <?php echo $this->Html->link(__('次へ>'), ['action' => 'index','?' => ['pagingId' => $pagingInfoBean->getCurrentPageId()+1]]);?>
                </li>
                <li class="around">
                    <?php echo $this->Html->link(__('最大>>'), ['action' => 'index','?' => ['pagingId' => $pagingInfoBean->getEndPageId()]]);?>
                </li>
            <?php endif; ?>
        <?php endif; ?>
		</ul>
	</div>
</div>
</div>