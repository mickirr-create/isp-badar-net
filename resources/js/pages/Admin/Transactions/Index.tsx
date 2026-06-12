import MainLayout from '@/layouts/MainLayout';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Search } from 'lucide-react';
import { useState } from 'react';

interface Transaction { id: number; invoice: string; username: string; plan_name: string; price: string; recharged_on: string; method: string; routers: string; type: string; note: string; }
interface PaginatedData { data: Transaction[]; current_page: number; last_page: number; total: number; }
interface Props { transactions: PaginatedData; filters: { search?: string; type?: string }; }

export default function TransactionsIndex({ transactions, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const handleSearch = () => router.get(route('admin.transactions.index'), { search }, { preserveState: true });
    return (
        <MainLayout>
            <div className="space-y-6">
                <h2 className="text-2xl font-bold">Transactions</h2>
                <Card>
                    <CardHeader>
                        <div className="flex items-center gap-2">
                            <Input placeholder="Search by invoice or username..." value={search} onChange={(e) => setSearch(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && handleSearch()} className="max-w-sm" />
                            <Button variant="outline" onClick={handleSearch}><Search className="h-4 w-4" /></Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader><TableRow><TableHead>Invoice</TableHead><TableHead>Username</TableHead><TableHead>Plan</TableHead><TableHead>Price</TableHead><TableHead>Method</TableHead><TableHead>Type</TableHead><TableHead>Date</TableHead></TableRow></TableHeader>
                            <TableBody>
                                {transactions.data.map((t) => (
                                    <TableRow key={t.id}>
                                        <TableCell className="font-mono">{t.invoice}</TableCell>
                                        <TableCell>{t.username}</TableCell>
                                        <TableCell>{t.plan_name}</TableCell>
                                        <TableCell>Rp {parseFloat(t.price as any)?.toLocaleString()}</TableCell>
                                        <TableCell>{t.method}</TableCell>
                                        <TableCell><Badge variant="outline">{t.type}</Badge></TableCell>
                                        <TableCell>{t.recharged_on}</TableCell>
                                    </TableRow>
                                ))}
                                {transactions.data.length === 0 && <TableRow><TableCell colSpan={7} className="text-center py-8 text-muted-foreground">No transactions found.</TableCell></TableRow>}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
