import MainLayout from '@/layouts/MainLayout';
import { router } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface Transaction { id: number; invoice: string; plan_name: string; price: string; recharged_on: string; method: string; type: string; }
interface PaginatedData { data: Transaction[]; current_page: number; last_page: number; total: number; }
interface Props { transactions: PaginatedData; filters: { type?: string }; }

export default function CustomerTransactions({ transactions, filters }: Props) {
    const handleFilter = (type: string) => {
        router.get(route('customer.transactions.index'), { type }, { preserveState: true });
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Transaksi Saya</h2>
                    <Select value={filters.type || 'all'} onValueChange={(v) => handleFilter(v === 'all' ? '' : v)}>
                        <SelectTrigger className="w-[180px]"><SelectValue placeholder="Semua Jenis" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Jenis</SelectItem>
                            <SelectItem value="Hotspot">Hotspot</SelectItem>
                            <SelectItem value="PPPOE">PPPoE</SelectItem>
                            <SelectItem value="Balance">Saldo</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <Card>
                    <CardContent>
                        <Table>
                            <TableHeader><TableRow><TableHead>Faktur</TableHead><TableHead>Paket</TableHead><TableHead>Jumlah</TableHead><TableHead>Metode</TableHead><TableHead>Jenis</TableHead><TableHead>Tanggal</TableHead></TableRow></TableHeader>
                            <TableBody>
                                {transactions.data.map((t) => (
                                    <TableRow key={t.id}>
                                        <TableCell className="font-mono">{t.invoice}</TableCell>
                                        <TableCell>{t.plan_name}</TableCell>
                                        <TableCell>Rp {parseFloat(t.price as any)?.toLocaleString()}</TableCell>
                                        <TableCell>{t.method}</TableCell>
                                        <TableCell><Badge variant="outline">{t.type}</Badge></TableCell>
                                        <TableCell>{t.recharged_on}</TableCell>
                                    </TableRow>
                                ))}
                                {transactions.data.length === 0 && <TableRow><TableCell colSpan={6} className="text-center py-8 text-muted-foreground">Belum ada transaksi.</TableCell></TableRow>}
                            </TableBody>
                        </Table>
                        {transactions.last_page > 1 && (
                            <div className="flex justify-between items-center mt-4">
                                <span className="text-sm text-muted-foreground">Halaman {transactions.current_page} dari {transactions.last_page}</span>
                                <div className="flex gap-2">
                                    {transactions.current_page > 1 && <Button variant="outline" size="sm" onClick={() => router.get(route('customer.transactions.index'), { page: transactions.current_page - 1 })}>Sebelumnya</Button>}
                                    {transactions.current_page < transactions.last_page && <Button variant="outline" size="sm" onClick={() => router.get(route('customer.transactions.index'), { page: transactions.current_page + 1 })}>Selanjutnya</Button>}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
