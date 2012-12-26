<?php

Class Controller_Nencho extends Controller
{
	public function action_index()
	{
		$dataRoot = Data::getData();

		// 準備
		Package::load('pdf');
		$pdf = Pdf::factory('tcpdf')->init('L');

		$pdf->setSourceFile('C:\work\fuelphp\fuel\app\classes\controller\nencho_template.pdf');
		$pdf->SetDrawColor(0,0,0);

		// 位置調整用
		$a = 10;
		$b = 10;
		$c = 10;

		$x = 0;
		$y = 0;

		for ($j = 0; $j < count($dataRoot); $j++) {

			if ($j > 0)
			{
				//continue;
			}
			
			$data  = $dataRoot[$j];

			// 1ページ目
			$pdf->AddPage();
			$index = $pdf->importPage(1);
			$pdf->useTemplate($index);

			// 郵便番号
			$pdf->SetFont('Helvetica','N',8);
			$pdf->SetXY($x+120,$y+7.5);
			$pdf->Cell($a,$b,$data['郵便番号'],0,0,'L',0);

			// 住所
			$pdf->SetFont('kozgopromedium','N',8);
			$pdf->SetXY($x+110,$y+12.5);
			$pdf->Cell($a,$b,$data['住所'],0,0,'L',0);

			// 氏名
			$pdf->SetFont('kozgopromedium','N',10);
			$pdf->SetXY($x+200,$y+11.1);
			$pdf->Cell($a,$b,$data['氏名'],0,0,'L',0);

			// フリガナ
			$pdf->SetFont('kozgopromedium','N',8);
			$pdf->SetXY($x+210,$y+7.5);
			$pdf->Cell($a,$b,$data['フリガナ'],0,0,'L',0);

			// 和暦
			$pdf->SetFont('kozgopromedium','N',8);
			if ($data['和暦'] === '昭和')
			{
				$pdf->SetXY($x+218.4,$y+14.5);
			}
			else
			{
				$pdf->SetXY($x+221.5,$y+14.5);
			}
			$pdf->Cell($a,$b,'○',0,0,'L',0);

			// 生年月日
			$birthday = preg_split('[-]',$data['生年月日']);
			$pdf->SetFont('Helvetica','N',8);
			$pdf->SetXY($x+220,$y+14.8);
			$pdf->Cell($a,$b,$birthday[0],0,0,'R',0);
			$pdf->SetXY($x+227,$y+14.8);
			$pdf->Cell($a,$b,$birthday[1],0,0,'R',0);
			$pdf->SetXY($x+234,$y+14.8);
			$pdf->Cell($a,$b,$birthday[2],0,0,'R',0);

			// ①総支給金額合計
			$calc1 = 0;
			// ②社会保険料等の控除額合計
			$calc2 = 0;
			// 社会保険料等控除後の給与等の金額合計
			$calcX1 = 0;
			// ③算出税額合計
			$calc3 = 0;

			// 給与明細
			for ($i = 0; $i < count($data['給与明細']); $i++) {

				$kyuyo = $data['給与明細'][$i];

				$pdf->SetFont('Helvetica','N',9);

				if ($kyuyo['支給日'] === '        ')
				{
					// 支給月
					$pdf->SetFont('kozgopromedium','N',8);
					$pdf->SetXY($x+43,$y+31.6+($i*10.1));
					$pdf->Cell($a,$b,'前職分',0,0,'L',0);
					$pdf->SetFont('Helvetica','N',9);
				}
				else
				{
					// 支給月
					$pdf->SetXY($x+38,$y+31.6+($i*10.1));
					$pdf->Cell($a,$b,substr($kyuyo['支給日'],4,2),0,0,'R',0);
					
					// 支給日
					$pdf->SetXY($x+43.5,$y+31.6+($i*10.1));
					$pdf->Cell($a,$b,substr($kyuyo['支給日'],6,2),0,0,'R',0);					
				}

			    // 総支給金額
			    $pdf->SetXY($x+62,$y+31.6+($i*10.1));
			    $pdf->Cell($a,$b,number_format($kyuyo['総支給額']-$kyuyo['交通費合計']-$kyuyo['支給_非課税']),0,0,'R',0);
			    $calc1 += $kyuyo['総支給額']-$kyuyo['交通費合計']-$kyuyo['支給_非課税'];
			    
			    // 社会保険料等の控除額
			    $pdf->SetXY($x+78,$y+31.6+($i*10.1));
			    $pdf->Cell($a,$b,number_format($kyuyo['社会保険料額合計']),0,0,'R',0);
			    $calc2 += $kyuyo['社会保険料額合計'];

			    // 社会保険料等控除後の給与等の金額
			    $pdf->SetXY($x+99,$y+31.6+($i*10.1));
			    $pdf->Cell($a,$b,number_format($kyuyo['総支給額']-$kyuyo['交通費合計']-$kyuyo['支給_非課税']-$kyuyo['社会保険料額合計']),0,0,'R',0);
			    $calcX1 += $kyuyo['総支給額']-$kyuyo['交通費合計']-$kyuyo['支給_非課税']-$kyuyo['社会保険料額合計'];

			    // 扶養親族等の数
			    $pdf->SetXY($x+108,$y+31.6+($i*10.1));
			    $pdf->Cell($a,$b,$data['扶養親族等の数'],0,0,'R',0);

			    // 算出税額
			    $pdf->SetXY($x+125,$y+31.6+($i*10.1));
			    $pdf->Cell($a,$b,number_format($kyuyo['源泉徴収税額']),0,0,'R',0);
			    $calc3 += $kyuyo['源泉徴収税額'];
			    
			}

			$pdf->SetFont('Helvetica','N',9);
			
			// ①総支給金額合計
			$pdf->SetXY($x+64,$y+154);
			$pdf->Cell($a,$b,number_format($calc1),0,0,'R',0);
			// ①（右欄）			
			$pdf->SetXY($x+235,$y+68.5);
			$pdf->Cell($a,$b,number_format($calc1),0,0,'R',0);
			// ⑦
			$pdf->SetXY($x+235,$y+79.5);
			$pdf->Cell($a,$b,number_format($calc1),0,0,'R',0);
			
			// ②社会保険料等の控除額合計
			$pdf->SetXY($x+81.3,$y+154);
			$pdf->Cell($a,$b,number_format($calc2),0,0,'R',0);
			// ⑩給与等からの控除分	
			$pdf->SetXY($x+235,$y+91.5);
			$pdf->Cell($a,$b,number_format($calc2),0,0,'R',0);
			
			// 社会保険料等の控除額合計
			$pdf->SetXY($x+101,$y+154);
			$pdf->Cell($a,$b,number_format($calcX1),0,0,'R',0);
			
			// ③算出税額合計
			$pdf->SetXY($x+126.5,$y+154);
			$pdf->Cell($a,$b,number_format($calc3),0,0,'R',0);
			// ③（右欄）
			$pdf->SetXY($x+270,$y+68.5);
			$pdf->Cell($a,$b,number_format($calc3),0,0,'R',0);
			
			// ⑧税額計
			$calc8 = $calc3;
			$pdf->SetXY($x+270,$y+79.5);
			$pdf->Cell($a,$b,number_format($calc8),0,0,'R',0);
			
			// ⑨給与所得控除後の給与等の金額
			$calcX9 = 0;
			if ($calc1 <= 1618999)
			{
				$calcX9 = $calc1;
			}
			else if (1619000 <= $calc1 && $calc1 <= 1619999)
			{
				$calcX9 = $calc1 - (($calc1 - 1619000) % 1000);
			}
			else if (1620000 <= $calc1 && $calc1 <= 1623999)
			{
				$calcX9 = $calc1 - (($calc1 - 1620000) % 2000);
			}
			else if (1624000 <= $calc1 && $calc1 <= 6599999)
			{
				$calcX9 = $calc1 - (($calc1 - 1624000) % 4000);
			}
			else
			{
				$calcX9 = $calc1;				
			}

			$calc9 = 0;
			if ($calc1 <= 650999)
			{
				$calc9 = 0;
			}
			else if (651000 <= $calcX9 && $calcX9 <= 1618999)
			{
				$calc9 = $calcX9 - 650000;
			}
			else if (1619000 <= $calcX9 && $calcX9 <= 1619999)
			{
				$calc9 = $calcX9 * 0.60 - 2400;
			}
			else if (1620000 <= $calcX9 && $calcX9 <= 1621999)
			{
				$calc9 = $calcX9 * 0.60 - 2000;
			}
			else if (1622000 <= $calcX9 && $calcX9 <= 1623999)
			{
				$calc9 = $calcX9 * 0.60 - 1200;
			}
			else if (1624000 <= $calcX9 && $calcX9 <= 1627999)
			{
				$calc9 = $calcX9 * 0.60 - 400;
			}
			else if (1628000 <= $calcX9 && $calcX9 <= 1799999)
			{
				$calc9 = $calcX9 * 0.60;
			}
			else if (1800000 <= $calcX9 && $calcX9 <= 3599999)
			{
				$calc9 = $calcX9 * 0.70 - 180000;
			}
			else if (3600000 <= $calcX9 && $calcX9 <= 6599999)
			{
				$calc9 = $calcX9 * 0.80 - 540000;
			}
			else if (6600000 <= $calcX9 && $calcX9 <= 9999999)
			{
				$calc9 = $calcX9 * 0.90 - 1200000;
			}
			else if (10000000 <= $calcX9 && $calcX9 <= 20000000)
			{
				$calc9 = $calcX9 * 0.95 - 1700000;
			}
			else
			{
				$calc9 = 0;
			}
			// ⑨給与所得控除後の給与等の金額
			$pdf->SetXY($x+235,$y+85.5);
			$pdf->Cell($a,$b,number_format($calc9),0,0,'R',0);

			// ⑪申告による社会保険料の控除分
			$calc11 = $data['社会保険料控除'];
			if ($calc11 > 0)
			{
				$pdf->SetXY($x+235,$y+97);
				$pdf->Cell($a,$b,number_format($calc11),0,0,'R',0);				
			}
						
			// 扶養控除等の申告の有無
			$pdf->SetFont('kozgopromedium','N',13);
			$pdf->SetXY($x+168.5,$y+50);
			$pdf->Cell($a,$b,'○',0,0,'R',0);
			$pdf->SetFont('Helvetica','N',9);
			
			// ⑯配偶者控除額、扶養控除額、基礎控除額及び障害者等の控除額の合計額
			// ※扶養控除、老人扶養親族控除、勤労学生控除のみ対応
			$calc16 = 380000 * $data['扶養親族等の数'] + 380000;
			if ($data['内老人扶養親族'] > '0')
			{
				$calc16 += 100000 * $data['内老人扶養親族']; 
				$pdf->SetXY($x+220,$y+44);
				$pdf->Cell($a,$b,$data['内老人扶養親族'],0,0,'R',0);
			}
			if ($data['勤労学生'] === '1')
			{
				$calc16 += 270000;
				$pdf->SetFont('kozgopromedium','N',8);
				$pdf->SetXY($x+237.2,$y+58.5);
				$pdf->Cell($a,$b,'○',0,0,'R',0);
				$pdf->SetFont('Helvetica','N',9);
				$pdf->SetXY($x+230,$y+44);
				$pdf->Cell($a,$b,$data['勤労学生'],0,0,'R',0);
			}
			$pdf->SetXY($x+235,$y+125.3);
			$pdf->Cell($a,$b,number_format($calc16),0,0,'R',0);

			// ⑰所得控除後の合計金額
			$calc17 = $calc2 + $calc16 + $calc11;
			$pdf->SetXY($x+235,$y+131);
			$pdf->Cell($a,$b,number_format($calc17),0,0,'R',0);
			
			// ⑱差引課税給与所得金額
			$calc18 = ($calc9 - $calc17) - (($calc9 - $calc17) % 1000);
			if ($calc18 < 0)
			{
				$calc18 = 0;
			}
			$pdf->SetXY($x+235,$y+138.5);
			$pdf->Cell($a,$b,number_format($calc18),0,0,'R',0);

			// ⑲算出年税額
			$calc19 = 0;
			if ($calc18 <= 1950000)
			{
				$calc19 = $calc18 * 0.05;
			}
			else if (1950000 < $calc18 && $calc18 <= 3300000)
			{
				$calc19 = $calc18 * 0.10 - 97500;
			}
			else if (3300000 < $calc18 && $calc18 <= 6950000)
			{
				$calc19 = $calc18 * 0.20 - 427500;			
			}
			else if (6950000 < $calc18 && $calc18 <= 9000000)
			{
				$calc19 = $calc18 * 0.23 - 636000;
			}
			else if (9000000 < $calc18 && $calc18 <= 16920000)
			{
				$calc19 = $calc18 * 0.33 - 1536000;
			}
			else
			{
				$calc19 = 0;
			}
			$pdf->SetXY($x+270,$y+138.5);
			$pdf->Cell($a,$b,number_format($calc19),0,0,'R',0);

			// (21)年調年税額
			$calc21 = $calc19;
			$pdf->SetXY($x+270,$y+150.5);
			$pdf->Cell($a,$b,number_format($calc21),0,0,'R',0);

			// (22)差引超過額又は不足額
			$calc22 = $calc21 - $calc8;
			if ($calc22 < 0)
			{
				$calc22 = $calc22 * -1;
				$pdf->SetFont('kozgopromedium','N',8);
				$pdf->SetXY($x+255,$y+155.5);
				$pdf->Cell($a,$b,'(超過)',0,0,'R',0);
				$pdf->SetFont('Helvetica','N',9);
			}
			elseif ($calc22 > 0)
			{
				$pdf->SetFont('kozgopromedium','N',8);
				$pdf->SetXY($x+255,$y+155.5);
				$pdf->Cell($a,$b,'(不足)',0,0,'R',0);
				$pdf->SetFont('Helvetica','N',9);				
			}
			$pdf->SetXY($x+270,$y+155.5);
			$pdf->Cell($a,$b,number_format($calc22),0,0,'R',0);

			// (25)差引還付する金額
			$calc25 = $calc22;
			$pdf->SetXY($x+270,$y+171.5);
			$pdf->Cell($a,$b,number_format($calc25),0,0,'R',0);
			
			// (26)本年中に還付する金額
			$calc26 = $calc22;
			$pdf->SetXY($x+270,$y+177);
			$pdf->Cell($a,$b,number_format($calc26),0,0,'R',0);
			
			//---------------------------------------------------------------------------
			
			// 2ページ目
			$pdf->AddPage();
			$index = $pdf->importPage(2);
			$pdf->useTemplate($index);

			// 手当ヘッダー
			$pdf->SetFont('kozgopromedium','N',7);
			$pdf->SetXY($x+76,$y+10.6);
			$pdf->Cell($a,$b,'調整',0,0,'R',0);
			$pdf->SetXY($x+91,$y+10.6);
			$pdf->Cell($a,$b,'深夜',0,0,'R',0);
			$pdf->SetXY($x+106,$y+10.6);
			$pdf->Cell($a,$b,'時間外',0,0,'R',0);
			$pdf->SetXY($x+121,$y+10.6);
			$pdf->Cell($a,$b,'諸',0,0,'R',0);

			// 給与明細
			for ($i = 0; $i < count($data['給与明細']); $i++) {

				$kyuyo = $data['給与明細'][$i];

			    $pdf->SetFont('Helvetica','N',9);

			    // 月区分
			    $pdf->SetXY($x+21.5,$y+20+($i*5));
			    $pdf->Cell($a,$b,$i+1,0,0,'R',0);

			    if ($kyuyo['支給日'] === '        ')
			    {
			    	// 支給月
			    	$pdf->SetFont('kozgopromedium','N',8);
			    	$pdf->SetXY($x+33.6,$y+20+($i*5));
			    	$pdf->Cell($a,$b,'前職分',0,0,'L',0);
			    	$pdf->SetFont('Helvetica','N',9); }
			    else
			    {
			    	// 支給月
			    	$pdf->SetXY($x+29,$y+20+($i*5));
			    	$pdf->Cell($a,$b,substr($kyuyo['支給日'],4,2),0,0,'R',0);
			    	
			    	// 支給日
			    	$pdf->SetXY($x+33.8,$y+20+($i*5));
			    	$pdf->Cell($a,$b,substr($kyuyo['支給日'],6,2),0,0,'R',0);
			    }

			    // 基本給
			    $pdf->SetXY($x+50,$y+20+($i*5));
			    $pdf->Cell($a,$b,number_format($kyuyo['基本給']),0,0,'R',0);

			    // 家族手当
			    //$pdf->SetXY($x+65,$y+20+($i*5));
			    //$pdf->Cell($a,$b,'99999',0,0,'R',0);

			    // 調整手当
			    $pdf->SetXY($x+80,$y+20+($i*5));
			    $pdf->Cell($a,$b,number_format($kyuyo['調整手当']),0,0,'R',0);

			    // 深夜手当
			    $pdf->SetXY($x+95,$y+20+($i*5));
			    $pdf->Cell($a,$b,number_format($kyuyo['深夜手当']),0,0,'R',0);

			    // 時間外手当
			    $pdf->SetXY($x+110,$y+20+($i*5));
			    $pdf->Cell($a,$b,number_format($kyuyo['時間外手当']),0,0,'R',0);

			    // お誕生日手当
			    $pdf->SetXY($x+125,$y+20+($i*5));
			    $pdf->Cell($a,$b,number_format($kyuyo['お誕生日手当']),0,0,'R',0);

			    // 総支給金額
			    $pdf->SetXY($x+144,$y+20+($i*5));
			    $pdf->Cell($a,$b,number_format($kyuyo['総支給額']-$kyuyo['交通費合計']-$kyuyo['支給_非課税']),0,0,'R',0);

			}
		}

		// 出力
		$pdf->Output('nencho.pdf', 'I');
	}
}