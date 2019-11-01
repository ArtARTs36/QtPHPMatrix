<?php

class MainWindow extends QMainWindow
{
    const GRADATION_MIN = 1;
    const GRADATION_MAX = 2;

    const SEARCH_ROW = 0;
    const SEARCH_COL = 1;
    const SEARCH_ALL = 2;

    /** @var array Здесь храним массив матрицы после каждой генерации */
    private $matrix;

    private $ui;

    public function __construct($parent = null)
    {
        parent::__construct($parent);
        $this->ui = setupUi($this);
        $this->setFormDefaultValues();
        $this->makeEventButtonGenerate();
        $this->makeEventButtonFindMinNumber();
        $this->makeEventButtonResetSelection();
        $this->makeEventButtonReset();
    }

    /**
     * Заполняем значения формы случайными числами
     */
    private function setFormDefaultValues()
    {
        $randStart = rand(5, 100);
        $randEnd = $randStart + rand(5, 100);

        $this->ui->randStart->setText(new QString((string)$randStart));
        $this->ui->randEnd->setText(new QString((string)$randEnd));

        $this->ui->rowCount->setText((string)rand(5, 10));
        $this->ui->columnCount->setText((string)rand(5, 10));
    }

    /**
     * Вешаем клик на кнопку "Сбросить" для формы генерации
     */
    private function makeEventButtonReset()
    {
        $this->ui->buttonReset->onClicked = function () {
            $this->ui->randStart->setText('');
            $this->ui->randEnd->setText('');

            $this->ui->rowCount->setText('');
            $this->ui->columnCount->setText('');
        };
    }

    /**
     * Обрабатываем клик по кнопке "Сгенерировать"
     */
    private function makeEventButtonGenerate()
    {
        $this->ui->buttonGenerate->onClicked = function () {
            $rowCount = (int)$this->ui->rowCount->text()->__toString();
            $columnCount = (int)$this->ui->columnCount->text()->__toString();
            $randStart = (int)$this->ui->randStart->text()->__toString();
            $randEnd = (int)$this->ui->randEnd->text()->__toString();
            // ->text() - возвращает объект QString, поэтому дергаю из него строку и привожу к инту

            if ((
                $rowCount > 0 &&
                $columnCount > 0 &&
                $randStart > 0 &&
                $randEnd > 0
            )) {
                $this->genMatrix($rowCount, $columnCount, $randStart, $randEnd);
            } else {
                $messageBox = new QMessageBox(
                    QMessageBox::Information,
                    'Ошибка!',
                    'Проверьте правильность введенных данных!'
                );

                $messageBox->addButton("Закрыть", QMessageBox::AcceptRole);
                return $messageBox->exec();
            }
        };
    }

    /**
     * Обрабатываем клик на кнопку "Сбросить выделения"
     */
    private function makeEventButtonResetSelection()
    {
        $this->ui->buttonResetSelection->onClicked = function () {
            if (isset($this->matrix[0][0])) {
                $rowCount = count($this->matrix);
                $columnCount = count($this->matrix[0]);

                for ($i = 0; $i < $rowCount; $i++) {
                    for ($j = 0; $j < $columnCount; $j++) {
                        $this->ui->matrix->setItem(
                            $i,
                            $j,
                            new QTableWidgetItem(
                                (string)$this->matrix[$i][$j]
                            )
                        );
                    }
                }
            }
        };
    }

    /**
     * Клик по кнопке "Найти"
     */
    private function makeEventButtonFindMinNumber()
    {
        $this->ui->buttonFindMinNumber->onClicked = function () {
            $minNumberRowOrColumn = (int)$this->ui->minNumberRowOrColumn->text()->__toString();

            $typeSearch = $this->ui->typeSearch->currentIndex();

            $gradation = ($this->ui->typeGradationMin->isChecked() ? self::GRADATION_MIN : (
            $this->ui->typeGradationMax->isChecked() ? self::GRADATION_MAX : 0
            ));

            if (
                $minNumberRowOrColumn > 0 &&
                isset($this->matrix[0][0]) &&
                $gradation > 0
            ) {
                $this->findMinOrMaxItem(
                    $typeSearch,
                    $gradation,
                    $minNumberRowOrColumn
                );
            } else {
                $messageBox = new QMessageBox(
                    QMessageBox::Information,
                    'Ошибка!',
                    'Проверьте правильность введенных данных!'
                );

                $messageBox->addButton("Закрыть", QMessageBox::AcceptRole);

                return $messageBox->exec();
            }
        };
    }

    /**
     * Поиск максимального/минимального
     *
     * @param $type - По строке/столбцу
     * @param $gradation - Мин/макс
     * @param $number - Номер строки/столбца
     */
    private function findMinOrMaxItem($type, $gradation, $number)
    {
        $matrix = $this->matrix;

        --$number;

        $value = ($gradation == self::GRADATION_MIN ? 999999999 : 0);
        $rowId = (int)0;
        $colId = (int)0;

        if ($type == self::SEARCH_ALL) {
            foreach ($matrix as $i => $row) {
                foreach ($row as $j => $col) {
                    if (
                        ($gradation == self::GRADATION_MIN && $col < $value) ||
                        ($gradation == self::GRADATION_MAX && $col > $value)
                    ) {
                        $value = $col;
                        $rowId = $i;
                        $colId = $j;
                    }
                }
            }
        } elseif ($type == self::SEARCH_ROW) {
            $rowId = $number;
            $row = $matrix[$number];

            foreach ($row as $j => $col) {
                if (
                    ($gradation == 1 && $col < $value) ||
                    ($gradation == 2 && $col > $value)
                ) {
                    $value = $col;
                    $colId = $j;
                }
            }
        } else {
            $colId = $number;
            foreach ($matrix as $i => $row) {
                if (
                    ($gradation == 1 && $row[$colId] < $value) ||
                    ($gradation == 2 && $row[$colId] > $value)
                ) {
                    $value = $row[$colId];
                    $rowId = $i;
                }
            }
        }

        $color = new QBrush(new QColor(255, 255, 0));

        $this->ui->matrix->item($rowId, $colId)->setBackground($color);
    }

    /**
     * Генерация матрицы
     *
     * @param int $rowCount
     * @param int $columnCount
     * @param int $randStart
     * @param int $randEnd
     */
    private function genMatrix($rowCount = 10, $columnCount = 10, $randStart = 5, $randEnd = 100)
    {
        $this->ui->matrix->setColumnCount($columnCount);
        $this->ui->matrix->setRowCount($rowCount);

        for ($i = 0; $i < $rowCount; $i++) {
            for ($j = 0; $j < $columnCount; $j++) {
                $rand = rand($randStart, $randEnd);
                $this->matrix[$i][$j] = $rand;

                $this->ui->matrix->setItem($i, $j, new QTableWidgetItem((string)$rand));
                // string - потому, что QTableWidgetItem - не пашет с интами
            }
        }
    }
}
