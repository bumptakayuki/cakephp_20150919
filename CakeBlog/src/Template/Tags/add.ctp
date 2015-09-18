<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('タグ一覧'), ['action' => 'index']) ?></li>
    </ul>
</div>
<div class="boards form large-10 medium-9 columns">
    <?= $this->Form->create($tag,array(
    'url' => array('controller' => 'tags', 'action' => 'add'))) ?>
    <fieldset>
        <legend><?= __('新規投稿') ?></legend>
        <?php
            echo $this->Form->input('tag_name',['required' => false]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('投稿')) ?>
    <?= $this->Form->end() ?>
</div>
