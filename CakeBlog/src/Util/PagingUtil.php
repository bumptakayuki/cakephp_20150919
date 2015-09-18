<?php
namespace App\Util;

use App\Bean\PagingInfoBean;

/**
 * ページング処理をまとめたUtilityクラス
 *
 * @access public
 * @author Takayuki_suzuki
 * @package Util
 */
class PagingUtil
{
    // ページング表示件数
    protected $displayLimit = 5;
    
    // ページング表示件数
    protected $maxPageDisplayLimit = 7;
    
    /**
     * ページングメソッド
     *
     * @access public
     * @param int $currentPageId 現在ページ番号
     * @param int $endPageId 終了ページ番号
     * @return $pagingInfoBean ページング情報
     */
    public function paging($currentPageId, $endPageId)
    {
        // ページングボタン表示制御フラグ
         $pagingDispButtonFlg = true;
        
        if($endPageId <= 1){
            $pagingDispButtonFlg = false;
        }
    
        // 初期値を設定
        $pagingButtonList = [];
        $betweenDispCount = 0;
        $startPageId = 1;

        // 存在するページの数だけ繰り返す
        for ($i = 1; $i <= $endPageId; $i ++) {

            // 最大表示件数以内の場合
            if ($this->maxPageDisplayLimit > count($pagingButtonList)) {

                // 開始ページから現在ページ番号が3番目以内の場合
                if ($startPageId + 2 >= $currentPageId) {
                    
                    // 最初と最後の場合 または現在ページの前後の番号の場合
                    if ($startPageId + 3 >= $i || $currentPageId == $i || $endPageId == $i) {
                        // ページ番号を設定する
                        $pagingIdSetFlg = true;
                        $pagingButtonInfo = $this->setPagingId($currentPageId, $pagingIdSetFlg, $i);
                        array_push($pagingButtonList, $pagingButtonInfo);
                    }
                    // 開始ページから4つ目のページの場合
                    if ($startPageId + 4 == $i) {
                        // ページ番号を省略する
                        $pagingIdSetFlg = false;
                        $pagingButtonInfo = $this->setPagingId($currentPageId, $pagingIdSetFlg, $i);
                        array_push($pagingButtonList, $pagingButtonInfo);
                    }

                // 終了ページから現在ページ番号が3番目以内の場合
                } elseif ($endPageId - 2 <= $currentPageId) {
                    
                    // 最初と最後の場合 または現在ページの前後の番号の場合
                    if ($endPageId - 3 <= $i || $currentPageId == $i || $endPageId == $i || $startPageId == $i) {
                        // ページ番号を設定する
                        $pagingIdSetFlg = true;
                        $pagingButtonInfo = $this->setPagingId($currentPageId, $pagingIdSetFlg, $i);
                        array_push($pagingButtonList, $pagingButtonInfo);
                    }
                    // 終了ページから4つ目のページの場合
                    if ($endPageId - 4 == $i) {
                        // ページ番号を省略する
                        $pagingIdSetFlg = false;
                        $pagingButtonInfo = $this->setPagingId($currentPageId, $pagingIdSetFlg, $i);
                        array_push($pagingButtonList, $pagingButtonInfo);
                    }

                // 上記以外の場合(現在ページが中間位置の場合)
                } else {
                    // 最初と最後の場合 または現在ページの前後の番号の場合
                    if ($i == $startPageId || $endPageId == $i || $currentPageId - 1 == $i || $currentPageId == $i || $currentPageId + 1 == $i) {
                        // ページ番号を設定する
                        $pagingIdSetFlg = true;
                        $pagingButtonInfo = $this->setPagingId($currentPageId, $pagingIdSetFlg, $i);
                        array_push($pagingButtonList, $pagingButtonInfo);
                        
                    // 現在ページから前後2つの場合
                    } elseif ($currentPageId - 2 == $i || $currentPageId + 2 == $i) {
                        // ページ番号を省略する
                        $pagingIdSetFlg = false;
                        $pagingButtonInfo = $this->setPagingId($currentPageId, $pagingIdSetFlg, $i);
                        array_push($pagingButtonList, $pagingButtonInfo);
                    }
                }
            }
        }
        // 「前へ」ボタン表示制御
        $prevButtonFlg = $this->isPrevButtonFlg($currentPageId);
    
        // 「次へ」ボタン表示制御
        $nextButtonFlg = false;
        if (strcmp($currentPageId, $endPageId)) {
            $nextButtonFlg = true;
        }
        
        // 返却するページング情報を設定する
        $pagingInfoBean = new PagingInfoBean();
        $pagingInfoBean->setCurrentPageId($currentPageId);
        $pagingInfoBean->setEndPageId($endPageId);
        $pagingInfoBean->setPagingDispButtonFlg($pagingDispButtonFlg);
        $pagingInfoBean->setPrevButtonFlg($prevButtonFlg);
        $pagingInfoBean->setNextButtonFlg($nextButtonFlg);
        $pagingInfoBean->setPagingButtonList($pagingButtonList);

        return $pagingInfoBean;
    }
    
    /**
     * // 「前へ」ボタン表示制御判定メソッド
     *
     * @access private
     * @return $prevButtonFlg 「前へ」ボタン表示制御フラグ
     */
    private function isPrevButtonFlg($currentPageId)
    {
        $prevButtonFlg = false;
        switch ($currentPageId) {
            case 0:
            case 1:
                break;
            default:
                $prevButtonFlg = true;
                break;
        }
        return $prevButtonFlg;
    }
    
    /**
     * // ページング番号表示設定メソッド
     *
     * @access private
     * @param int $currentPageId 現在ページ番号
     * @param boolean $pagingIdSetFlg ページング番号設定フラグ
     * @param int $pageCount 繰り返しページ番号
     * @return array $pagingButtonList ページボタンリスト
     */
    private function setPagingId($currentPageId, $pagingIdSetFlg, $pageCount)
    {
        // ページングボタンリスト
        $pagingButtonInfo = [];
    
        // ページング番号設定フラグがTRUE(表示)の場合
        if ($pagingIdSetFlg) {
            $pagingButtonInfo['pageId'] = $pageCount; // ページリンク番号
            $pagingButtonInfo['pagingButtonDispFlg'] = true; // ページリンク表示制御フラグ
             
            // 現在ページの場合
            if ($currentPageId == $pageCount) {
                $pagingButtonInfo['current'] = 'current';
            } else {
                $pagingButtonInfo['current'] = '';
            }
        }
        // ページング番号設定フラグがFALSE(非表示)の場合
        if (! $pagingIdSetFlg) {
            // ページ番号を省略する
            $pagingButtonInfo['current'] = '';
            $pagingButtonInfo['pagingButtonDispFlg'] = false;
        }
        return $pagingButtonInfo;
    }
}