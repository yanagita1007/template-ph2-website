<?php
require __DIR__ . '/../../dbconnect.php';
require __DIR__ . '/../../vendor/autoload.php';

// use Verot\Upload\Upload;

session_start();

if (!isset($_SESSION['id'])) {
    // header('Location: /admin/auth/signin.php');
    // exit;
}

$sql = "SELECT * FROM questions WHERE id = :id";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(":id", $_REQUEST["id"]);
$stmt->execute();
$question = $stmt->fetch();

$sql = "SELECT * FROM choices WHERE question_id = :question_id";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(":question_id", $_REQUEST["id"]);
$stmt->execute();
$choices = $stmt->fetchAll();

$image_name = $question["image"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    $dbh->beginTransaction();

    // ファイルアップロード
    $file = $_FILES['image'];
    $lang = 'ja_JP';

    if (!empty($file['name'])) {
        $handle = new Upload($file, $lang);

    if (!$handle->uploaded) {
        throw new Exception($handle->error);
    }

      // ファイルサイズのバリデーション： 5MB
        $handle->file_max_size = '5120000';
      // ファイルの拡張子と MIMEタイプをチェック
        $handle->allowed = array('image/jpeg', 'image/png', 'image/gif');
      // PNGに変換して拡張子を統一
        $handle->image_convert = 'png';
        $handle->file_new_name_ext = 'png';
      // サイズ統一
        $handle->image_resize = true;
        $handle->image_x = 718;
      // アップロードディレクトリを指定して保存
        $handle->process('../../assets/img/quiz/');
        if (!$handle->processed) {
        throw new Exception($handle->error);
        }

      // 更新前の画像を削除
        if ($image_name) {
        $image_path = __DIR__ . '/../../assets/img/quiz/' . $image_name;
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        }

        $image_name = $handle->file_dst_name;
    }

    // 問題レコードの更新
    $sql = "UPDATE questions SET image = :image, content = :content, supplement = :supplement WHERE id = :id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(":image", $image_name);
    $stmt->bindValue(":content", $_POST["content"]);
    $stmt->bindValue(":supplement", $_POST["supplement"]);
    $stmt->bindValue(":id", $_POST["question_id"]);
    $stmt->execute();

    // 選択肢レコードの更新
    $sql = "UPDATE choices SET name = :name, valid = :valid WHERE id = :id AND question_id = :question_id";
    // 各選択肢についてループ
    for ($i = 0; $i < count($_POST["choices"]); $i++) {
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(":name", $_POST["choices"][$i]);
        $stmt->bindValue(":valid", (int)($_POST['correctChoice'] == $_POST["choice_ids"][$i]) ? 1 : 0);
        $stmt->bindValue(":id", $_POST["choice_ids"][$i]);
        $stmt->bindValue(":question_id", $_POST["question_id"]);
        $stmt->execute();
    }
    $dbh->commit();
    $_SESSION['message'] = "問題編集に成功しました。";
    header('Location: /admin/index.php');
    exit;
} catch (PDOException $e) {
    $dbh->rollBack();
    $_SESSION['message'] = "問題編集に失敗しました。";
    error_log($e->getMessage());
    exit;
}
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POSSE 管理画面ダッシュボード</title>
    <!-- スタイルシート読み込み -->
    <link rel="stylesheet" href="./assets/styles/common.css">
    <link rel="stylesheet" href="../admin.css">
    <!-- Google Fonts読み込み -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <?php 
    // include __DIR__ . '/../../components/admin/header.php'; 
    ?>
    <header id="js-header" class="l-header p-header">
    <div class="p-header__logo"><img src="./assets/img/logo.svg" alt="POSSE"></div>
    <button class="p-header__button" id="js-headerButton"></button>
    <!-- <div class="p-header__inner">
        <nav class="p-header__nav">
        <ul class="p-header__nav__list">
            <li class="p-header__nav__item">
            <a href="./" class="p-header__nav__item__link">POSSEとは</a>
            </li>
            <li class="p-header__nav__item">
            <a href="./quiz/" class="p-header__nav__item__link">クイズ</a>
            </li>
        </ul>
        </nav>
        <div class="p-header__official">
        <a href="https://line.me/R/ti/p/@651htnqp?from=page" target="_blank" rel="noopener noreferrer" class="p-header__official__link--line">
            <i class="u-icon__line"></i>
            <span class="">POSSE公式LINEを追加</span>
            <i class="u-icon__link"></i>
        </a>
        <a href="" class="p-header__official__link--website">POSSE 公式サイト<i class="u-icon__link"></i></a>
    </div>
    <ul class="p-header__sns p-sns">
        <li class="p-sns__item">
        <a href="https://twitter.com/posse_program" target="_blank" rel="noopener noreferrer" class="p-sns__item__link"
            aria-label="Twitter">
            <i class="u-icon__twitter"></i>
            </a>
        </li>
        <li class="p-sns__item">
            <a href="https://www.instagram.com/posse_programming/" target="_blank" rel="noopener noreferrer"
            class="p-sns__item__link" aria-label="instagram">
            <i class="u-icon__instagram"></i>
            </a>
        </li>
        </ul>
    </div> -->
    </header>
    <div class="wrapper">
    <?php 
    // include __DIR__ . '/../../components/admin/sidebar.php'; 
    ?>
    <aside>
            <nav>
            <ul>
                <li><a href="/admin/index.php">ユーザー招待</a></li>
                <li><a href="/admin/index.php">問題一覧</a></li>
                <li><a href="/admin/questions/create.php">問題作成</a></li>
            </ul>
            </nav>
    </aside>
    <main>
        <div class="container">
        <h1 class="mb-4">問題編集</h1>
        <form class="question-form" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
            <label for="question" class="form-label">問題文:</label>
            <input type="text" name="content" id="question" class="form-control required" value="<?= $question["content"] ?>" placeholder="問題文を入力してください" />
            </div>
            <div class="mb-4">
            <label class="form-label">選択肢:</label>
            <div class="form-choices-container">
                <?php foreach ($choices as $key => $choice) { ?>
                <input type="text" name="choices[]" class="required form-control mb-2" placeholder="選択肢を入力してください" value=<?= $choice["name"] ?>>
                <input type="hidden" name="choice_ids[]" value="<?= $choice["id"] ?>">
                <?php } ?>
            </div>
            </div>
            <div class="mb-4">
            <label class="form-label">正解の選択肢</label>
            <div class="form-check-container">
                <?php foreach ($choices as $key => $choice) { ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="correctChoice" id="correctChoice<?= $key ?>" value="<?= $choice["id"] ?>" <?= $choice["valid"] === 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="correctChoice1">
                    選択肢<?= $key + 1 ?>
                    </label>
                </div>
                <?php } ?>
            </div>
            </div>
            <div class="mb-4">
            <label for="question" class="form-label">問題の画像</label>
            <input type="file" name="image" id="image" class="form-control" />
            </div>
            <div class="mb-4">
            <label for="question" class="form-label">補足:</label>
            <input type="text" name="supplement" id="supplement" class="form-control" placeholder="補足を入力してください" value="<?= $question["supplement"] ?>" />
            </div>
            <input type="hidden" name="question_id" value="<?= $question["id"] ?>">
            <button type="submit" class="btn submit">更新</button>
        </form>
        </div>
    </main>
</div>
<script>
    const submitButton = document.querySelector('.btn.submit')
    const inputDoms = Array.from(document.querySelectorAll('.required'))
    inputDoms.forEach(inputDom => {
        inputDom.addEventListener('input', event => {
        const isFilled = inputDoms.filter(d => d.value).length === inputDoms.length
        submitButton.disabled = !isFilled
        })
    })
</script>
</body>

</html>