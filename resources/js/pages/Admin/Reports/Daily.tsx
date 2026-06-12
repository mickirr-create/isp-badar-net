import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Calendar } from 'lucide-react';

interface Transaction {
    id: number;
    invoice: string;
    username: string;
    plan_name: string;
    price: number;
    method: string;
    recharged_on: string;
}

interface DailyProps {
    transactions: Transaction[];
    totalIncome: number;
    filters: { date_from: string; date_to: string };
}

export default function Daily({ transactions, totalIncome, filters }: DailyProps) {
    return (
        <>
            <Head title="Laporan Harian" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Calendar className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Laporan Harian</h1>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            {filters.date_from === filters.date_to
                                ? `Tanggal: ${filters.date_from}`
                                : `${filters.date_from} s/d ${filters.date_to}`}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4">
                            <p className="text-lg font-semibold">
                                Total Pendapatan: Rp {totalIncome.toLocaleString('id-ID')}
                            </p>
                            <p className="text-sm text-muted-foreground">
                                {transactions.length} transaksi
                            </p>
                        </div>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Invoice</TableHead>
                                    <TableHead>Pelanggan</TableHead>
                                    <TableHead>Paket</TableHead>
                                    <TableHead className="text-right">Harga</TableHead>
                                    <TableHead>Metode</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {transactions.map((tx) => (
                                    <TableRow key={tx.id}>
                                        <TableCell className="font-mono text-sm">{tx.invoice}</TableCell>
                                        <TableCell>{tx.username}</TableCell>
                                        <TableCell>{tx.plan_name}</TableCell>
                                        <TableCell className="text-right">Rp {tx.price.toLocaleString('id-ID')}</TableCell>
                                        <TableCell className="capitalize">{tx.method}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        {transactions.length === 0 && (
                            <p className="text-center text-muted-foreground py-8">Tidak ada transaksi pada periode ini</p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
