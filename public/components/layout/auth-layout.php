<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Shift Studio' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body class="auth-body">

    <div class="auth-container">
        <div class="auth-left">
            <?= $content ?>
        </div>

        <div class="auth-right">
            <img src="assets/img/logo.png" alt="Shift Studio Logo">
        </div>
    </div>
</body>

</html>