# template-tags

このディレクトリが、投稿タイプの判定やページの説明文取得など、複数の VEKTOR 製品で共通して使うテンプレートタグ関数（記事一覧や記事ページの表示に使う、投稿タイプ名・アーカイブタイトル・ページ説明文などを取得する関数群）の**正本（元データ。修正はここに対して行う場所）**です。この関数群は、製品ごとに別々のリポジトリへ**同梱**（プラグイン・テーマ本体のファイルとして一緒に配布すること）されています。

## 同梱先

このディレクトリのファイルは、下記2つの製品にコピーされて配布されます。それぞれ別リポジトリの別プラグインで、Composer（PHP のパッケージ管理ツール）等の依存関係管理は使わず、ファイルをそのまま複製する方式で配布しています（配布方法の方針は vektor-inc/vk-all-in-one-expansion-unit#1476 で決定済みで、本ディレクトリでは再検討しません）。

同梱先パスはどちらも `inc/template-tags/package/` ですが、**コピーされるファイルは製品によって異なります**（各製品の `inc/template-tags/template-tags-config.php` が、自分が実際に読み込むファイルだけを `require_once` しているため）。

| 製品 | 同梱先パス | コピーされるファイル |
|---|---|---|
| VK All in One Expansion Unit（ExUnit） | `inc/template-tags/package/` | `template-tags.php` / `template-tags-veu.php` / `template-tags-veu-old.php` の3ファイルすべて |
| VK Post Author Display | `inc/template-tags/package/` | `template-tags.php` の1ファイルのみ |

**ExUnit で3ファイルが必ずセットで必要な理由:** 正本の `template-tags.php` は `vk_sanitize_boolean()` を定義しており、旧名の `veu_sanitize_boolean()` は同じ `package/` 内の `template-tags-veu-old.php` が橋渡し（新名を呼び出すだけの薄いラッパー）しています。ExUnit の `inc/sns/sns_customizer.php` は17箇所で `'sanitize_callback' => 'veu_sanitize_boolean'` を登録しているため、`template-tags-veu-old.php` を配り忘れると、ExUnit のカスタマイザー（外観 > カスタマイズ画面）保存時に「未定義の関数を呼び出した」という致命的エラーになります。`template-tags-veu.php` も同様に ExUnit の他のコードから呼ばれる関数を含むため、3ファイルは分割せずセットで配布します。

VK Post Author Display は `template-tags.php` の関数しか使わないため、他の2ファイルを配ると使われないコード（デッドコード）が同梱されるだけになります。そのため `gulpfile.js` の `c_tags` タスクでは、コピー元のパターンを製品ごとに分けています。

### テストは配らない

`template-tags/tests/` 配下の PHPUnit テスト（自動テストのコード）は、`gulp c_tags` では**配布しません**。以前はコピーしていましたが、次の問題が判明したため取りやめました。

- ExUnit・VK Post Author Display とも、共有関数に対する自前のテストを既に持っており、正本のものより充実しています（例: ExUnit の `tests/test-template-tags.php` は同名で、正本の3倍以上のテストケースを持ちます）。同じファイル名でコピーすると、この製品固有の充実したテストが正本の内容で**上書きされて消えます**。
- 正本のテストが、ExUnit・PAD のどちらにも存在しない関数（`vk_is_block_category_exist()`）を呼んでいたことがあり、そのままコピーするとコピー先で「未定義の関数を呼び出した」エラーになりテストスイートごと落ちる状態でした（実際に配布して壊れる前にレビューで発見し、正本側からはこのテストを削除済みです）。
- 上書きを避けるためにファイル名を変える案も検討しましたが、テストクラス名（`TemplateTagsTest`）も両製品と衝突するため、クラス名の付け方と各製品の PHPUnit 設定まで含めた設計が別途必要になり、この配布の仕組みだけでは解決できません。

そのため、共有関数のテストは**各製品が自分の `tests/` 配下で持つ**方針とし、正本の `template-tags/tests/` は正本側の動作確認専用として残しています。

## 配布方法（正本を変更したときにやること）

1. このディレクトリの `template-tags/package/` を修正する（`template-tags/tests/` は正本側の動作確認専用で、上記のとおり配布されない）。
2. リポジトリ直下で `gulp c_tags` を実行する（`gulpfile.js` の `c_tags` タスク）。これで、上記2製品のディレクトリへファイルがコピーされる。
   - **注意:** `gulp c_tags` は、このリポジトリと同じ階層に2製品のリポジトリ（`../plugins/vk-all-in-one-expansion-unit/` と `../plugins/vk-post-author-display/`）が並んで存在することを前提にしている。存在しない場所で実行すると、そのディレクトリの外へ書き込もうとしてエラーになるか、意図しない場所にファイルが作られる。
3. コピー先の各製品リポジトリで、通常のリリース手順に沿ってリリースする（正本を直しただけでは、製品を使っているサイトには反映されない）。

### テキストドメインの置換

翻訳文字列（画面に表示する文言を翻訳できるようにするため `__()` などの翻訳関数に渡す文字列）には、どの製品の翻訳データと紐付けるかを示す「テキストドメイン」という識別子が必要です。製品ごとに異なる値を持つ必要があるため、正本側では実在しない仮の識別子 `'template_tags_textdomain'` を使っています。

`gulp c_tags` の実行時、この仮の識別子を製品ごとの実際のテキストドメインへ自動的に置換します（`gulp-replace` という、ファイルの中の文字列を置換しながらコピーする仕組みを使用）。

| 同梱先 | 置換後のテキストドメイン |
|---|---|
| ExUnit | `vk-all-in-one-expansion-unit` |
| VK Post Author Display | `vk-post-author-display` |

正本の PHP ファイルに翻訳関数を追加・変更する場合は、テキストドメインの引数に必ず仮の識別子 `'template_tags_textdomain'` を使ってください（製品名を直接書かない）。

## 関数名の規約

- 共有関数の名前には `vk_` 接頭辞を使います（例: `vk_get_post_type()`、`vk_get_page_description()`）。
- 過去に使われていた旧関数名（`vkExUnit_` 接頭辞、`veu_` 接頭辞）は、同じ `package/` ディレクトリ内の `template-tags-veu-old.php` が旧名から新名（`vk_` 接頭辞）へ橋渡し（旧名の関数の中身で新名の関数を呼び出すだけの薄いラッパー）しています。
- そのため、`template-tags.php` 側では旧名（`vkExUnit_` / `veu_`）の関数を直接定義しないでください。旧名を直接定義してしまうと、`template-tags-veu-old.php` 側の橋渡しが `function_exists()` の判定で無効化され、正本と製品側コピーで実装が二重化する原因になります。
- **例外: `veu_sanitize_radio()` と `veu_sanitize_boolean()` の2つは、上記の原則に反して `template-tags.php` に直接定義しています。** VK Post Author Display には `template-tags.php` の1ファイルしか配られず（`template-tags-veu-old.php` は配られません）、この2つの旧名は ExUnit だけでなく Lightning Pro / Katawara の `vk-campaign-text` からも `sanitize_callback` として直接呼ばれます。橋渡し役の `template-tags-veu-old.php` に置いたままだと PAD 環境で未定義関数エラーになるため、`function_exists()` で guarded にした上で `template-tags.php` 側にも置いています。今後この2つと同じ理由（PAD からも呼ばれる旧名）が見つかった場合は、同じ形で例外を増やして構いません。

### 正本では絶対に定義してはいけない9つの関数名（ExUnit 側で予約済み）

ExUnit は `inc/template-tags/exunit-template-tags.php`（vektor-wp-libraries から同期されない、ExUnit 自身が保守するファイル）で、下記9つの関数を **`function_exists()` ガード無しで**定義しています。

`veu_get_page_for_posts()` / `veu_get_post_type()` / `veu_get_page_description()` / `veu_the_post_type_check_list()` / `veu_the_taxonomy_check_list()` / `veu_the_post_type_check_list_saved_array_convert()` / `veu_is_checked()` / `veu_sanitize_number()` / `veu_is_excerpt()`

ガードが無い理由は、ExUnit 側が「他プラグインの古いコピーが先に読み込まれて自分の修正が効かなくなる」問題を避けるために、あえて `vk_` 版に委譲せず独立した実装を持たせているためです（詳細は ExUnit リポジトリの当該ファイルの docblock を参照）。**正本（このリポジトリ）がこの9つと同名の関数を定義すると、`gulp c_tags` で配布した際に ExUnit 側で「既に宣言されている関数を再宣言した」致命的エラーになり、サイト全体が止まります。** 正本に新しい共有関数を追加するときは、この9つの名前と重複しないか必ず確認してください。

## なぜ正本が必要か

`template-tags.php` 内の各関数は `function_exists()` で個別にガードされており、複数の同名ファイルが読み込まれた場合は最初に読み込まれた側の実装が採用されます。ExUnit と VK Post Author Display はそれぞれ独立したリポジトリで、同じファイルを同梱しているため、製品側で直接修正が入ると、そのままでは正本（このディレクトリ）に反映されず、放置すると製品間で実装が食い違っていきます。このディレクトリを正本と決め、修正は必ずここに対して行うことで、食い違いを防ぎます。

なお、「どのバージョンの `template-tags.php` が実際に採用されているか（先に読み込まれているか）を確認する手段」は vektor-inc/vk-all-in-one-expansion-unit#1479 で ExUnit 側に実装済みです。このリポジトリでは実装しません。

## template-tags-veu.php の関数ガードについての前提

`template-tags-veu.php` 内の一部の関数（`veu_get_common_options()` / `veu_get_common_options_default()` / `veu_common_options_validate()` / `veu_is_parent_metabox_display()` / `veu_is_insert_item_metabox_display()` / `veu_is_parent_metabox_display_maual()`）には `function_exists()` ガードがありません。これは**「`template-tags-veu.php` を読み込む同梱先は ExUnit の1製品だけ」という前提**の上で許容している状態です。VK Post Author Display はこのファイルを読み込みません（上記「同梱先」参照）。もし将来、`template-tags-veu.php` を読み込む製品が2つ以上になった場合は、この前提が崩れるため、ガード追加を改めて検討してください。
