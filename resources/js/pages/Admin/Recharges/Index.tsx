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

interface Recharge {
    id: number; username: string; namebp: string; recharged_on: string; expiration: string; status: string; method: string; routers: string; type: string;
}
interface PaginatedData { data: Recharge[]; current_page: number; last_page: number; total: number; }
interface Props { recharges: PaginatedData; filters: { search?: string; type?: string }; }

export default function RechargesIndex({ recharges, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const handleSearch = () => router.get(route('admin.recharges.index'), { search }, { preserveState: true });
    const handleDelete = (id: number) => {
        if (confirm('Delete this recharge record?')) {
            router.delete(route('admin.recharges.destroy', id), { onSuccess: () => toast.success('Recharge deleted') });
        }
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Recharges</h2>
                    <Link href={route('admin.recharges.create')}><Button><Plus className="mr-2 h-4 w-4" />Manual Recharge</Button></Link>
                </div>
                <Card>
                    <CardHeader>
                        <div className="flex items-center gap-2">
                            <Input placeholder="Search by username..." value={search} onChange={(e) => setSearch(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && handleSearch()} className="max-w-sm" />
                            <Button variant="outline" onClick={handleSearch}><Search className="h-4 w-4" /></Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader><TableRow><TableHead>Username</TableHead><TableHead>Plan</TableHead><TableHead>Router</TableHead><TableHead>Type</TableHead><TableHead>Method</TableHead><TableHead>Recharged</TableHead><TableHead>Expires</TableHead><TableHead>Status</TableHead><TableHead>Actions</TableHead></TableRow></TableHeader>
                            <TableBody>
                                {recharges.data.map((r) => (
                                    <TableRow key={r.id}>
                                        <TableCell className="font-medium">{r.username}</TableCell>
                                        <TableCell>{r.namebp}</TableCell>
                                        <TableCell>{r.routers}</TableCell>
                                        <TableCell><Badge variant="outline">{r.type}</Badge></TableCell>
                                        <TableCell>{r.method}</TableCell>
                                        <TableCell>{r.recharged_on}</TableCell>
                                        <TableCell>{r.expiration}</TableCell>
                                        <TableCell><Badge variant={r.status === 'on' ? 'default' : 'secondary'}>{r.status}</Badge></TableCell>
                                        <TableCell>
                                            <Button variant="outline" size="sm" onClick={() => handleDelete(r.id)}><Trash2 className="h-4 w-4" /></Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {recharges.data.length === 0 && <TableRow><TableCell colSpan={9} className="text-center py-8 text-muted-foreground">No recharges found.</TableCell></TableRow>}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
