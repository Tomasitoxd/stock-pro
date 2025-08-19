<?php
require 'db.php';
require 'fpdf.php';

// --- función limpia fecha ---
function clean_date($s) {
  $s = substr(trim($s ?? ''), 0, 10);
  return preg_match('/^\d{4}-\d{2}-\d{2}$/', $s) ? $s : null;
}

$fecha = clean_date($_GET['fecha'] ?? null);
$desde = clean_date($_GET['desde'] ?? null);
$hasta = clean_date($_GET['hasta'] ?? null);
$modo_rango = $desde && $hasta;
if (!$modo_rango) $fecha = $fecha ?: date('Y-m-d');
$tituloPeriodo = $modo_rango ? "$desde al $hasta" : $fecha;

// --- CONSULTAS ---
if ($modo_rango) {
  $sql_pagos = "SELECT id, nombre_cliente, concepto, monto, DATE(fecha) AS f FROM pagos 
                WHERE DATE(fecha) BETWEEN ? AND ? AND anulado=0 ORDER BY f, hora, id";
  $stmt = $conexion->prepare($sql_pagos);
  $stmt->bind_param("ss",$desde,$hasta);
} else {
  $sql_pagos = "SELECT id, nombre_cliente, concepto, monto, DATE(fecha) AS f FROM pagos 
                WHERE DATE(fecha)=? AND anulado=0 ORDER BY hora, id";
  $stmt = $conexion->prepare($sql_pagos);
  $stmt->bind_param("s",$fecha);
}
$stmt->execute();
$res = $stmt->get_result();
$lista_pagos=[]; $total_pagos=0;
while($r=$res->fetch_assoc()){ $lista_pagos[]=$r; $total_pagos+=floatval($r['monto']); }

if ($modo_rango) {
  $sql_gastos = "SELECT id, concepto, monto, DATE(fecha) AS f FROM gastos 
                 WHERE DATE(fecha) BETWEEN ? AND ? ORDER BY f, hora, id";
  $stmt = $conexion->prepare($sql_gastos);
  $stmt->bind_param("ss",$desde,$hasta);
} else {
  $sql_gastos = "SELECT id, concepto, monto, DATE(fecha) AS f FROM gastos 
                 WHERE DATE(fecha)=? ORDER BY hora, id";
  $stmt = $conexion->prepare($sql_gastos);
  $stmt->bind_param("s",$fecha);
}
$stmt->execute();
$res = $stmt->get_result();
$lista_gastos=[]; $total_gastos=0;
while($r=$res->fetch_assoc()){ $lista_gastos[]=$r; $total_gastos+=floatval($r['monto']); }

$balance = $total_pagos - $total_gastos;

// --- PDF ---
class PDF extends FPDF {
    function Header() {
        // Logo
        if(file_exists('logo.png')){
            $this->Image('logo.png',10,8,25); // x,y,ancho
        }
        // Título
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,utf8_decode('Balance de Librería Azul'),0,1,'C');
        $this->Ln(5);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Página '.$this->PageNo().'/{nb}',0,0,'C');
    }
}
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true,15);
$pdf->AddPage();

// Subtítulo con el período
$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(60,60,60);
$pdf->Cell(0,10,'Periodo: '.$tituloPeriodo,0,1,'C');
$pdf->Ln(5);

// ====== ESTILOS ======
$colorHeader = [200,220,255]; // azul pastel
$colorTotal  = [220,240,200]; // verde pastel
$colorSaldo  = [255,230,200]; // naranja pastel

// ---- Función tabla ----
function renderTable($pdf,$headers,$rows,$widths,$total,$labelTotal,$colorHeader,$colorTotal){
    $pdf->SetFont('Arial','B',11);
    $pdf->SetFillColor($colorHeader[0],$colorHeader[1],$colorHeader[2]);
    foreach($headers as $i=>$h){
        $pdf->Cell($widths[$i],8,utf8_decode($h),1,0,'C',true);
    }
    $pdf->Ln();

    $pdf->SetFont('Arial','',10);
    $pdf->SetFillColor(245,245,245);
    $fill=false;
    foreach($rows as $r){
        foreach(array_values($r) as $i=>$col){
            $align = is_numeric(str_replace(['$','.',','],'',$col)) ? 'R' : 'L';
            $pdf->Cell($widths[$i],8,utf8_decode($col),1,0,$align,$fill);
        }
        $pdf->Ln();
        $fill=!$fill;
    }
    // total
    $pdf->SetFont('Arial','B',11);
    $pdf->SetFillColor($colorTotal[0],$colorTotal[1],$colorTotal[2]);
    $pdf->Cell(array_sum($widths)-$widths[count($widths)-1],8,$labelTotal,1,0,'R',true);
    $pdf->Cell($widths[count($widths)-1],8,'$'.number_format($total,2),1,0,'R',true);
    $pdf->Ln(12);
}

// ---- PAGOS ----
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Pagos',0,1);
$rows=[];
foreach($lista_pagos as $p){
    $rows[] = [$p['id'],$p['f'],$p['nombre_cliente'],$p['concepto'],'$'.number_format($p['monto'],2)];
}
renderTable($pdf,
    ['ID','Fecha','Cliente','Concepto','Monto'],
    $rows,[15,28,45,70,30],$total_pagos,'TOTAL PAGOS',$colorHeader,$colorTotal);

// ---- GASTOS ----
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Gastos',0,1);
$rows=[];
foreach($lista_gastos as $g){
    $rows[] = [$g['id'],$g['f'],$g['concepto'],'$'.number_format($g['monto'],2)];
}
renderTable($pdf,
    ['ID','Fecha','Concepto','Monto'],
    $rows,[15,28,115,30],$total_gastos,'TOTAL GASTOS',$colorHeader,$colorTotal);

// ---- BALANCE FINAL ----
$pdf->SetFont('Arial','B',14);
$pdf->SetFillColor($colorSaldo[0],$colorSaldo[1],$colorSaldo[2]);
$pdf->Cell(158,10,'BALANCE FINAL',1,0,'R',true);
$pdf->Cell(30,10,'$'.number_format($balance,2),1,0,'R',true);

$filename = $modo_rango ? "Balance_{$desde}_a_{$hasta}.pdf" : "Balance_{$fecha}.pdf";
$pdf->Output('D',$filename);
