<?php
namespace App\Util;

use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
/**
 * 画像ファイル関連処理をまとめたUtilityクラス
 *
 * @access public
 * @author Takayuki_suzuki
 * @package Util
 */
class ImageFileUtil
{
    protected $uploadFilePath;
    protected $uploadTempFilePath;

    /**
     * 初期化処理
     *
     * @access public
     */
    public function __construct()
    {
        // 「app.php」から画像アップロード先のパスを取得する
        Configure::config('default', new PhpConfig());
        Configure::load('app', 'default', false);
        $this->uploadFilePath = Configure::read('App.uploadFilePath');
        $this->uploadTempFilePath = Configure::read('App.uploadTempFilePath');
    }

    /**
     * 画像情報を登録するメソッド
     *
     * @access public
     * @param $imageData 画像情報
     * @param $postId 記事ID
     * @param $session セッション情報
     *
     * @return $imageIｄ 登録した画像のID
     */
    public function insertImage($imageData, $postId, $session)
    {
        // 現在日付を取得する(マイクロ秒単位)
        $currentDate = date('YmdHisU');

        // ファイル名を決定する
        $fileType = explode('/', $imageData['type']);
        $fileName = $currentDate . '.' . $fileType[1];

        // ディレクトリ作成用の現在日付を取得する
        $directoryCreateDate = date('Ymd');

        // ディレクトリを作成する
        $directoryPath = $this->uploadFilePath . $directoryCreateDate;
        // mkdir($directoryPath);

        // 画像の格納先のパス(絶対パス)
        $fullPath = $this->uploadFilePath . $directoryCreateDate . DS . $fileName;

        // 画像ファイルを指定した場所に格納する
        $result = rename($session->read('tempFileInfo.tempFilePath'), $fullPath);
        if (! $result) {
            throw new NotFoundException(__('画像のアップロードに失敗しました。'));
        }

        // 画像情報を保存用画像リストに格納する
        $imageSaveData = [];
        $imageSaveData['filename'] = $fileName;
        $imageSaveData['type'] = $imageData['type'];
        $imageSaveData['size'] = $imageData['size'];
        $imageSaveData['path'] = $fullPath;

        // 画像情報をDBに登録する
        $images = TableRegistry::get('Images');
        $imageEntity = $images->newEntity();
        $imagesEntity = $images->patchEntity($imageEntity, $imageSaveData);
        $imagesEntity->post_id = $postId;
        $imagesEntity->created = $currentDate;
        $imagesEntity->modified = $currentDate;
        $images->save($imagesEntity);

        // 一定期間過ぎた一時ファイルを削除する
        $this->deleteTempFile();

        // 一時ファイル存在フラグを削除する
        $session->delete('tempFileExistsFlg');
    }

    /**
     * 画像情報を一時的に格納するメソッド
     *
     * @access private
     * @param $imageData 画像情報
     * @param $session セッション情報
     */
    public function insertTempImage($imageData, $session)
    {
        // 現在日付を取得する(マイクロ秒単位)
        $currentDate = date('YmdHisU');

        // ファイル名を決定する
        $fileType = explode('/', $imageData['type']);
        $fileName = 'temp_' . $currentDate . '.' . $fileType[1];

        // 画像の一時格納先のパス(絶対パス)
        $fullPath = $this->uploadTempFilePath . $fileName;

        // 画像ファイルを指定した場所に一時的に格納する
        $result = move_uploaded_file($imageData["tmp_name"], $fullPath);
        if (! $result) {
            throw new NotFoundException(__('画像のアップロードに失敗しました。'));
        }

        // 各情報をセッションに設定する
        $session->write('tempFileInfo.tempFilePath', $fullPath); // 一時格納先
        $session->write('tempFileInfo.tempFileType', $imageData['type']); // 一時格納先
        $session->write('fileUploadFlg', true); // ファイルアップロードフラグ
        $session->write('tempFileExistsFlg', true); // 一時ファイル存在フラグ
    }

    /**
     * 画像情報を確認するメソッド
     *
     * @access private
     * @param $imageData 画像情報
     */
    public function checkImage($imageData)
    {
        // 判定結果フラグ
        $resultFlg = false;

        try {
            // errorの値を確認
            switch ($imageData['error']) {
                case UPLOAD_ERR_OK: // OK
                    break;
                case UPLOAD_ERR_INI_SIZE: // php.ini定義の最大サイズ超過
                case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
                    $this->Flash->error(__('画像ファイルのサイズが大き過ぎます'));
                    return true;
                default:
                    $this->Flash->error(__('その他のエラーが発生しました'));
                    return true;
            }

            // ファイルサイズ上限チェック
            if ($imageData['size'] > 1000000) {
                $this->Flash->error(__('画像ファイルのサイズが大き過ぎます'));
                return true;
            }

            // 拡張子チェック
            switch ($imageData['type']) {
                case 'image/jpeg':
                    break;
                case 'image/png':
                    break;
                case 'image/gif':
                    break;
                default:
                    $this->Flash->error(__('画像ファイルの拡張子が不正です'));
                    return true;
                    break;
            }
        } catch (RuntimeException $e) {
            return true;
        }
        return $resultFlg;
    }

    /**
     * 一定期間を過ぎた一時ファイルを削除するメソッド
     *
     * @access private
     * @param $imageData 画像情報
     */
    private function deleteTempFile()
    {
        // 一時保存画像ファイル保存期限(テストの為、短い期間で設定)
        $tempImageSaveTerm = '-1 min';
        $tempFullPath = $this->uploadTempFilePath; // 一時保存ファイルパス
        $expireTime = strtotime($tempImageSaveTerm); // 削除期限
        $deleteFileList = scandir($tempFullPath); // 削除ファイルリスト

        // 削除対象ファイルリストの数だけ繰り返す
        foreach ($deleteFileList as $fileValue) {
            $tempFileFullPath = $tempFullPath . $fileValue;
            // ファイルが存在している場合
            if (! is_file($tempFileFullPath))
                continue;
            // ファイルの作成時間を取得する
            $fileCreateTime = filemtime($tempFileFullPath);

            // 一時保存した画像が指定した期間以上前の場合
            if ($fileCreateTime < $expireTime) {
                unlink($tempFileFullPath);
            }
        }
    }
}
