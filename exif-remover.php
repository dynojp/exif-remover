<?php

/*
 * Plugin Name: Exif Remover
 * Description: メディアに画像がアップロードされたときに EXIF データを自動削除する。サポート対象フォーマット: JPEG / PNG / WebP 。
 * Version: 0.1.0
 * Author: 後藤隼人
 * Author URI: https://dyno.design/
 */

defined( 'ABSPATH' ) || exit;

/**
 * 画像アップロード時のアクションフック: Exif 情報を削除する
 */
add_action('add_attachment', function ($attachment_id) {
  try {
    $result = exif_remover_remove_exif($attachment_id);

    if ($result !== true) {
      if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Exif Remover Result for ID {$attachment_id}: {$result}");
      }
    }
  } catch (Exception $e) {
    error_log('Exif Remover Error: ' . $e->getMessage());
  }  
});

/**
 * 画像の EXIF 情報を削除する
 */
function exif_remover_remove_exif($attachment_id) { 
  $path = get_attached_file($attachment_id);

  if (!file_exists($path)) {
    return 'ファイルが見つかりませんでした';
  }

  $type = exif_imagetype($path); 
  switch ($type) {
   case IMAGETYPE_JPEG:
      $image = imagecreatefromjpeg($path);
      imagejpeg($image, $path, 100);
      imagedestroy($image);
      break;
    case IMAGETYPE_PNG:
      $image = imagecreatefrompng($path);
      imagepng($image, $path, 9);
      imagedestroy($image);
      break;
    case IMAGETYPE_WEBP:
      $image = imagecreatefromwebp($path);
      imagewebp($image, $path, 100);
      imagedestroy($image);
      break;
    default:
      return '対応していないファイルフォーマット';
  }
  
  return true;
}
