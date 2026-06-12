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

interface Customer { id: number; username: string; name: string; email: string; phone: string; status: string; balance: number; created_at: string; }
interface PaginatedData { data: Customer[]; current_page: number; last_page: number; per_page: number; total: number; }
interface Props { customers: PaginatedData; filters: { search?: string; status?: string }; }

export default function CustomersIndex({ customers, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const handleSearch = () => router.get(route('admin.customers.index'), { search }, { preserveState: true });
    const handleDelete = (id: number) => {
        if (confirm('Yakin ingin menghapus pelanggan ini?')) {
            router.delete(route('admin.customers.destroy', id), { onSuccess: () => toast.success('Pelanggan berhasil dihapus') });
        }
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Pelanggan</h2>
                    <Link href={route('admin.customers.create')}><Button><Plus className="mr-2 h-4 w-4" />Tambah Pelanggan</Button></Link>
                </div>
                <Card>
                    <CardHeader>
                        <div className="flex items-center gap-2">
                            <Input placeholder="Cari pelanggan..." value={search} onChange={(e) => setSearch(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && handleSearch()} className="max-w-sm" />
                            <Button variant="outline" onClick={handleSearch}><Search className="h-4 w-4" /></Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader><TableRow><TableHead>Username</TableHead><TableHead>Nama</TableHead><TableHead>Email</TableHead><TableHead>Telepon</TableHead><TableHead>Status</TableHead><TableHead>Saldo</TableHead><TableHead>Aksi</TableHead></TableRow></TableHeader>
                            <TableBody>
                                {customers.data.map((c) => (
                                    <TableRow key={c.id}>
                                        <TableCell className="font-medium">{c.username}</TableCell>
                                        <TableCell>{c.name}</TableCell>
                                        <TableCell>{c.email}</TableCell>
                                        <TableCell>{c.phone}</TableCell>
                                        <TableCell><Badge variant={c.status === 'Active' ? 'default' : c.status === 'Suspended' ? 'destructive' : 'secondary'}>{c.status === 'Active' ? 'Aktif' : c.status === 'Suspended' ? 'Suspend' : 'Nonaktif'}</Badge></TableCell>
                                        <TableCell>Rp {c.balance?.toLocaleString() || '0'}</TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Link href={route('admin.customers.edit', c.id)}><Button variant="outline" size="sm"><Pencil className="h-4 w-4" /></Button></Link>
                                                <Button variant="outline" size="sm" onClick={() => handleDelete(c.id)}><Trash2 className="h-4 w-4" /></Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {customers.data.length === 0 && <TableRow><TableCell colSpan={7} className="text-center py-8 text-muted-foreground">Tidak ada pelanggan ditemukan.</TableCell></TableRow>}
                            </TableBody>
                        </Table>
                        {customers.last_page > 1 && (
                            <div className="flex justify-between items-center mt-4">
                                <span className="text-sm text-muted-foreground">Menampilkan {customers.data.length} dari {customers.total} pelanggan</span>
                                <div className="flex gap-2">
                                    {customers.current_page > 1 && <Button variant="outline" size="sm" onClick={() => router.get(route('admin.customers.index'), { page: customers.current_page - 1, search })}>Sebelumnya</Button>}
                                    {customers.current_page < customers.last_page && <Button variant="outline" size="sm" onClick={() => router.get(route('admin.customers.index'), { page: customers.current_page + 1, search })}>Selanjutnya</Button>}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
