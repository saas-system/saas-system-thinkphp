<?php

namespace app\common\library;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\facade\Log;

class SpreadsheetUtil
{
    protected $spreadSheet;

    /**
     * 当前活动sheet
     * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    protected $activeSheet;

    public $borderColor = Color::COLOR_BLACK; // 内容边框颜色

    /**
     * @var int 当前行号
     */
    private $currentRow;


    public function __construct()
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        $this->spreadSheet = new Spreadsheet();
        $this->activeSheet = $this->spreadSheet->getActiveSheet();
        $this->setDefaultDimension();
    }

    /**
     * 获取最大行
     * @return int
     */
    public function getHighestRow()
    {
        return $this->activeSheet->getHighestRow();
    }

    /**
     * 获取初始化的实例
     * @return Spreadsheet
     */
    public function getSpreadSheet()
    {
        return $this->spreadSheet;
    }

    /**
     * 获取最大列
     * @return string
     */
    public function getHighestColumn()
    {
        return $this->activeSheet->getHighestColumn();
    }

    /**
     * 设置默认高度、宽度
     * @param bool $autosize
     * @param int $rowHeight
     */
    public function setDefaultDimension($autosize = true, $rowHeight = 25)
    {
        if ($this->activeSheet) {
            $this->activeSheet->getDefaultColumnDimension()->setAutoSize($autosize);
            $this->activeSheet->getDefaultRowDimension()->setRowHeight($rowHeight);
        }

        return $this;
    }

    /**
     * 添加sheet
     * @param null $pIndex
     * @param bool $is_active
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function addSheet($pIndex = null)
    {
        $this->activeSheet = $this->spreadSheet->createSheet($pIndex);
        $this->setDefaultDimension();

        return $this;
    }

    /**
     * 设置活动sheet
     * @param $pIndex
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function setActiveSheet($pIndex)
    {
        $this->spreadSheet->setActiveSheetIndex($pIndex);
        $this->activeSheet = $this->spreadSheet->getActiveSheet();

        return $this;
    }

    /**
     * 设置sheet标题
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->activeSheet->setTitle($title);

        return $this;
    }


    /**
     * 写入一行
     * @param array $rowValue 行数据，一维数组
     *                               如：['值1', '值2']
     *                               or ['A1'=>'A1的值','B1'=>'B1的值']
     *                               or ['A1:B1'=>'A1的值','C1:D1'=>'C1的值']
     *                               or ['A'=>'A{$maxRow+1}的值','B'=>'B{$maxRow+1}的值']
     * @param integer|null $row 行号，未指定则为最大行+1
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function setRowValue(array $rowValue, $row = null)
    {
        if ($rowValue) {
            $defaultCol = 'A';
            if (!$row) {
                if ($this->currentRow) {
                    $row = $this->currentRow + 1;
                } else {
                    $coor = $this->activeSheet->getHighestRowAndColumn();
                    $row  = $coor['row'] == 1 && $coor['column'] == 'A' ? 1 : $coor['row'] + 1;
                }
            }
            foreach ($rowValue as $col => $val) {
                if ($this->isValidCoordinate($col)) {
                    $coordinate  = $col;
                    $pCoordinate = $col;
                    if (Coordinate::coordinateIsRange($col)) {
                        $this->activeSheet->mergeCells($col);
                        $pCoordinate = Coordinate::extractAllCellReferencesInRange($col)[0];
                    }
                } elseif ($this->isValidColumn($col)) {
                    $coordinate  = $col . $row;
                    $pCoordinate = $col . $row;
                } else {
                    $coordinate  = $defaultCol . $row;
                    $pCoordinate = $defaultCol . $row;
                    $defaultCol++;
                }
                // 内容区域加黑色边框
                $styleArray = [
                    'borders'   => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => $this->borderColor],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER, // 水平中心
                        'vertical'   => Alignment::VERTICAL_CENTER // 垂直中心
                    ],
                ];
                $this->activeSheet->getStyle($coordinate)->applyFromArray($styleArray);
                $this->activeSheet->setCellValue($pCoordinate, $val);
            }

            $this->currentRow = $row;
            $row % 100 == 0 && usleep(100);  // 释放cpu
        }

        return $this;
    }

    /**
     * 判断是否为有效的坐标
     * @param string $coordinate 如 A1、A2:A4
     * @return bool
     */
    private function isValidCoordinate($coordinate)
    {
        return preg_match_all('/^[A-Z]{1,3}[0-9]{1,7}(:[A-Z]{1,3}[0-9]{1,7})?$/', $coordinate) === 1;
    }

    /**
     * 判断是否为有效的列
     * @param string $coordinate 如 A、AA、ZZZ
     * @return bool
     */
    private function isValidColumn($column)
    {
        return preg_match_all('/^[A-Z]{1,3}$/', $column) === 1;
    }

    /**
     * 写入多行
     * @param array $rowValues 多行数据，二维数组
     * @param null|integer $start_row 开始行
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @see setRowValue()
     */
    public function setRowValues(array $rowValues, $start_row = null)
    {
        if ($rowValues) {
            if (!$start_row) {
                $coor      = $this->activeSheet->getHighestRowAndColumn();
                $start_row = $coor['row'] == 1 && $coor['column'] == 'A' ? 1 : $coor['row'] + 1;
            }
            foreach ($rowValues as $rv) {
                $this->setRowValue($rv, $start_row);
                $start_row += 1;
            }
        }

        return $this;
    }


    /**
     * 设置列宽度
     * @param null|string $columns 要设置的列
     * @param integer $width 宽度
     * @return $this
     */
    public function setWidth($width, $columns = null)
    {
        if (is_string($columns) && !empty($columns)) {
            if (strpos($columns, '-') !== false) {
                $columns = explode('-', $columns);
                $columns = $this->rangeColumn($columns[0], $columns[1]);
            } elseif (strpos($columns, ',') !== false) {
                $columns = explode(',', $columns);
            } else {
                $columns = [$columns];
            }
        } else {
            $columns = $this->rangeColumn('A', $this->activeSheet->getHighestColumn());
        }

        foreach ($columns as $column) {
            $this->activeSheet->getColumnDimension($column)->setWidth($width);
        }

        return $this;
    }


    /**
     * 取列区间的数组， 如A-Z
     * @param $start
     * @param $end
     * @return array
     */
    public function rangeColumn($start, $end)
    {
        $columns = [];

        for ($i = $start; $i !== $end; $i++) {
            $columns[] = $i;
        }
        $columns[] = $end;

        return $columns;
    }

    /**
     * 设置行高度
     * @param integer $hight 高度
     * @param null|string $rows 要设置的行, 如1-19 | 1,2,3
     * @return $this
     */
    public function setRowHeight($hight, $rows = null)
    {
        if (is_string($rows) && !empty($rows)) {
            if (strpos($rows, '-') !== false) {
                $rows = explode('-', $rows);
                $rows = range($rows[0], $rows[1]);
            } elseif (strpos($rows, ',') !== false) {
                $rows = explode(',', $rows);
            } else {
                $rows = [$rows];
            }
        } else {
            $rows = range(1, $this->activeSheet->getHighestRow());
        }

        foreach ($rows as $row) {
            $this->activeSheet->getRowDimension($row)->setRowHeight($hight);
        }

        return $this;
    }


    /**
     * 复制样式
     * @param array $styles 样式数组 {@see http://phpspreadsheet.readthedocs.io/en/develop/topics/recipes/#styles}
     * @param string $range 单元格区间，如 A1:B1
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function duplicateStyle(array $styles, $range = null)
    {
        if (!$range) {
            $maxRow = $this->activeSheet->getHighestRow();
            $maxCol = $this->activeSheet->getHighestColumn();
            $range  = "A1:{$maxCol}{$maxRow}";
        }

        $style = new Style();
        $style->applyFromArray($styles);
        $this->activeSheet->duplicateStyle($style, $range);

        return $this;
    }

    /**
     * 保存文件
     * @return null|string  写入的文件
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function save($baseDir = null, $filename = '', $isPath = true)
    {
        $this->setActiveSheet(0);
        $writer = new Xlsx($this->spreadSheet);

        // 保存文件
        if ($isPath) {
            $baseDir = $baseDir ?: root_path() . 'public/storage/';
            file_exists($baseDir) || mkdir($baseDir, 0777, true);
            $filepath = $filename == '' ? $baseDir . DIRECTORY_SEPARATOR . uniqid('xlsx_') . '.xlsx'
                : $baseDir . DIRECTORY_SEPARATOR . $filename . '.xlsx';
            $writer->save($filepath);
            return is_file($filepath) ? $filepath : null;
        } else {
            ob_end_clean();
            header('pragma:public');
            header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $filename . '.xlsx"');
            header("Content-Disposition:attachment;filename=$filename.xlsx"); //attachment新窗口打印inline本窗口打印
            $writer->save('php://output');
            return true;
        }
    }

    /**
     * 获取当前活动activeSheet
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    public function getActiveSheet()
    {
        return $this->activeSheet;
    }

    /**
     * 删除worksheet
     *
     * @param $pIndex
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function removeSheetByIndex($pIndex)
    {
        try {
            $this->spreadSheet->removeSheetByIndex($pIndex);
        } catch (\Exception $e) {
            Log::critical("删除worksheet失败{$e->getMessage()}");
        }
    }

    /* Eof */
}
