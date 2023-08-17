<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;

class OrderExport implements WithEvents, WithColumnWidths
{
    public function registerEvents(): array {
        
        return [
            AfterSheet::class => function(AfterSheet $event) {
                /** @var Sheet $sheet */

                $sheet = $event->sheet;
                
                $sheet->setCellValue("A1", "No.");
                $sheet->setCellValue("B1", "Nama Customer");
                $sheet->setCellValue("C1", "No Hp");
                $sheet->setCellValue("D1", "Alamat");
                
                $sheet->setCellValue("E1", "Tanggal Order");
                $sheet->setCellValue("F1", "Kode Order");
                $sheet->setCellValue("G1", "Urutan Order");
                $sheet->setCellValue("H1", "Status");

                $sheet->setCellValue("I1", "Nama Produk");
                $sheet->setCellValue("J1", "Qty");
                $sheet->setCellValue("K1", "Harga");
                $sheet->setCellValue("L1", "Total");
                
                $orders = Order::with('customer', 'order_details')->orderBy('sequence')->get();

                $row = 2;
                foreach($orders as $idx => $order){
                    $sheet->setCellValue("A{$row}", $idx + 1 . ".");
                    $sheet->setCellValue("B{$row}", $order->customer->user->name);
                    $sheet->setCellValue("C{$row}", $order->customer->phone);
                    $sheet->setCellValue("D{$row}", $order->customer->address);

                    $sheet->setCellValue("E{$row}", $order->created_at);
                    $sheet->setCellValue("F{$row}", $order->code);
                    $sheet->setCellValue("G{$row}", $order->sequence);
                    $sheet->setCellValue("H{$row}", $order->is_complete ? 'Selesai' : 'Belum Selesai');

                    foreach($order->order_details as $order_detail){
                        $sheet->setCellValue("I{$row}", $order_detail->product->name);
                        $sheet->setCellValue("J{$row}", $order_detail->qty);
                        $sheet->setCellValue("K{$row}", $order_detail->price);
                        $sheet->setCellValue("L{$row}", $order_detail->qty * $order_detail->price);
                        $row++;
                    }
                }


                $styleArray = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ];

                $cellRange = 'A1:L1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray($styleArray);
            },
        ];
    }

    public function columnWidths(): array
    {
       $list_width = [
            'A' => 5, 'B' => 20, 'C' => 20, 'D' => 20, 
            'E' => 20, 'F' => 20, 'G' => 20, 'H' => 20, 
            'I' => 20, 'J' => 15, 'K' => 15, 'L' => 15,
        ];

        return $list_width;
    }
}
