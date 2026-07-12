<?php
declare(strict_types=1);

final class ReceiptPdf
{
    public static function make(array $order): string
    {
        $stream='';$y=790;
        $text=function(string $value,int $size,int $x,int $lineY) use (&$stream): void {$stream.='BT /F1 '.$size.' Tf '.$x.' '.$lineY.' Td ('.self::escape($value).") Tj ET\n";};
        $line=function(int $x1,int $y1,int $x2,int $y2) use (&$stream): void {$stream.="0.82 0.84 0.80 RG 0.7 w {$x1} {$y1} m {$x2} {$y2} l S\n";};
        $text('SAVORLY KITCHEN',18,55,$y);$y-=26;
        $text('ORDER RECEIPT',14,55,$y);$y-=22;
        $text("Receipt #{$order['id']}",10,55,$y);$y-=17;
        $text(date('F j, Y g:i A',(int)($order['createdAt']/1000)).' Philippine Time',10,55,$y);$y-=26;
        foreach(["Customer: {$order['customer']}","Email: {$order['email']}","Delivery: {$order['address']}","Payment: {$order['payment']}"] as $row){$text($row,10,55,$y);$y-=17;}
        $y-=8;$text('ORDERED PRODUCTS',12,55,$y);$y-=18;
        $left=55;$top=$y;$rowHeight=24;$cols=[55,285,350,435,535];$headers=['Product','Qty','Unit Price','Line Total'];
        $line($left,$top,$cols[4],$top);$line($left,$top-$rowHeight,$cols[4],$top-$rowHeight);
        foreach($cols as $x)$line($x,$top,$x,$top-$rowHeight);
        $text($headers[0],9,$cols[0]+7,$top-16);$text($headers[1],9,$cols[1]+7,$top-16);$text($headers[2],9,$cols[2]+7,$top-16);$text($headers[3],9,$cols[3]+7,$top-16);
        $y=$top-$rowHeight;
        foreach($order['items'] as $item){
            $next=$y-$rowHeight;
            $name=self::shorten((string)$item['name'],38);$qty=(string)(int)$item['qty'];$unit=sprintf('PHP %.2f',(float)$item['price']);$lineTotal=sprintf('PHP %.2f',(float)$item['price']*(int)$item['qty']);
            $line($left,$next,$cols[4],$next);foreach($cols as $x)$line($x,$y,$x,$next);
            $text($name,9,$cols[0]+7,$y-16);$text($qty,9,$cols[1]+7,$y-16);$text($unit,9,$cols[2]+7,$y-16);$text($lineTotal,9,$cols[3]+7,$y-16);
            $y=$next;
        }
        $y-=24;
        $text(sprintf('Subtotal: PHP %.2f',$order['subtotal']),10,360,$y);$y-=17;
        $text(sprintf('Delivery: PHP %.2f',$order['delivery']),10,360,$y);$y-=20;
        $text(sprintf('TOTAL: PHP %.2f',$order['total']),13,360,$y);$y-=34;
        $text('Thank you for ordering with Savorly!',10,55,$y);
        $objects=[null,'<< /Type /Catalog /Pages 2 0 R >>','<< /Type /Pages /Kids [3 0 R] /Count 1 >>','<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',"<< /Length ".strlen($stream)." >>\nstream\n{$stream}endstream",'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
        $pdf="%PDF-1.4\n";$offsets=[0];for($i=1;$i<count($objects);$i++){$offsets[$i]=strlen($pdf);$pdf.="{$i} 0 obj\n{$objects[$i]}\nendobj\n";}$xref=strlen($pdf);$pdf.="xref\n0 ".count($objects)."\n0000000000 65535 f \n";for($i=1;$i<count($objects);$i++)$pdf.=str_pad((string)$offsets[$i],10,'0',STR_PAD_LEFT)." 00000 n \n";return $pdf."trailer << /Size ".count($objects)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private static function escape(string $value): string
    {
        $value=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value)?:'';return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$value);
    }

    private static function shorten(string $value,int $max): string
    {
        $value=trim($value);
        return strlen($value)>$max?substr($value,0,$max-3).'...':$value;
    }
}
