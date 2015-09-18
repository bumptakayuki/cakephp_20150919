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
<div id="headline">
	予約記事一覧
</div>
<div id="tag-list-all">
	<ul>
    <?php foreach ($tags as $tagId => $tagName) : ?>
            <li class="tag_name"><span>
                <?php echo $this->Html->link(__($tagName), ['action' => 'viewTagBlogList','?' => ['tag_id' => $tagId]]);?>
            </span></li>
    <?php endforeach; ?>
    </ul>
</div>
<table>
	<tr>
		<th>Id</th>
		<th>Title</th>
		<th>Tag</th>
		<th>AuthorName</th>
		<th>Created</th>
		<th>published</th>
		<th>draft_status</th>
		<th>Action</th>
	</tr>
	<!-- $posts配列をループして、投稿記事の情報を表示 -->
<?php foreach ($posts as $post) :?>
	<tr>
		<td>
			<?php echo h($post->id); ?>
		</td>
		<td>
			<?php echo $this->Html->link($post->title, ['controller' => 'posts', 'action' => "view",$post->id]); ?>
		</td>
		<td>
            <div id="tag-list">
            <?php foreach ($post->tags as $tag) : ?>
                    <li class="tag_name">
                        <span>
    	                    <?php echo $this->Html->link(__($tag->tag_name), ['action' => 'viewTagBlogList','?' => ['tag_id' => $tag->tag_id]]);?>
                        </span>
                    </li>
            <?php endforeach; ?>
            </div>
		</td>
    	<td>
    		<?php echo h($post->author_name); ?>
    	</td>
    	<td>
    		<?php echo h($post->created->format('Y-m-d H:i:s')); ?>
    	</td>
		<td>
			<?php echo h($post->published_date->format('Y-m-d H:i:s')); ?>
		</td>
		<td>
			<?php echo h($post->draft->draft_status); ?>
		</td>
		<td>
    		<?= $this->Html->link(__('詳細'), ['action' => 'view', $post->id]) ?>
    		<?= $this->Html->link(__('編集'), ['action' => 'edit', $post->id]) ?>
    		<?= $this->Form->postLink(__('削除'), ['action' => 'delete', $post->id])?>
		</td>
	</tr>
<?php endforeach; ?>
<?php unset($post); ?>
</table>
<!-- paging部分 -->
<div id='paging-area'>
	<div id='paging-area-inner'>
		<ul>
        <?php if($pagingInfoBean->getPagingDispButtonFlg()) : ?>
    		<!-- 「TOP」「前へ」ボタン制御 -->
            <?php if($pagingInfoBean->getPrevButtonFlg()) : ?>
                <li class="around">
                    <?php echo $this->Html->link(__('<<TOP'), ['action' => 'indexDraft','?' => ['pagingId' => 1]]);?>
                </li>
                <li class="around">
                    <?php echo $this->Html->link(__('<前へ'), ['action' => 'indexDraft','?' => ['pagingId' => $pagingInfoBean->getCurrentPageId()-1]]);?>
                </li>
            <?php endif; ?>
            <!-- ページ番号リンク制御 -->
            <?php foreach ($pagingInfoBean->getPagingButtonList() as $pagingButton) : ?>
                <li class= "<?php echo $pagingButton['current']?>">
                    <?php if($pagingButton['pagingButtonDispFlg'] == true) : ?>
                        <?php echo $this->Html->link(__($pagingButton['pageId']), ['action' => 'indexDraft','?' => ['pagingId' => $pagingButton['pageId']]]);?>
                    <?php elseif($pagingButton['pagingButtonDispFlg'] == false) : ?>
                    	<span>...</span>
                    <?php endif; ?>
                </li>
            <?php endForeach;?>	
            <!-- 「最大」「次へ」ボタン制御 -->
            <?php if ($pagingInfoBean->getNextButtonFlg()) :?>
                <li class="around">
                    <?php echo $this->Html->link(__('次へ>'), ['action' => 'indexDraft','?' => ['pagingId' => $pagingInfoBean->getCurrentPageId()+1]]);?>
                </li>
                <li class="around">
                    <?php echo $this->Html->link(__('最大>>'), ['action' => 'indexDraft','?' => ['pagingId' => $pagingInfoBean->getEndPageId()]]);?>
                </li>
            <?php endif; ?>
        <?php endif; ?>
		</ul>
	</div>
</div>
</div>