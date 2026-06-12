import MainLayout from '@/layouts/MainLayout';
import { Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, Search, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Bandwidth { id: number; name_bw: string; rate_down: number; rate_up: number; rate_down_unit: string; rate_up_unit: string; }
interface PaginatedData { data: Bandwidth[]; current_page: number; last_page: number; total: number; }
interface Props { bandwidths: PaginatedData; filters: { search?: string }; }

export default function BandwidthIndex({ bandwidths, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const handleSearch = () => router.get(route('admin.bandwidth.index'), { search }, { preserveState: true });
    const handleDelete = (id: number) => {
        if (confirm('Delete this bandwidth?')) {
            router.delete(route('admin.bandwidth.destroy', id), { onSuccess: () => toast.success('Bandwidth deleted') });
        }
    };
    return (
        <MainLayout>
            <div className="space-y-6">
                <div className="flex items-center justify-between"><h2 className="text-2xl font-bold">Bandwidth</h2><Link href={route('admin.bandwidth.create')}><Button><Plus className="mr-2 h-4 w-4" />Add Bandwidth</Button></Link></div>
                <Card>
                    <CardHeader>
                        <div className="flex items-center gap-2">
                            <Input placeholder="Search bandwidth..." value={search} onChange={(e) => setSearch(e.target.value)} onKeyDown={(e) => e.key === 'Enter' && handleSearch()} className="max-w-sm" />
                            <Button variant="outline" onClick={handleSearch}><Search className="h-4 w-4" /></Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader><TableRow><TableHead>Name</TableHead><TableHead>Download</TableHead><TableHead>Upload</TableHead><TableHead>Actions</TableHead></TableRow></TableHeader>
                            <TableBody>
                                {bandwidths.data.map((bw) => (
                                    <TableRow key={bw.id}>
                                        <TableCell className="font-medium">{bw.name_bw}</TableCell>
                                        <TableCell>{bw.rate_down} {bw.rate_down_unit || 'Kbps'}</TableCell>
                                        <TableCell>{bw.rate_up} {bw.rate_up_unit || 'Kbps'}</TableCell>
                                        <TableCell>
                                            <div className="flex gap-2">
                                                <Link href={route('admin.bandwidth.edit', bw.id)}><Button variant="outline" size="sm"><Pencil className="h-4 w-4" /></Button></Link>
                                                <Button variant="outline" size="sm" onClick={() => handleDelete(bw.id)}><Trash2 className="h-4 w-4" /></Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                                {bandwidths.data.length === 0 && <TableRow><TableCell colSpan={4} className="text-center py-8 text-muted-foreground">No bandwidth found.</TableCell></TableRow>}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </MainLayout>
    );
}
