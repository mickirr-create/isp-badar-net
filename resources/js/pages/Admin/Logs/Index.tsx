import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Activity, Trash2 } from 'lucide-react';

interface Log {
    id: number;
    date: string | null;
    type: string;
    description: string;
    userid: number;
    ip: string;
    user?: { fullname: string; username: string } | null;
}

interface LogsProps {
    logs: {
        data: Log[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search?: string; type?: string };
}

export default function Logs({ logs, filters }: LogsProps) {
    const handleSearch = (search: string) => {
        router.get(route('admin.logs.index'), { search, type: filters.type }, { preserveState: true });
    };

    const handleDelete = (id: number) => {
        if (confirm('Yakin ingin menghapus log ini?')) {
            router.delete(route('admin.logs.destroy', id));
        }
    };

    const typeBadge = (type: string) => {
        const colors: Record<string, string> = {
            Login: 'bg-green-100 text-green-800',
            Logout: 'bg-gray-100 text-gray-800',
            Recharge: 'bg-blue-100 text-blue-800',
            Error: 'bg-red-100 text-red-800',
        };
        return colors[type] || 'bg-gray-100 text-gray-800';
    };

    return (
        <>
            <Head title="Log Aktivitas" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Activity className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Log Aktivitas</h1>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Sistem Log</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4 flex gap-2">
                            <Input
                                placeholder="Cari log..."
                                defaultValue={filters.search || ''}
                                onChange={(e) => handleSearch(e.target.value)}
                                className="max-w-sm"
                            />
                            <select
                                value={filters.type || ''}
                                onChange={(e) => router.get(route('admin.logs.index'), { search: filters.search, type: e.target.value }, { preserveState: true })}
                                className="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="">Semua Tipe</option>
                                <option value="Login">Login</option>
                                <option value="Logout">Logout</option>
                                <option value="Recharge">Recharge</option>
                                <option value="Error">Error</option>
                            </select>
                        </div>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Tanggal</TableHead>
                                    <TableHead>Tipe</TableHead>
                                    <TableHead>Deskripsi</TableHead>
                                    <TableHead>User</TableHead>
                                    <TableHead>IP</TableHead>
                                    <TableHead className="w-16"></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {logs.data.map((log) => (
                                    <TableRow key={log.id}>
                                        <TableCell className="text-sm whitespace-nowrap">
                                            {log.date ? new Date(log.date).toLocaleString('id-ID') : '-'}
                                        </TableCell>
                                        <TableCell>
                                            <span className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${typeBadge(log.type)}`}>
                                                {log.type}
                                            </span>
                                        </TableCell>
                                        <TableCell className="max-w-md truncate text-sm">{log.description}</TableCell>
                                        <TableCell className="text-sm">{log.user?.fullname || log.user?.username || log.userid}</TableCell>
                                        <TableCell className="font-mono text-xs">{log.ip}</TableCell>
                                        <TableCell>
                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(log.id)}>
                                                <Trash2 className="h-4 w-4 text-destructive" />
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        {logs.data.length === 0 && (
                            <p className="text-center text-muted-foreground py-8">Tidak ada data log</p>
                        )}
                        {logs.last_page > 1 && (
                            <div className="mt-4 flex justify-center gap-2">
                                {Array.from({ length: Math.min(logs.last_page, 10) }, (_, i) => i + 1).map((page) => (
                                    <Button
                                        key={page}
                                        variant={page === logs.current_page ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => router.get(route('admin.logs.index'), { ...filters, page })}
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
