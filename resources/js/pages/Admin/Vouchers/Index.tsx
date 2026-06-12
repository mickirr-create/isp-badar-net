import MainLayout from '@/layouts/MainLayout';
import { Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Search, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Voucher { id: number; code: string; status: string; routers: string; created_at: string; plan?: { name_plan: string }; }
interface PaginatedData { data: Voucher[]; current_page: number; last_page: number; total: number; }
interface Props { vouchers: PaginatedData; filters: { search?: string; status?: string }; }

export default function VouchersIndex({ vouchers, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const handleSearch = () => router.get(route('admin.vouchers.index'), { search }, { preserveState: true });
    const handleDelete = (id: number) => {
        if (confirm('Delete this voucher?')) {
            router.delete(route('admin.vouchers.destroy', id), { onSuccess: () => toast.success('Voucher deleted') });
        }
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Vouchers</h2>
                    <Link href={route('admin.vouchers.create')}><Button><Plus className="mr-2 h-4 w-4" />Generate Vouchers</Button></Link>
                </div>
                <Card>
                    <CardHeader>
                        <div className="flex items-center gap-2">
                            <Input placeholder="Search by code..." value={search} onChange={(e) => setSearch(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && handleSearch()} className="max-w-sm" />
                            <Button variant="outline" onClick={handleSearch}><Search className="h-4 w-4" /></Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader><TableRow><TableHead>Code</TableHead><TableHead>Plan</TableHead><TableHead>Router</TableHead><TableHead>Status</TableHead><TableHead>Created</TableHead><TableHead>Actions</TableHead></TableRow></TableHeader>
                            <TableBody>
                                {vouchers.data.map((v) => (
                                    <TableRow key={v.id}>
                                        <TableCell className="font-mono font-medium">{v.code}</TableCell>
                                        <TableCell>{v.plan?.name_plan || '-'}</TableCell>
                                        <TableCell>{v.routers}</TableCell>
                                        <TableCell><Badge variant={v.status === 'Available' ? 'default' : 'secondary'}>{v.status}</Badge></TableCell>
                                        <TableCell>{v.created_at}</TableCell>
                                        <TableCell>
                                            <Button variant="outline" size="sm" onClick={() => handleDelete(v.id)}><Trash2 className="h-4 w-4" /></Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {vouchers.data.length === 0 && <TableRow><TableCell colSpan={6} className="text-center py-8 text-muted-foreground">No vouchers found.</TableCell></TableRow>}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
