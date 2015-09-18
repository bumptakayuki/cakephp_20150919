<?php $session = $this->request->session();
$tempFilePath = $session->read('tempFilePath')?>
<div class="actions columns large-2 medium-3">
	<h3><?= __('Actions') ?></h3>
	<ul class="side-nav">
		<li><?= $this->Html->link(__('記事一覧'), ['action' => 'index']) ?></li>
	</ul>
</div>
<div class="boards form large-10 medium-9 columns">
    <?= $this->Form->create($post,['type'=>'file', 'enctype' => 'multipart/form-data',
    'url' => ['controller' => 'posts', 'action' => 'add']]) ?>
    <fieldset>
		<legend><?= __('新規投稿') ?></legend>
        <?= $this->Form->input('title',['default' => $session->read('data')['title'],'required' => false]); ?>
        <?= $this->Form->input('body',['default' => $session->read('data')['body'],'required' => false]); ?>
        <?php $selectTags = $session->read('data')['tag_id'] ?>
        <?php if(empty($selectTags)) : ?>
        <?php $selectTags=[]; ?>
        <?php endif; ?>
        <?= $this->Form->input('tag_id', [
          'default' => $selectTags,
          'type' => 'select',
          'multiple'=> 'checkbox',
          'options' => $tag
        ]); ?>
        <?= $this->Form->input('published_date',['default' => $session->read('data')['body'],'required' => false]); ?>
        <?= $this->Form->file('imageFile',['id' => 'files']); ?>
    	<output id="list"></output>
    <?php if($session->read('tempFileExistsFlg')) : ?>
    	<hr>
    	<p>＜選択していた画像＞</p>
    	<p>同じファイルを上げる場合は、チェックをつけてください。</p>
    	<?= $this->Form->checkbox('sameFileUploadFlg', ['default' => false]); ?>
		<img width="300" height="300" src="/CakeBlog/Posts/readTempImage" />
    <?php endif; ?>
    </fieldset>
    <?= $this->Form->button(__('投稿')) ?>
    <?= $this->Form->end() ?>
</div>

<script>
  function handleFileSelect(evt) {

    var files = evt.target.files;

    for (var i = 0, f; f = files[i]; i++) {
      if (!f.type.match('image.*')) {
        continue;
      }
      var reader = new FileReader();
      reader.onload = (function(theFile) {
        return function(e) {
        var span = document.createElement('span');
          span.innerHTML = ['<img class="thumb" src="', e.target.result,
                            '" title="', escape(theFile.name), '"/>'].join('');
          document.getElementById('list').insertBefore(span, null);
        };
      })(f);

      reader.readAsDataURL(f);
    }
  }

  document.getElementById('files').addEventListener('change', handleFileSelect, false);
</script>
