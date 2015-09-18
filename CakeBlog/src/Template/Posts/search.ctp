<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('記事一覧'), ['action' => 'index']) ?></li>
    </ul>
</div>
<div class="boards form large-10 medium-9 columns">
    <?= $this->Form->create($post,[
    'url' => ['controller' => 'posts', 'action' => 'search'],'type' => 'get']) ?>
    <fieldset>
        <legend><?= __('検索') ?></legend>
        <?php
            echo $this->Form->input('keyword',['required' => false]);
            echo $this->Form->input('tag_id', [
              'type' => 'select',
              'multiple'=> 'checkbox',
              'options' => $tag
            ]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('検索')) ?>
    <?= $this->Form->end() ?>
    <table>
    <tr>
        <th>Id</th>
        <th>Title</th>
        <th>Tag</th>
        <th>AuthorName</th>
        <th>Created</th>
		<th>published</th>
        <th>Action</th>
    </tr>
<!-- $posts配列をループして、投稿記事の情報を表示 -->
<?php foreach ($posts as $post): ?>
    <tr>
    <td><?php echo h($post->id); ?></td>
    <td>
        <?php echo $this->Html->link($post->title,
        ['controller' => 'posts', 'action' => "view", $post->id]);?>
    </td>
    <td>
    <div id="tag-list">
        <?php
        foreach ($post->tags as $tagId => $tagName) :
        echo '<li class="tag_name"><span>'
            .$this->Html->link(__($tagName), ['action' => 'viewTagBlogList','?' => ['tag_id' => $tagId]])
            .'</span></li>';
        endforeach;
        ?>
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
                        <?php echo $this->Html->link(__('<<TOP'), ['action' => 'search','?' => ['pagingId' => 1]]);?>
                    </li>
                    <li class="around">
                        <?php echo $this->Html->link(__('<前へ'), ['action' => 'search','?' => ['pagingId' => $pagingInfoBean->getCurrentPageId()-1]]);?>
                    </li>
                <?php endif; ?>
                <!-- ページ番号リンク制御 -->
                <?php foreach ($pagingInfoBean->getPagingButtonList() as $pagingButton) : ?>
                    <li class= "<?php echo $pagingButton['current']?>">
                        <?php if($pagingButton['pagingButtonDispFlg'] == true) : ?>
                            <?php echo $this->Html->link(__($pagingButton['pageId']), ['action' => 'search','?' => ['pagingId' => $pagingButton['pageId']]]);?>
                        <?php elseif($pagingButton['pagingButtonDispFlg'] == false) : ?>
                            <span>...</span>
                        <?php endif; ?>
                    </li>
                <?php endForeach;?>
                <!-- 「最大」「次へ」ボタン制御 -->
                <?php if ($pagingInfoBean->getNextButtonFlg()) :?>
                    <li class="around">
                        <?php echo $this->Html->link(__('次へ>'), ['action' => 'search','?' => ['pagingId' => $pagingInfoBean->getCurrentPageId()+1]]);?>
                    </li>
                    <li class="around">
                        <?php echo $this->Html->link(__('最大>>'), ['action' => 'search','?' => ['pagingId' => $pagingInfoBean->getEndPageId()]]);?>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
