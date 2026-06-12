import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { BarChart3 } from 'lucide-react';

interface Transaction {
    id: number;
    invoice: string;
    username: string;
    plan_name: string;
    price: number;
    method: string;
    type: string;
    recharged_on: string;
}

interface ReportsProps {
    transactions: {
        data: Transaction[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: { totalIncome: number; totalCount: number };
    filters: Record<string, string>;
}

export default function Reports({ transactions, stats, filters }: ReportsProps) {
    const handleFilter = (key: string, value: string) => {
        router.get(route('admin.reports.index'), { ...filters, [key]: value }, { preserveState: true });
    };

    return (
        <>
            <Head title="Laporan Transaksi" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <BarChart3 className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Laporan Transaksi</h1>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm text-muted-foreground">Total Pendapatan</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-bold">Rp {stats.totalIncome.toLocaleString('id-ID')}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm text-muted-foreground">Total Transaksi</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p className="text-2xl font-bold">{stats.totalCount}</p>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Filter</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-wrap gap-4">
                            <div className="flex items-center gap-2">
                                <label className="text-sm">Dari:</label>
                                <Input
                                    type="date"
                                    defaultValue={filters.date_from || ''}
                                    onChange={(e) => handleFilter('date_from', e.target.value)}
                                    className="w-40"
                                />
                            </div>
                            <div className="flex items-center gap-2">
                                <label className="text-sm">Sampai:</label>
                                <Input
                                    type="date"
                                    defaultValue={filters.date_to || ''}
                                    onChange={(e) => handleFilter('date_to', e.target.value)}
                                    className="w-40"
                                />
                            </div>
                            <div className="flex items-center gap-2">
                                <label className="text-sm">Metode:</label>
                                <select
                                    value={filters.method || ''}
                                    onChange={(e) => handleFilter('method', e.target.value)}
                                    className="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                >
                                    <option value="">Semua</option>
                                    <option value="manual">Manual</option>
                                    <option value="midtrans">Midtrans</option>
                                    <option value="xendit">Xendit</option>
                                    <option value="tripay">Tripay</option>
                                    <option value="voucher">Voucher</option>
                                </select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Daftar Transaksi</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Invoice</TableHead>
                                    <TableHead>Pelanggan</TableHead>
                                    <TableHead>Paket</TableHead>
                                    <TableHead className="text-right">Harga</TableHead>
                                    <TableHead>Metode</TableHead>
                                    <TableHead>Tanggal</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {transactions.data.map((tx) => (
                                    <TableRow key={tx.id}>
                                        <TableCell className="font-mono text-sm">{tx.invoice}</TableCell>
                                        <TableCell>{tx.username}</TableCell>
                                        <TableCell>{tx.plan_name}</TableCell>
                                        <TableCell className="text-right">Rp {tx.price.toLocaleString('id-ID')}</TableCell>
                                        <TableCell className="capitalize">{tx.method}</TableCell>
                                        <TableCell className="text-sm">{tx.recharged_on}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        {transactions.data.length === 0 && (
                            <p className="text-center text-muted-foreground py-8">Tidak ada data transaksi</p>
                        )}
                        {transactions.last_page > 1 && (
                            <div className="mt-4 flex justify-center gap-2">
                                {Array.from({ length: Math.min(transactions.last_page, 10) }, (_, i) => i + 1).map((page) => (
                                    <Button
                                        key={page}
                                        variant={page === transactions.current_page ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => router.get(route('admin.reports.index'), { ...filters, page })}
                                    >
                                        {page}
                                    </Button>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
