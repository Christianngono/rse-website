<?php
require_once ('fpdf.php');

class PDF_Diag extends PDF
{
    function BarDiagram($w, $h, $data, $format = '%', $color = [100, 150, 255], $maxVal = 0, $nbDiv = 4)
    {
        $X = $this->GetX();
        $Y = $this->GetY();
        $this->SetLineWidth(0.2);
        $this->Rect($X, $Y, $w, $h);

        if ($maxVal == 0) {
            $maxVal = max($data) * 1.1;
        }

        $valIndRepere = $maxVal / $nbDiv;
        $hRepere = $h / $nbDiv;
        $unit = $h / $maxVal;
        $barWidth = $w / (count($data) + 1);
        $this->SetFont('Arial', '', 9);

        for ($i = 0; $i <= $nbDiv; $i++) {
            $y = $Y + $h - $i * $hRepere;
            $this->Line($X, $y, $X + $w, $y);
            $val = $i * $valIndRepere;
            $this->SetXY($X - 10, $y - 3);
            $this->Cell(8, 5, number_format($val), 0, 0, 'R');
        }

        $this->SetFillColor($color[0], $color[1], $color[2]);
        $i = 0;
        foreach ($data as $label => $val) {
            $xval = $X + ($i + 1) * $barWidth;
            $yval = $Y + $h - ($val * $unit);
            $this->Rect($xval, $yval, $barWidth * 0.6, $val * $unit, 'DF');
            $this->SetXY($xval, $Y + $h + 2);
            $this->Cell($barWidth * 0.6, 5, $label, 0, 0, 'C');
            $this->SetXY($xval, $yval - 5);
            $this->Cell($barWidth * 0.6, 5, sprintf("%.1f%s", $val, $format), 0, 0, 'C');
            $i++;
        }

        $this->SetXY($X, $Y + $h + 15);
    }
}