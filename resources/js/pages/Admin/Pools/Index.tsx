import MainLayout from '@/layouts/MainLayout';
import { Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Search, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Pool { id: number; pool_name: string; routers: string; ip_address: string; description: string; }
interface Router { id: number; name: string; }
interface PaginatedData { data: Pool[]; current_page: number; last_page: number; total: number; }
interface Props { pools: PaginatedData; routers: Router[]; filters: { search?: string; router?: string }; }

export default function PoolsIndex({ pools, routers, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const handleSearch = () => router.get(route('admin.pools.index'), { search }, { preserveState: true });
    const handleDelete = (id: number) => {
        if (confirm('Delete this IP pool?')) {
            router.delete(route('admin.pools.destroy', id), { onSuccess: () => toast.success('IP Pool deleted') });
        }
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between"><h2 className="text-2xl font-bold">IP Pools</h2><Link href={route('admin.pools.create')}><Button><Plus className="mr-2 h-4 w-4" />Add IP Pool</Button></Link></div>
                <Card>
                    <CardHeader>
                        <div className="flex items-center gap-2">
                            <Input placeholder="Search pools..." value={search} onChange={(e) => setSearch(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && handleSearch()} className="max-w-sm" />
                            <Button variant="outline" onClick={handleSearch}><Search className="h-4 w-4" /></Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader><TableRow><TableHead>Name</TableHead><TableHead>Router</TableHead><TableHead>IP Address</TableHead><TableHead>Description</TableHead><TableHead>Actions</TableHead></TableRow></TableHeader>
                            <TableBody>
                                {pools.data.map((pool) => (
                                    <TableRow key={pool.id}>
                                        <TableCell className="font-medium">{pool.pool_name}</TableCell>
                                        <TableCell>{pool.routers}</TableCell>
                                        <TableCell>{pool.ip_address}</TableCell>
                                        <TableCell>{pool.description}</TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Link href={route('admin.pools.edit', pool.id)}><Button variant="outline" size="sm"><Pencil className="h-4 w-4" /></Button></Link>
                                                <Button variant="outline" size="sm" onClick={() => handleDelete(pool.id)}><Trash2 className="h-4 w-4" /></Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {pools.data.length === 0 && <TableRow><TableCell colSpan={5} className="text-center py-8 text-muted-foreground">No IP pools found.</TableCell></TableRow>}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
