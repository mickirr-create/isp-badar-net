import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Eye, CreditCard } from 'lucide-react';

interface Audit {
    id: number;
    gateway: string;
    username: string;
    status: string;
    gateway_fee: number;
    created_at: string;
    plan?: { name: string };
    router?: { name: string };
}

interface AuditProps {
    audits: {
        data: Audit[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search?: string; gateway?: string; status?: string };
}

export default function Audit({ audits, filters }: AuditProps) {
    const handleSearch = (search: string) => {
        router.get(route('admin.payment-gateways.audit'), { ...filters, search }, { preserveState: true });
    };

    const statusBadge = (status: string) => {
        const colors: Record<string, string> = {
            Success: 'bg-green-100 text-green-800',
            Pending: 'bg-yellow-100 text-yellow-800',
            Failed: 'bg-red-100 text-red-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    return (
        <>
            <Head title="Audit Gateway Pembayaran" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <CreditCard className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Audit Gateway Pembayaran</h1>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Riwayat Transaksi Gateway</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4 flex flex-wrap gap-2">
                            <Input
                                placeholder="Cari username atau gateway..."
                                defaultValue={filters.search || ''}
                                onChange={(e) => handleSearch(e.target.value)}
                                className="max-w-sm"
                            />
                            <select
                                value={filters.gateway || ''}
                                onChange={(e) => router.get(route('admin.payment-gateways.audit'), { ...filters, gateway: e.target.value }, { preserveState: true })}
                                className="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="">Semua Gateway</option>
                                <option value="Midtrans">Midtrans</option>
                                <option value="Xendit">Xendit</option>
                                <option value="Tripay">Tripay</option>
                            </select>
                            <select
                                value={filters.status || ''}
                                onChange={(e) => router.get(route('admin.payment-gateways.audit'), { ...filters, status: e.target.value }, { preserveState: true })}
                                className="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="">Semua Status</option>
                                <option value="Success">Success</option>
                                <option value="Pending">Pending</option>
                                <option value="Failed">Failed</option>
                            </select>
                        </div>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Gateway</TableHead>
                                    <TableHead>Username</TableHead>
                                    <TableHead>Paket</TableHead>
                                    <TableHead className="text-right">Fee</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Tanggal</TableHead>
                                    <TableHead className="w-16"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {audits.data.map((audit) => (
                                    <TableRow key={audit.id}>
                                        <TableCell className="font-mono text-sm">{audit.id}</TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{audit.gateway}</Badge>
                                        </TableCell>
                                        <TableCell>{audit.username}</TableCell>
                                        <TableCell>{audit.plan?.name || '-'}</TableCell>
                                        <TableCell className="text-right">Rp {(audit.gateway_fee || 0).toLocaleString('id-ID')}</TableCell>
                                        <TableCell>
                                            <span className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${statusBadge(audit.status)}`}>
                                                {audit.status}
                                            </span>
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {audit.created_at ? new Date(audit.created_at).toLocaleString('id-ID') : '-'}
                                        </TableCell>
                                        <TableCell>
                                            <Button variant="ghost" size="icon" onClick={() => router.visit(route('admin.payment-gateways.audit-view', audit.id))}>
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        {audits.data.length === 0 && (
                            <p className="text-center text-muted-foreground py-8">Tidak ada data audit</p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
