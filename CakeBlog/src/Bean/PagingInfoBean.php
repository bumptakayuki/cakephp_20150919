<?php
namespace App\Bean;

/**
 * ページング処理に必用な情報をまとめたBeanクラス
 *
 * @access public
 * @author Takayuki_suzuki
 * @package Bean
 */
class PagingInfoBean
{
    /* 現在ページ番号 */
    private $currentPageId = 0;

    /* 開始ページ番号 */
    private $startPageId = 0;

    /* 終了ページ番号 */
    private $endPageId = 0;

    /* ページ番号表示最大件数 */
    private $maxPageDisplayLimit = 0;

    /* ページングボタン表示制御フラグ */
    private $pagingDispButtonFlg = false;

    /* 「前へ」ボタン表示制御フラグ */
    private $prevButtonFlg = false;

    /* 「次へ」ボタン表示制御フラグ */
    private $nextButtonFlg = false;

    /* ページングボタン表示制御フラグ */
    private $pagingButtonList = [];

    /**
     * 現在ページ番号を設定する
     *
     * @access public
     * @param $currentPageId 現在ページ番号
     */
    public function setCurrentPageId($currentPageId)
    {
        $this->currentPageId = $currentPageId;
    }
    
    /**
     * 現在ページ番号を取得する
     *
     * @access public
     * @return $currentPageId 現在ページ番号
     */
    public function getCurrentPageId()
    {
        return $this->currentPageId;
    }

    /**
     * 開始ページ番号を設定する
     *
     * @access public
     * @param $startPageId 開始ページ番号
     */
    public function setStartPageId($startPageId)
    {
        $this->startPageId = $startPageId;
    }

    /**
     * 開始ページ番号を取得する
     *
     * @access public
     * @return $startPageId 開始ページ番号
     */
    public function getStartPageId()
    {
        return $this->startPageId;
    }

    /**
     * 終了ページ番号を設定する
     *
     * @access public
     * @param $endPageId 終了ページ番号
     */
    public function setEndPageId($endPageId)
    {
        $this->endPageId = $endPageId;
    }

    /**
     * 終了ページ番号を取得する
     *
     * @access public
     * @return $endPageId 終了ページ番号
     */
    public function getEndPageId()
    {
        return $this->endPageId;
    }

    /**
     * ページ番号表示最大件数を設定する
     * 
     *
     * @access public
     * @param $maxPageDisplayLimit ページ番号表示最大件数
     */
    public function setMaxPageDisplayLimit($maxPageDisplayLimit)
    {
        $this->maxPageDisplayLimit = $maxPageDisplayLimit;
    }

    /**
     * ページ番号表示最大件数を取得する
     *
     * @access public
     * @return maxPageDisplayLimit ページ番号表示最大件数
     */
    public function getMaxPageDisplayLimit()
    {
        return $this->maxPageDisplayLimit;
    }

    /**
     * ページングボタン表示制御フラグを設定する
     *
     * @access public
     * @param $pagingDispButtonFlg ページングボタン表示制御フラ
     */
    public function setPagingDispButtonFlg($pagingDispButtonFlg)
    {
        $this->pagingDispButtonFlg = $pagingDispButtonFlg;
    }

    /**
     * ページングボタン表示制御フラグを取得する
     *
     * @access public
     * @return pagingDispButtonFlg ページングボタン表示制御フラグ
     */
    public function getPagingDispButtonFlg()
    {
        return $this->pagingDispButtonFlg;
    }

    /**
     * 「前へ」ボタン表示制御フラグを設定する
     *
     * @access public
     * @param $prevButtonFlg 「前へ」ボタン表示制御フラグ
     */
    public function setPrevButtonFlg($prevButtonFlg)
    {
        $this->prevButtonFlg = $prevButtonFlg;
    }

    /**
     * 「前へ」ボタン表示制御フラグを取得する
     *
     * @access public
     * @return $prevButtonFlg 「前へ」ボタン表示制御フラグ
     */
    public function getPrevButtonFlg()
    {
        return $this->prevButtonFlg;
    }

    /**
     * 「次へ」ボタン表示制御フラグを設定する
     *
     * @access public
     * @param $nextButtonFlg 「次へ」ボタン表示制御フラグ
     */
    public function setNextButtonFlg($nextButtonFlg)
    {
        $this->nextButtonFlg = $nextButtonFlg;
    }

    /**
     * 「次へ」ボタン表示制御フラグを取得する
     *
     * @access public
     * @return $nextButtonFlg 「次へ」ボタン表示制御フラグ
     */
    public function getNextButtonFlg()
    {
        return $this->nextButtonFlg;
    }

    /**
     * ページングボタン表示制御フラグを設定する
     *
     * @access public
     * @param $pagingButtonList ページングボタン表示制御フラグ
     */
    public function setPagingButtonList($pagingButtonList)
    {
        $this->pagingButtonList = $pagingButtonList;
    }

    /**
     * ページングボタン表示制御フラグを取得する
     *
     * @access public
     * @return $pagingButtonList ページングボタン表示制御フラグ
     */
    public function getPagingButtonList()
    {
        return $this->pagingButtonList;
    }
}
