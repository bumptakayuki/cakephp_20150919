<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Html->link(__('記事一覧'), ['action' => 'index']) ?></li>
    </ul>
</div>
<div class="boards form large-10 medium-9 columns">
    <?= $this->Form->create($post) ?>
    <fieldset>
        <legend><?= __('記事編集') ?></legend>
        <?php
            echo $this->Form->input('id', ['type' => 'hidden']);
            echo $this->Form->input('title',['required' => false]);
            echo $this->Form->input('body',['required' => false]);
            echo $this->Form->input('draft_flag', ['required' => false,
                'options' => [ 0 => '下書き', 1 => '公開中']
            ]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('更新')) ?>
    <?= $this->Form->end() ?>
</div>
