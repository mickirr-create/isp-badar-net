import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';
import { Send } from 'lucide-react';

export default function Bulk() {
    const { data, setData, post, processing } = useForm({
        group: 'all',
        message: '',
        channel: 'whatsapp',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('admin.messages.bulk-send'), {
            onSuccess: () => {
                toast.success('Pesan massal berhasil dikirim');
                setData({ group: 'all', message: '', channel: 'whatsapp' });
            },
        });
    };

    return (
        <>
            <Head title="Pesan Massal" />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Send className="h-6 w-6" />
                    <h1 className="text-2xl font-bold">Pesan Massal</h1>
                </div>

                <form onSubmit={handleSubmit}>
                    <Card>
                        <CardHeader>
                            <CardTitle>Kirim Pesan Massal</CardTitle>
                            <CardDescription>Kirim pesan ke banyak pelanggan sekaligus</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-2">
                                <Label>Kelompok Pelanggan</Label>
                                <select
                                    value={data.group}
                                    onChange={(e) => setData('group', e.target.value)}
                                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                >
                                    <option value="all">Semua Pelanggan</option>
                                    <option value="active">Pelanggan Aktif</option>
                                    <option value="expired">Pelanggan Kadaluarsa</option>
                                    <option value="new">Pelanggan Baru (7 hari terakhir)</option>
                                </select>
                            </div>
                            <div className="grid gap-2">
                                <Label>Channel</Label>
                                <select
                                    value={data.channel}
                                    onChange={(e) => setData('channel', e.target.value)}
                                    className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                >
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="sms">SMS</option>
                                    <option value="email">Email</option>
                                    <option value="inbox">Inbox</option>
                                </select>
                            </div>
                            <div className="grid gap-2">
                                <Label>Pesan</Label>
                                <textarea
                                    value={data.message}
                                    onChange={(e) => setData('message', e.target.value)}
                                    rows={4}
                                    className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    placeholder="Tulis pesan di sini..."
                                />
                            </div>
                            <Button type="submit" disabled={processing || !data.message}>
                                {processing ? 'Mengirim...' : 'Kirim ke Semua'}
                            </Button>
                        </CardContent>
                    </Card>
                </form>
            </div>
        </>
    );
}
