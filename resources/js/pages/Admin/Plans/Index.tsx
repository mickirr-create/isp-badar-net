import MainLayout from '@/layouts/MainLayout';
import { Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Search, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Plan { id: number; name_plan: string; price: number; validity: number; validity_unit: string; type: string; enabled: boolean; bandwidth?: { name_bw: string }; }
interface PaginatedData { data: Plan[]; current_page: number; last_page: number; total: number; }
interface Props { plans: PaginatedData; filters: { search?: string; type?: string }; }

export default function PlansIndex({ plans, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const handleSearch = () => router.get(route('admin.plans.index'), { search }, { preserveState: true });
    const handleDelete = (id: number) => {
        if (confirm('Yakin ingin menghapus paket ini?')) {
            router.delete(route('admin.plans.destroy', id), { onSuccess: () => toast.success('Paket berhasil dihapus') });
        }
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between"><h2 className="text-2xl font-bold">Paket</h2><Link href={route('admin.plans.create')}><Button><Plus className="mr-2 h-4 w-4" />Tambah Paket</Button></Link></div>
                <Card>
                    <CardHeader><div className="flex items-center gap-2"><Input placeholder="Cari paket..." value={search} onChange={(e) => setSearch(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && handleSearch()} className="max-w-sm" /><Button variant="outline" onClick={handleSearch}><Search className="h-4 w-4" /></Button></div></CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader><TableRow><TableHead>Nama</TableHead><TableHead>Jenis</TableHead><TableHead>Bandwidth</TableHead><TableHead>Harga</TableHead><TableHead>Masa Aktif</TableHead><TableHead>Status</TableHead><TableHead>Aksi</TableHead></TableRow></TableHeader>
                            <TableBody>
                                {plans.data.map((p) => (
                                    <TableRow key={p.id}>
                                        <TableCell className="font-medium">{p.name_plan}</TableCell>
                                        <TableCell><Badge variant="outline">{p.type}</Badge></TableCell>
                                        <TableCell>{p.bandwidth?.name_bw || '-'}</TableCell>
                                        <TableCell>Rp {p.price?.toLocaleString()}</TableCell>
                                        <TableCell>{p.validity} {p.validity_unit}</TableCell>
                                        <TableCell><Badge variant={p.enabled ? 'default' : 'secondary'}>{p.enabled ? 'Aktif' : 'Nonaktif'}</Badge></TableCell>
                                        <TableCell><div className="flex gap-2"><Link href={route('admin.plans.edit', p.id)}><Button variant="outline" size="sm"><Pencil className="h-4 w-4" /></Button></Link><Button variant="outline" size="sm" onClick={() => handleDelete(p.id)}><Trash2 className="h-4 w-4" /></Button></div></TableCell>
                                    </TableRow>
                                ))}
                                {plans.data.length === 0 && <TableRow><TableCell colSpan={7} className="text-center py-8 text-muted-foreground">Tidak ada paket ditemukan.</TableCell></TableRow>}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
