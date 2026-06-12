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

interface RouterItem { id: number; name: string; ip_address: string; type: string; status: string; enabled: boolean; }
interface PaginatedData { data: RouterItem[]; current_page: number; last_page: number; total: number; }
interface Props { routers: PaginatedData; filters: { search?: string }; }

export default function RoutersIndex({ routers, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const handleSearch = () => router.get(route('admin.routers.index'), { search }, { preserveState: true });
    const handleDelete = (id: number) => {
        if (confirm('Yakin ingin menghapus router ini?')) {
            router.delete(route('admin.routers.destroy', id), { onSuccess: () => toast.success('Router berhasil dihapus') });
        }
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between"><h2 className="text-2xl font-bold">Router</h2><Link href={route('admin.routers.create')}><Button><Plus className="mr-2 h-4 w-4" />Tambah Router</Button></Link></div>
                <Card>
                    <CardHeader><div className="flex items-center gap-2"><Input placeholder="Cari router..." value={search} onChange={(e) => setSearch(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && handleSearch()} className="max-w-sm" /><Button variant="outline" onClick={handleSearch}><Search className="h-4 w-4" /></Button></div></CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader><TableRow><TableHead>Nama</TableHead><TableHead>IP Address</TableHead><TableHead>Jenis</TableHead><TableHead>Status</TableHead><TableHead>Aksi</TableHead></TableRow></TableHeader>
                            <TableBody>
                                {routers.data.map((r) => (
                                    <TableRow key={r.id}>
                                        <TableCell className="font-medium">{r.name}</TableCell>
                                        <TableCell>{r.ip_address}</TableCell>
                                        <TableCell><Badge variant="outline">{r.type || 'Mikrotik'}</Badge></TableCell>
                                        <TableCell><Badge variant={r.status === 'online' ? 'default' : 'secondary'}>{r.status === 'online' ? 'Online' : 'Offline'}</Badge></TableCell>
                                        <TableCell><div className="flex gap-2"><Link href={route('admin.routers.edit', r.id)}><Button variant="outline" size="sm"><Pencil className="h-4 w-4" /></Button></Link><Button variant="outline" size="sm" onClick={() => handleDelete(r.id)}><Trash2 className="h-4 w-4" /></Button></div></TableCell>
                                    </TableRow>
                                ))}
                                {routers.data.length === 0 && <TableRow><TableCell colSpan={5} className="text-center py-8 text-muted-foreground">Tidak ada router ditemukan.</TableCell></TableRow>}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
