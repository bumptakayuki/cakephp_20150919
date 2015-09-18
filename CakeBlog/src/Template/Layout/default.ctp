<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = 'ブログ投稿アプリ';
$userInfo = $this->request->session()->read('Auth.User');
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport"
    content="width=device-width, initial-scale=1.0">
<title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css('base.css') ?>
    <?= $this->Html->css('cake.css') ?>
    <?php if (! empty($userInfo['permission'])) : ?>
        <?php if($userInfo['permission'] == 'admin'): ?>
            <?= $this->Html->css('admin.css') ?>
        <?php endif;?>
    <?php endif;?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script>
    $(function(){
        $('#menu li').hover(function(){
            $("ul:not(:animated)", this).slideDown();
        }, function(){
            $("ul.child",this).slideUp();
        });
    });
	</script>	
</head>
<body>
    <header>
        <div class="header-title">
            <span><?= $this->fetch('title') ?></span>
        </div>
        <div class="header-login">
            <div class='login-area'>
                <ul>
                <?php  if (! empty($userInfo)) : ?>
                    <li><?= $this->Html->link(__('ログアウト　|'),  ['controller' => 'Login', 'action' => "logout"]) ?> </li>
                 <?php endif;?>
                 <?php  if ( empty($userInfo)) : ?>
                    <li><?= $this->Html->link(__('ログイン　|'),  ['controller' => 'Login', 'action' => "login"]) ?> </li>
                <?php endif;?>
                </ul>
                <div class='login-user'>
                    <?php  if (! empty($userInfo['name'])) : ?>
                    	<?php echo '「' . $userInfo['name'] . '」がログイン中'; ?>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </header>
    <div id="container">
        <div id="content">
            <?= $this->Flash->render() ?>
            <div class="row">
                <?= $this->fetch('content') ?>
            </div>
        </div>
        <footer> </footer>
    </div>
</body>
</html>
