<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= ($titel ?? 'typeshyt') ?> – typeshyt</title>
    <link rel="stylesheet" href="<?= url('/css/style.css') ?>">
    <link rel="stylesheet" href="<?= url('/css/filter.css') ?>">
</head>
<body>
<header class="topbar">
    <a class="logo" href="<?= url('/ticket/') ?>">typeshyt</a>
    <nav>
        <a href="<?= url('/ticket/') ?>">Board</a>
        <a class="btn btn-primary" href="<?= url('/ticket/ticket_form.php') ?>">+ Neues Ticket</a>
    </nav>
</header>
<main>
