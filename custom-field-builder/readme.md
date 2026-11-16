## 使い方

1. custom-field-builder-config.php を Custom Field Builder を使用するプラグインディレクトリ（incなど）に複製
1. custom-field-builder-config.php の中身をプラグインの情報にあわせて書き換える
1. プラグインが最初に読み込むPHPファイルなどから require_once( 'inc/custom-field-builder-config.php' ); などで読み込む

## サンプルコードについて

`class-flexible-table-sample.php` は、フィールド定義からフォーム表示・保存までの一連の記述例を示す**サンプルコード**です。ライブラリ本体や config からは読み込まれません。実装の参考としてご利用ください。

## 更新履歴

* [ 不具合修正 ] URL・画像の項目で、整形すると 2048 バイトを超える値（空白を含む長い URL など）が一度保存され、保存し直すと空になる不具合を修正
* [ 仕様変更 ] 2048 バイトを超える入力値を保存しないように変更（URL・画像・ファイルは空、複数行テキスト以外は切り詰め）
* [ 不具合修正 ] 入れ子配列を一律テキストサニタイズして値が失われ、textarea・URLを保存できない不具合を修正
