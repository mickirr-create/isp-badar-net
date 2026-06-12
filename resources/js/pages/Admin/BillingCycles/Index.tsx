import { Head, router, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';
import { Calendar, Send, AlertTriangle, CheckCircle } from 'lucide-react';

interface Customer {
    id: number;
    username: string;
    name: string;
    billing_day: number | null;
    throttle_enabled: boolean;
    throttle_profile: string | null;
    billing_status: string;
    due_date: string | null;
    days_until_due: number | null;
    recharges: Array<{
        id: number;
        namebp: string;
        expiration: string;
        throttle_applied: boolean;
    }>;
}

interface BillingCyclesProps {
    customers: {
        data: Customer[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { status?: string };
}

export default function BillingCycles({ customers, filters }: BillingCyclesProps) {
    const handleFilter = (status: string) => {
        router.get(route('admin.billing-cycles.index'), { status }, { preserveState: true });
    };

    const handleSendNotification = (customerId: number) => {
        if (confirm('Kirim notifikasi jatuh tempo?')) {
            router.post(route('admin.billing-cycles.send-notification', customerId), {}, {
                onSuccess: () => toast.success('Notifikasi berhasil dikirim'),
            });
        }
    };

    const handleApplyThrottle = (customerId: number) => {
        if (confirm('Terapkan throttle ke pelanggan ini?')) {
            router.post(route('admin.billing-cycles.apply-throttle', customerId), {}, {
                onSuccess: () => toast.success('Throttle berhasil diterapkan'),
            });
        }
    };

    const handleRestoreSpeed = (customerId: number) => {
        if (confirm('Restore speed pelanggan ini?')) {
            router.post(route('admin.billing-cycles.restore-speed', customerId), {}, {
                onSuccess: () => toast.success('Speed berhasil direstore'),
            });
        }
    };

    const statusBadge = (status: string) => {
        const styles: Record<string, string> = {
            active: 'bg-green-100 text-green-800',
            due_soon: 'bg-yellow-100 text-yellow-800',
            overdue: 'bg-red-100 text-red-800',
            no_cycle: 'bg-gray-100 text-gray-800',
        };
        const labels: Record<string, string> = {
            active: 'Aktif',
            due_soon: 'Jatuh Tempo',
            overdue: 'Terlambat',
            no_cycle: 'Tanpa Siklus',
        };
        return (
            <span className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${styles[status] || styles.no_cycle}`}>
                {labels[status] || status}
            </span>
        );
    };

    return (
        <>
            <Head title="Siklus Billing" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Calendar className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Siklus Billing</h1>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Daftar Pelanggan dengan Siklus Billing</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4 flex gap-2">
                            <Button
                                variant={!filters.status ? 'default' : 'outline'}
                                size="sm"
                                onClick={() => handleFilter('')}
                            >
                                Semua
                            </Button>
                            <Button
                                variant={filters.status === 'active' ? 'default' : 'outline'}
                                size="sm"
                                onClick={() => handleFilter('active')}
                            >
                                Aktif
                            </Button>
                            <Button
                                variant={filters.status === 'due_soon' ? 'default' : 'outline'}
                                size="sm"
                                onClick={() => handleFilter('due_soon')}
                            >
                                Jatuh Tempo
                            </Button>
                            <Button
                                variant={filters.status === 'overdue' ? 'default' : 'outline'}
                                size="sm"
                                onClick={() => handleFilter('overdue')}
                            >
                                Terlambat
                            </Button>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Pelanggan</TableHead>
                                    <TableHead>Hari Billing</TableHead>
                                    <TableHead>Jatuh Tempo</TableHead>
                                    <TableHead>Sisa Hari</TableHead>
                                    <TableHead>Paket</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {customers.data.map((customer) => (
                                    <TableRow key={customer.id}>
                                        <TableCell>
                                            <div>
                                                <p className="font-medium">{customer.username}</p>
                                                <p className="text-sm text-muted-foreground">{customer.name}</p>
                                            </div>
                                        </TableCell>
                                        <TableCell className="text-center">
                                            {customer.billing_day || '-'}
                                        </TableCell>
                                        <TableCell>
                                            {customer.due_date || '-'}
                                        </TableCell>
                                        <TableCell className="text-center">
                                            {customer.days_until_due !== null ? (
                                                <span className={customer.days_until_due <= 7 ? 'text-red-600 font-medium' : ''}>
                                                    {customer.days_until_due > 0 ? customer.days_until_due + ' hari' : 'Lewat'}
                                                </span>
                                            ) : '-'}
                                        </TableCell>
                                        <TableCell>
                                            {customer.recharges[0]?.namebp || '-'}
                                        </TableCell>
                                        <TableCell>
                                            {statusBadge(customer.billing_status)}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex gap-1">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    title="Kirim Notifikasi"
                                                    onClick={() => handleSendNotification(customer.id)}
                                                >
                                                    <Send className="h-4 w-4" />
                                                </Button>
                                                {customer.billing_status === 'overdue' && !customer.recharges[0]?.throttle_applied && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        title="Apply Throttle"
                                                        onClick={() => handleApplyThrottle(customer.id)}
                                                    >
                                                        <AlertTriangle className="h-4 w-4 text-orange-500" />
                                                    </Button>
                                                )}
                                                {customer.recharges[0]?.throttle_applied && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        title="Restore Speed"
                                                        onClick={() => handleRestoreSpeed(customer.id)}
                                                    >
                                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                                    </Button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {customers.data.length === 0 && (
                            <p className="text-center text-muted-foreground py-8">Tidak ada data siklus billing</p>
                        )}

                        {customers.last_page > 1 && (
                            <div className="mt-4 flex justify-center gap-2">
                                {Array.from({ length: Math.min(customers.last_page, 10) }, (_, i) => i + 1).map((page) => (
                                    <Button
                                        key={page}
                                        variant={page === customers.current_page ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => router.get(route('admin.billing-cycles.index'), { ...filters, page })}
                                    >
                                        {page}
                                    </Button>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
