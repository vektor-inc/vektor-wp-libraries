## 使い方

1. custom-field-builder-config.php を Custom Field Builder を使用するプラグインディレクトリ（incなど）に複製
1. custom-field-builder-config.php の中身をプラグインの情報にあわせて書き換える
1. プラグインが最初に読み込むPHPファイルなどから require_once( 'inc/custom-field-builder-config.php' ); などで読み込む

## サンプルコードについて

`class-flexible-table-sample.php` は、フィールド定義からフォーム表示・保存までの一連の記述例を示す**サンプルコード**です。ライブラリ本体や config からは読み込まれません。実装の参考としてご利用ください。
