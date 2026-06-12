import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { MessageSquare } from 'lucide-react';

interface MessageLog {
    id: number;
    message_type: string | null;
    recipient: string | null;
    message_content: string | null;
    status: string | null;
    error_message: string | null;
    sent_at: string;
}

interface MessageLogsProps {
    messageLogs: {
        data: MessageLog[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search?: string; message_type?: string };
}

export default function MessageLogs({ messageLogs, filters }: MessageLogsProps) {
    const handleSearch = (search: string) => {
        router.get(route('admin.message-logs.index'), { search, message_type: filters.message_type }, { preserveState: true });
    };

    const statusBadge = (status: string | null) => {
        if (status === 'sent' || status === 'delivered') {
            return <Badge className="bg-green-100 text-green-800">Terkirim</Badge>;
        }
        if (status === 'failed') {
            return <Badge variant="destructive">Gagal</Badge>;
        }
        return <Badge variant="secondary">{status || '-'}</Badge>;
    };

    return (
        <>
            <Head title="Log Pesan" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <MessageSquare className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Log Pesan</h1>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Riwayat Pesan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4 flex gap-2">
                            <Input
                                placeholder="Cari penerima atau pesan..."
                                defaultValue={filters.search || ''}
                                onChange={(e) => handleSearch(e.target.value)}
                                className="max-w-sm"
                            />
                            <select
                                value={filters.message_type || ''}
                                onChange={(e) => router.get(route('admin.message-logs.index'), { search: filters.search, message_type: e.target.value }, { preserveState: true })}
                                className="flex h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                            >
                                <option value="">Semua Tipe</option>
                                <option value="sms">SMS</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="email">Email</option>
                                <option value="inbox">Inbox</option>
                            </select>
                        </div>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Tanggal</TableHead>
                                    <TableHead>Tipe</TableHead>
                                    <TableHead>Penerima</TableHead>
                                    <TableHead>Pesan</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {messageLogs.data.map((log) => (
                                    <TableRow key={log.id}>
                                        <TableCell className="text-sm whitespace-nowrap">
                                            {new Date(log.sent_at).toLocaleString('id-ID')}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">{(log.message_type || '-').toUpperCase()}</Badge>
                                        </TableCell>
                                        <TableCell className="text-sm">{log.recipient || '-'}</TableCell>
                                        <TableCell className="max-w-xs truncate text-sm">{log.message_content || '-'}</TableCell>
                                        <TableCell>{statusBadge(log.status)}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                        {messageLogs.data.length === 0 && (
                            <p className="text-center text-muted-foreground py-8">Tidak ada data log pesan</p>
                        )}
                        {messageLogs.last_page > 1 && (
                            <div className="mt-4 flex justify-center gap-2">
                                {Array.from({ length: Math.min(messageLogs.last_page, 10) }, (_, i) => i + 1).map((page) => (
                                    <Button
                                        key={page}
                                        variant={page === messageLogs.current_page ? 'default' : 'outline'}
                                        size="sm"
                                        onClick={() => router.get(route('admin.message-logs.index'), { ...filters, page })}
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
