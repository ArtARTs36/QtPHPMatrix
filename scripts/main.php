<?php

require_once("qrc://scripts/mainwindow.php");

$app = new QApplication($argc, $argv);

$w = new MainWindow;
$w->setWindowTitle('Генерация матрицы случайных чисел');
$w->show();

return $app->exec();
