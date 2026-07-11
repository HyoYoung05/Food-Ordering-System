<?php
declare(strict_types=1);

final class ReceiptPdf
{
    public static function make(array $order): string
    {
        $lines=[['SAVORLY KITCHEN',18],['ORDER RECEIPT',14],["Receipt #{$order['id']}",10],[date('F j, Y g:i A',(int)($order['createdAt']/1000)).' Philippine Time',10],['',10],["Customer: {$order['customer']}",10],["Email: {$order['email']}",10],["Delivery: {$order['address']}",10],["Payment: {$order['payment']}",10],['',10],['ORDER DETAILS',12]];
        foreach($order['items'] as $item)$lines[]=[sprintf('%d x %s     PHP %.2f',$item['qty'],$item['name'],$item['price']*$item['qty']),10];
        $lines=array_merge($lines,[['',10],[sprintf('Subtotal: PHP %.2f',$order['subtotal']),10],[sprintf('Delivery: PHP %.2f',$order['delivery']),10],[sprintf('TOTAL: PHP %.2f',$order['total']),13],['',10],['Thank you for ordering with Savorly!',10]]);
        $y=790;$stream='';foreach($lines as [$text,$size]){$text=self::escape($text);$stream.="BT /F1 {$size} Tf 55 {$y} Td ({$text}) Tj ET\n";$y-=$size+9;}
        $objects=[null,'<< /Type /Catalog /Pages 2 0 R >>','<< /Type /Pages /Kids [3 0 R] /Count 1 >>','<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',"<< /Length ".strlen($stream)." >>\nstream\n{$stream}endstream",'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
        $pdf="%PDF-1.4\n";$offsets=[0];for($i=1;$i<count($objects);$i++){$offsets[$i]=strlen($pdf);$pdf.="{$i} 0 obj\n{$objects[$i]}\nendobj\n";}$xref=strlen($pdf);$pdf.="xref\n0 ".count($objects)."\n0000000000 65535 f \n";for($i=1;$i<count($objects);$i++)$pdf.=str_pad((string)$offsets[$i],10,'0',STR_PAD_LEFT)." 00000 n \n";return $pdf."trailer << /Size ".count($objects)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private static function escape(string $value): string
    {
        $value=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value)?:'';return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$value);
    }
}
